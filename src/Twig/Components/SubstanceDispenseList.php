<?php

namespace App\Twig\Components;

use App\DTO\Pagination\PaginatedResult;
use App\DTO\Pagination\PaginationRequest;
use App\Entity\Substance;
use App\Repository\DispenseRepository;
use App\Service\PaginationService;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class SubstanceDispenseList
{
    use DefaultActionTrait;

    public Substance $substance;

    public ?int $limit = 2;

    public function __construct(
        private readonly DispenseRepository $dispenseRepository,
        private readonly PaginationService  $paginationService
    )
    {
    }

    public function getDispenses(): PaginatedResult
    {

        $query = $this->dispenseRepository->createBySubstanceQueryBuilder($this->substance);

        return $this->paginationService->paginate($query, PaginationRequest::create(1, $this->limit));
    }
}
