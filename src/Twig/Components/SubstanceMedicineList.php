<?php

namespace App\Twig\Components;

use App\Entity\Substance;
use App\Repository\MedicineRepository;
use App\Service\PaginationService;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SubstanceMedicineList
{
    public Substance $substance;

    public ?int $limit = 5;

    public function __construct(
        private readonly MedicineRepository $medicineRepository,
        private readonly PaginationService  $paginationService
    )
    {
    }

    public function getMedicines(): array
    {

        $query = $this->medicineRepository->getBySubstanceQuery($this->substance);

        return $this->paginationService->paginate($query, 1, $this->limit);
    }
}
