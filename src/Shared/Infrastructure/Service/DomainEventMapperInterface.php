<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

interface DomainEventMapperInterface
{
    /**
     * @return array
     */
    public function getEventMap(): array;
}
