<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Command;

use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final readonly class ConfirmRequestCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public string $projectId
    ) {
    }
}
