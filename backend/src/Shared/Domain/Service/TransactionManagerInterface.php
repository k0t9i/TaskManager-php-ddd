<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Service;

interface TransactionManagerInterface
{
    public function withTransaction(callable $callback): void;
}
