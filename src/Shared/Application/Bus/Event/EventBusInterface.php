<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Event;

use TaskManager\Shared\Domain\Event\DomainEvent;

interface EventBusInterface
{
    public function dispatch(DomainEvent ...$events): void;
}
