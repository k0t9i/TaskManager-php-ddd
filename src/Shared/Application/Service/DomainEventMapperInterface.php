<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

use TaskManager\Shared\Domain\Event\DomainEvent;

interface DomainEventMapperInterface
{
    /**
     * @return array
     */
    public function getEventMap(): array;
}
