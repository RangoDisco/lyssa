<?php

namespace App\Twig\Components;

use App\DTO\Pagination\DisplayablePage;
use App\DTO\Pagination\PaginatedResult;
use App\Repository\DispenseRepository;
use App\Repository\SubstanceRepository;
use App\Service\PaginationService;
use Symfony\UX\LiveComponent\DefaultActionTrait;
use Symfony\UX\TwigComponent\Attribute\AsTwigComponent;

#[AsTwigComponent]
class Paginator
{

    private const int SIBLING_COUNT = 2;

    public PaginatedResult $result;
    private array $pages = [];

    /**
     * @return DisplayablePage[]
     */
    public function getPages(): array
    {
        $start = $this->result->page;
        $end = $this->result->getTotalPages();

        return match (true) {
            // Page 1 to 3
            $start - self::SIBLING_COUNT <= 1 => $this->buildStartingPages($start, $end),
            // Page last and last - 2
            $start + self::SIBLING_COUNT >= $end => $this->buildEndingPages($end),
            default => $this->buildPackPages($start, $end),
        };
    }

    /**
     * @return DisplayablePage[]
     */
    private function buildStartingPages(int $start, int $end): array
    {
        // Sibling count + 2 bc we start the loop at 1 instead of the current page
        for ($i = 1; $i <= (self::SIBLING_COUNT + 2); $i++) {
            $this->pages[] = DisplayablePage::create($i, $i === $this->result->page, false);
        }
        $this->pages[] = DisplayablePage::create($start + self::SIBLING_COUNT + 1, false, true);
        $this->pages[] = DisplayablePage::create($end, false, false);

        return $this->pages;
    }

    /**
     * @return DisplayablePage[]
     */
    private function buildEndingPages(int $end): array
    {
        // Sibling count - 1 bc we start the loop at the end instead of the current page
        for ($i = $end; $i >= ($end - self::SIBLING_COUNT - 1); $i--) {
            array_unshift($this->pages, DisplayablePage::create($i, $i === $this->result->page, false));
        }
        array_unshift($this->pages, DisplayablePage::create(2, false, true));

        array_unshift($this->pages, DisplayablePage::create(1, false, false));

        return $this->pages;
    }

    /**
     * @return DisplayablePage[]
     */
    private function buildPackPages(int $start, int $end): array
    {
        $this->pages[] = DisplayablePage::create(1, false, false);
        $this->pages[] = DisplayablePage::create(2, false, true);
        for ($i = $start - 1; $i < $start + self::SIBLING_COUNT; $i++) {
            $this->pages[] = DisplayablePage::create($i, $i === $this->result->page, false);

        }
        $this->pages[] = DisplayablePage::create($start + self::SIBLING_COUNT + 1, false, true);
        $this->pages[] = DisplayablePage::create($end, false, false);

        return $this->pages;
    }
}
