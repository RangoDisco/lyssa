<?php

namespace App\Twig\Components;

use App\DTO\Pagination\PaginatedResult;
use App\DTO\Pagination\PaginationRequest;
use App\Entity\Substance;
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

    public function getMedicineSubstances(): PaginatedResult
    {

        $query = $this->medicineSubstanceRepository->createBySubstanceQueryBuilder($this->substance);

        return $this->paginationService->paginate($query, PaginationRequest::create(1, $this->limit));
    }
}
