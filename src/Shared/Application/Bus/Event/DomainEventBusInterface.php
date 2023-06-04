<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Event;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface DomainEventBusInterface
{
    public function dispatch(DomainEventInterface ...$events): void;
}
