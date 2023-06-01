<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Event;

use TaskManager\Shared\Domain\Event\Event;

interface EventBusInterface
{
    public function dispatch(Event ...$events): void;
}
