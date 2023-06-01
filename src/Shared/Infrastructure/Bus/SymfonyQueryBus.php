<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Bus;

use TaskManager\Shared\Application\Bus\Query\QueryBusInterface;
use TaskManager\Shared\Application\Bus\Query\QueryInterface;
use TaskManager\Shared\Application\Bus\Query\QueryResponseInterface;
use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;

final class SymfonyQueryBus implements QueryBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $queryBus)
    {
        $this->messageBus = $queryBus;
    }

    public function dispatch(QueryInterface $query): QueryResponseInterface
    {
        return $this->handle($query);
    }
}
