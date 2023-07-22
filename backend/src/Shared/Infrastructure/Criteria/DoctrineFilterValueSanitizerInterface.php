<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\DBAL\Types\Type;

interface DoctrineFilterValueSanitizerInterface
{
    public function sanitize(Type $type, mixed $value): mixed;
}
