<?php

namespace App\DTO\Pagination;

use Symfony\Component\Validator\Constraints as Assert;


class PaginationRequest
{

    #[Assert\Positive]
    public int $page = 1;

    #[Assert\Positive]
    public int $limit = 10;

    public ?string $sort = null;

    #[Assert\Choice(callback: 'getDirections')]
    public string $direction = 'DESC';

    public function getOffset(): int
    {
        return ($this->page - 1) * $this->limit;
    }

    public function getDirections() {
        return ['ASC', 'DESC'];
    }

    public static function create(
        int $page = 1,
        int $limit = 10,
        ?string $sort = null,
        string $direction = 'DESC',
    ): self {
        $request = new self();
        $request->page = $page;
        $request->limit = $limit;
        $request->sort = $sort;
        $request->direction = $direction;

        return $request;
    }
}
