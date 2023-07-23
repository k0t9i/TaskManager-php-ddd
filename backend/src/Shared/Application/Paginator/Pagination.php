<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Paginator;

use TaskManager\Shared\Domain\Exception\PageNotExistException;

final class Pagination
{
    public const PAGE_SIZE = 10;

    public function __construct(
        private readonly array $items,
        private readonly int $totalCount,
        private readonly ?int $offset,
        private readonly ?int $limit,
    ) {
        $this->ensureIsValidCurrentPage();
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getTotalPageCount(): int
    {
        if (null === $this->limit) {
            return 0 === $this->totalCount ? 0 : 1;
        }

        return (int) ceil($this->totalCount / $this->limit);
    }

    public function getCurrentPage(): int
    {
        return null === $this->limit ? 1 : ((int) floor((int) $this->offset / $this->limit)) + 1;
    }

    public function getNextPage(): ?int
    {
        $next = $this->getCurrentPage() + 1;

        return $next > $this->getTotalPageCount() ? null : $next;
    }

    public function getPreviousPage(): ?int
    {
        $prev = $this->getCurrentPage() - 1;

        return $prev <= 0 ? null : $prev;
    }

    private function ensureIsValidCurrentPage(): void
    {
        if (0 === $this->getTotalPageCount() && 1 === $this->getCurrentPage()) {
            return;
        }
        if ($this->getCurrentPage() > $this->getTotalPageCount() || $this->getCurrentPage() < 1) {
            throw new PageNotExistException($this->getCurrentPage());
        }
    }
}
