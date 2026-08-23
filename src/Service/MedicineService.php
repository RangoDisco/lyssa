<?php

namespace App\Service;

use App\DTO\Pagination\PaginatedResult;
use App\DTO\Pagination\PaginationRequest;
use App\Entity\User;
use App\Repository\MedicineRepository;
use Symfony\Component\Security\Core\User\UserInterface;

readonly class MedicineService
{

    public function __construct(
        private MedicineRepository $medicineRepository,
        private PaginationService  $paginationService
    )
    {
    }

    public function getAccessible(User|UserInterface $user, PaginationRequest $pagination): PaginatedResult
    {
        $query = $this->medicineRepository->createAccessibleQueryBuilder($user);
        return $this->paginationService->paginate($query, $pagination);
    }

}
