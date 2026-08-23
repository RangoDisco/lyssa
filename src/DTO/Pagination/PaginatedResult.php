<?php

namespace App\DTO\Pagination;

use Countable;
use Doctrine\ORM\Tools\Pagination\Paginator;
use IteratorAggregate;
use Traversable;

class PaginatedResult implements IteratorAggregate, Countable
{

    public function __construct(
        private readonly Paginator $paginator,
        public int                 $page,
        public int                 $limit,
    )
    {
    }

    public function getIterator(): Traversable
    {
        return $this->paginator->getIterator();
    }

    public function count(): int
    {
        return $this->paginator->count();
    }

    public function getTotalPages(): int
    {
        return max(1, (int)ceil($this->count() / $this->limit));
    }

    public function getPreviousPage(): ?int
    {
        return $this->page > 1 ? $this->page - 1 : null;
    }

    public function getNextPage(): ?int
    {
        return $this->page < $this->getTotalPages() ? $this->page + 1 : null;
    }
}
