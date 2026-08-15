<?php

namespace App\Service;

use App\DTO\Pagination\PaginatedResult;
use App\DTO\Pagination\PaginationRequest;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{

    const int MAX = 100;

    public function paginate(QueryBuilder $qb, PaginationRequest $request): PaginatedResult
    {
        $limit = min($request->limit, self::MAX);

        if ($request->sort !== null) {
            $qb->orderBy($request->sort, $request->direction);
        }

        $qb
            ->setMaxResults($limit)
            ->setFirstResult($request->getOffset());

        $paginator = new Paginator($qb);

        return new PaginatedResult(
            $paginator,
            $request->page,
            $limit
        );
    }
}
