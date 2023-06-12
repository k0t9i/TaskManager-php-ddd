<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain;

interface Hashable
{
    public function getHash(): string;
}
