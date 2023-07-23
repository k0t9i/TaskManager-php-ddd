<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Paginator;

use TaskManager\Shared\Application\Paginator\Pagination;

final readonly class PaginationResponseDTO implements \JsonSerializable
{
    public function __construct(
        public array $items,
        public int $total,
        public int $current,
        public ?int $next,
        public ?int $previous,
    ) {
    }

    public static function createFromPagination(Pagination $pagination, callable $itemsCallback = null): self
    {
        return new self(
            null === $itemsCallback ? $pagination->getItems() : call_user_func($itemsCallback, $pagination->getItems()),
            $pagination->getTotalPageCount(),
            $pagination->getCurrentPage(),
            $pagination->getNextPage(),
            $pagination->getPreviousPage()
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'items' => $this->items,
            'page' => [
                'total' => $this->total,
                'current' => $this->current,
                'next' => $this->next,
                'previous' => $this->previous,
            ],
        ];
    }
}
