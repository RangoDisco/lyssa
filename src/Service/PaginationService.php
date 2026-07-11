<?php

namespace App\Service;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PaginationService
{

    const int DEFAULT_LIMIT = 10;
    const int MAX = 100;

    public function paginate(QueryBuilder $qb, int $page = 1, ?int $limit = null): array
    {
        if ($limit === null) {
            $limit = self::MAX;
        }

        $limit = min($limit, self::DEFAULT_LIMIT);
        $offset = ($page - 1) * $limit;

        $qb
            ->setMaxResults($limit)
            ->setFirstResult($offset)
            ->getQuery();

        $paginator = new Paginator($qb);

        return
            [
                'results' => $paginator,
                'previous' => $offset - $limit,
                'next' => min(count($paginator), $offset + $limit)
            ];
    }
}
