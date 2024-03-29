<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Bus;

use Symfony\Component\Messenger\HandleTrait;
use Symfony\Component\Messenger\MessageBusInterface;
use TaskManager\Shared\Application\Bus\Command\CommandBusInterface;
use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final class SymfonyCommandBus implements CommandBusInterface
{
    use HandleTrait;

    public function __construct(MessageBusInterface $commandBus)
    {
        $this->messageBus = $commandBus;
    }

    public function dispatch(CommandInterface $command): mixed
    {
        return $this->handle($command);
    }
}
