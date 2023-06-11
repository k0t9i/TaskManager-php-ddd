<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface as UuidGeneratorInterfaceAlias;

final class RamseyUuid4Generator implements UuidGeneratorInterfaceAlias
{
    public function __construct(private ?UuidInterface $uuid = null)
    {
        if (null === $this->uuid) {
            $this->uuid = Uuid::uuid4();
        }
    }

    public function generate(): string
    {
        return $this->uuid ? $this->uuid->toString() : '';
    }
}
