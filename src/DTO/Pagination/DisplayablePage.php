<?php

namespace App\DTO\Pagination;

use Symfony\Component\Validator\Constraints as Assert;

class DisplayablePage
{

    #[Assert\Positive]
    public int $position = 1;

    public bool $isActive = false;

    public bool $isEllipsis = false;

    public static function create(int $position, bool $isActive, bool $isEllipsis): self
    {
        $page = new self();
        $page->position = $position;
        $page->isActive = $isActive;
        $page->isEllipsis = $isEllipsis;

        return $page;
    }
}
