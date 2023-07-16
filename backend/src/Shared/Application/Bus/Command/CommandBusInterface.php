<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Bus\Command;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): mixed;
}
