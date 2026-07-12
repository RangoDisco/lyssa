<?php

namespace App\Twig\Components;

use App\Entity\Medication;
use App\Repository\DispenseRepository;
use App\Repository\VariantRepository;
use App\Service\PaginationService;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class MedicationVariantList
{
    public Medication $medication;

    public ?int $limit = 5;

    public function __construct(
        private readonly VariantRepository $variantRepository,
        private readonly PaginationService  $paginationService
    )
    {
    }

    public function getVariants(): array
    {

        $query = $this->variantRepository->getByMedicationQuery($this->medication);

        return $this->paginationService->paginate($query, 1, $this->limit);
    }
}
