<?php

namespace App\Service;

use App\DTO\Import\CISBDPMDTO;
use App\DTO\Import\CISGENERDTO;
use App\Entity\Lab;
use App\Entity\Medicine;
use App\Enum\MedicineFormat;
use App\Enum\Source;
use App\Helper\Strings;
use App\Repository\LabRepository;
use App\Repository\MedicineRepository;
use Doctrine\ORM\EntityManagerInterface;
use Transliterator;

readonly class ImportService
{

    public function __construct(
        private EntityManagerInterface $em,
        private LabRepository          $labRepository,
        private MedicineRepository     $medicineRepository
    )
    {
    }

    public function importMedicine(CISBDPMDTO|CISGENERDTO $dto, bool $shouldPersist): bool
    {
        $existing = $this->medicineRepository->findOneBy([
            'cis' => $dto->cis
        ]);

        if ($existing) {
            return true;
        }

        // Both imports have slightly different columns, we are forced to use different methods.
        if ($dto instanceof CISBDPMDTO) {
            $name = $this->normalizeMedicineName($dto->name);
            $lab = $this->getOrCreateLab($dto->maker, $shouldPersist);
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

        if ($shouldPersist) {
            $this->em->persist($medicine);
            $this->em->flush();
        }

        return true;
    }

    private function normalizeFormat(string $format): MedicineFormat
    {
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

    private function getOrCreateLab(string $name, bool $shouldPersist): Lab
    {
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

        if ($shouldPersist) {
            $this->em->persist($lab);
        }

        return $lab;
    }
}
