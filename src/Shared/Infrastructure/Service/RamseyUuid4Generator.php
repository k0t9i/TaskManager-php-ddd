<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Ramsey\Uuid\UuidFactoryInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface as UuidGeneratorInterfaceAlias;

final readonly class RamseyUuid4Generator implements UuidGeneratorInterfaceAlias
{
    public function __construct(private UuidFactoryInterface $factory)
    {
    }

    public function generate(): string
    {
        return $this->factory->uuid4()->toString();
    }
}
