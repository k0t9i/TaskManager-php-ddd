<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

interface TransactionManagerInterface
{
    public function withTransaction(callable $callback): void;
}
