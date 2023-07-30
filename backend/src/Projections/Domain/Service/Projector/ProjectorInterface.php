<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\DTO\DomainEventEnvelope;

interface ProjectorInterface
{
    public function projectWhen(DomainEventEnvelope $envelope): void;

    public function priority(): int;

    public function flush(): void;
}
