<?php

namespace App\Twig\Components;

use App\Entity\Substance;
use App\Repository\MedicineRepository;
use App\Repository\MedicineSubstanceRepository;
use App\Service\PaginationService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SubstanceMedicineList
{
    public Substance $substance;

    public ?int $limit = 5;

    public function __construct(
        private readonly MedicineSubstanceRepository $medicineSubstanceRepository,
        private readonly PaginationService           $paginationService
    )
    {
    }

    public function getMedicineSubstances(): array
    {

        $query = $this->medicineSubstanceRepository->getBySubstanceQuery($this->substance);

        return $this->paginationService->paginate($query, 1, $this->limit);
    }
}
