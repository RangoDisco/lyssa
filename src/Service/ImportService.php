<?php

namespace App\Service;

use App\DTO\Import\CisBdpmDTO;
use App\DTO\Import\CisCompoDTO;
use App\DTO\Import\CisGenerDTO;
use App\Entity\Lab;
use App\Entity\Medicine;
use App\Entity\MedicineSubstance;
use App\Entity\Substance;
use App\Enum\DosageUnitEnum;
use App\Enum\MedicineFormat;
use App\Enum\Source;
use App\Helper\Strings;
use App\Repository\LabRepository;
use App\Repository\MedicineRepository;
use App\Repository\MedicineSubstanceRepository;
use App\Repository\SubstanceRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Transliterator;

readonly class ImportService
{

    public function __construct(
        private EntityManagerInterface      $em,
        private LabRepository               $labRepository,
        private MedicineRepository          $medicineRepository,
        private SubstanceRepository         $substanceRepository,
        private MedicineSubstanceRepository $msRepository,
    )
    {
    }

    /**
     *  Imports Medicine and its lab or a Generic Medicine from a row of the French db.
     *  Either a CisBdpmDTO (medicine) or CisGenerDto (generic) is passed. Both come from different csv files
     *  but are really similar
     */
    public function importMedicine(CisBdpmDTO|CisGenerDTO $dto): bool
    {
        $existing = $this->medicineRepository->findOneBy([
            'cis' => $dto->cis
        ]);

        if ($existing) {
            return true;
        }

        $this->createMedicine($dto);

        return true;
    }

    /**
     * Imports a Substance and create the composition rows from a row of the French db.
     */
    public function importSubstance(CisCompoDTO $compo): void
    {
        $substance = $this->getOrCreateSubstance($compo);

        $existingMs = $this->msRepository->findOneBySubstanceAndCis($substance, $compo->cis);
        if ($existingMs) {
            return;
        }

        ['amount' => $amount, 'unit' => $unit] = $this->normalizeDosage($compo->dosage);

        // Medicine should always exist at this point, if not we want to stop the import.
        $medicine = $this->medicineRepository->findOneBy([
            'cis' => $compo->cis
        ]);
        if ($medicine === null) {
            throw new NotFoundHttpException('Unable to find medicine linked to this substance.');
        }

        $newMs = new MedicineSubstance()
            ->setSubstance($substance)
            ->setAmount($amount)
            ->setUnit($unit)
            ->setMedicine($medicine);

        $this->em->persist($newMs);
        $this->em->flush();
        $this->em->clear();
    }

    private function createMedicine(CisBdpmDTO|CisGenerDTO $dto): void
    {
        // Both imports have slightly different columns, we are forced to use different methods.
        if ($dto instanceof CisBdpmDTO) {
            $name = $this->normalizeMedicineName($dto->name);
            $lab = $this->getOrCreateLab($dto->maker);
            $format = $this->normalizeFormat($dto->pharma_type);
            $isGeneric = false;
        } else {
            $name = $this->normalizeMedicineName($dto->name);
            $lab = null;
            $format = $this->getFormatFromName($dto->name);
            $isGeneric = true;
        }

        $medicine = new Medicine()
            ->setCis($dto->cis)
            ->setName($name)
            ->setLab($lab)
            ->setFormat($format)
            ->setIsGeneric($isGeneric)
            ->setSource(Source::Official);

        $this->em->persist($medicine);
        $this->em->flush();
    }

    private function normalizeFormat(?string $format): MedicineFormat
    {
        if (!$format) {
            return MedicineFormat::Unknown;
        }

        $transliterator = Transliterator::createFromRules(':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;', Transliterator::FORWARD);
        $normalized = $transliterator->transliterate($format);

        if (Strings::str_contains_any($normalized, ['comprime', 'gelule', 'capsule', 'pastille'])) {
            return MedicineFormat::Pill;
        }

        // Injectable is second because some types also include MedicineFormat::Liquid keywords (e.g. solution injectable pour perfusion)
        if (Strings::str_contains_any($normalized, ['injectable', 'perfusion'])) {
            return MedicineFormat::Injectable;
        }

        if (Strings::str_contains_any($normalized, ['solution', 'liquide', 'buvable', 'diluer'])) {
            return MedicineFormat::Liquid;
        }

        if (str_contains($normalized, 'gel')) {
            return MedicineFormat::Gel;
        }

        return MedicineFormat::Unknown;
    }

    private function normalizeMedicineName(string $name): string
    {
        return explode(', ', $name)[0];
    }

    // Used for generics as they do not include a "type" column.
    private function getFormatFromName(string $name): MedicineFormat
    {
        $format = explode(', ', $name)[1];

        return $this->normalizeFormat($format);
    }

    private function getOrCreateLab(string $name): Lab
    {
        // Some labs' name start with a whitespace for some reason
        $searchName = $name;
        if (str_starts_with($searchName, ' ')) {
            $searchName = substr($searchName, 1);
        }

        $existing = $this->labRepository->findOneBy([
            'name' => $searchName
        ]);
        if ($existing !== null) {
            return $existing;
        }

        $lab = new Lab()
            ->setName($searchName);

        $this->em->persist($lab);

        return $lab;
    }

    private function normalizeDosage(?string $dosage = null): array
    {
        if ($dosage === null) {
            return ['amount' => null, 'unit' => null];
        }

        // Removes parenthesis and their content
        $dosage = preg_replace('/\s*\([^)]*\)/u', '', $dosage);

        // Lowers, removes accents
        $transliterator = Transliterator::createFromRules(
            ':: Any-Latin; :: Latin-ASCII; :: NFD; :: [:Nonspacing Mark:] Remove; :: Lower(); :: NFC;',
            Transliterator::FORWARD
        );

        if ($transliterator === null) {
            return ['amount' => null, 'unit' => null];
        }

        $normalized = $transliterator->transliterate($dosage);

        $split = explode(' ', $normalized);

        // See if transliteration results in "amount+whitespace+unit". If not, forfeit.
        if (count($split) !== 2) {
            return ['amount' => null, 'unit' => null];
        }

        $amount = $this->normalizeAmount($split[0]);
        $unit = $this->normalizeUnit($split[1]);

        // Reset both as only 1 value would not make sense
        if ($amount === null || $unit === null) {
            $amount = null;
            $unit = null;
        }

        return ['amount' => $amount, 'unit' => $unit];
    }

    // Replace every , with . and checks if value is a number.
    private function normalizeAmount(string $rawAmount): ?float
    {
        $normalized = str_replace(",", '.', $rawAmount);

        // This should never happen in theory, but we still want to throw anyway
        if (!is_numeric($normalized)) {
            return null;
        }

        return $normalized;
    }

    private function normalizeUnit(string $rawUnit): ?DosageUnitEnum
    {
        // Microgrammes gets its own condition as the value is written in French instead of "μg" in the import
        if ($rawUnit === 'microgrammes') {
            return DosageUnitEnum::MicroGram;
        }

        $unit = DosageUnitEnum::tryFrom($rawUnit);

        return $unit ?? null;
    }

    private function getOrCreateSubstance(CisCompoDTO $compo): Substance
    {
        $existing = $this->substanceRepository->findOneBy([
            'code' => $compo->code
        ]);
        if ($existing) {
            return $existing;
        }

        $substance = new Substance()
            ->setName($compo->name)
            ->setSource(Source::Official)
            ->setCode($compo->code);

        $this->em->persist($substance);
        $this->em->flush();

        return $substance;
    }
}
