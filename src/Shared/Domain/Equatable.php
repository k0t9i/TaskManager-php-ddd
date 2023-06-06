<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain;

interface Equatable
{
    public function equals(Equatable $other): bool;
}
