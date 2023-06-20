<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

interface DomainEventMapperInterface
{
    /**
     * @return array<array-key, class-string>
     */
    public function getEventClasses(string $eventName): array;
}
