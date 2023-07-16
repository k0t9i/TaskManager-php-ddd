<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

interface UuidGeneratorInterface
{
    public function generate(): string;
}
