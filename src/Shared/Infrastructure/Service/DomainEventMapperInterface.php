<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

interface DomainEventMapperInterface
{
    public function getEventMap(): array;
}
