<?php

namespace App\Twig\Components;

use App\Entity\Medication;
use App\Repository\DispenseRepository;
use App\Service\PaginationService;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class MedicationDispenseList
{
    use DefaultActionTrait;

    public Medication $medication;

    public ?int $limit = 2;

    public function __construct(
        private readonly DispenseRepository $dispenseRepository,
        private readonly PaginationService  $paginationService
    )
    {
    }

    public function getDispenses(): array
    {

        $query = $this->dispenseRepository->getByMedicationQuery($this->medication);

        return $this->paginationService->paginate($query, 1, $this->limit);
    }
}
