<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

interface ProjectorInterface
{
    public function projectWhen(DomainEventInterface $event): void;

    public function flush(): void;
}
