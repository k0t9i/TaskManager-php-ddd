<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Query;

interface QueryBusInterface
{
    public function dispatch(QueryInterface $query): QueryResponseInterface;
}
