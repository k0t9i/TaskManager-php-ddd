<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Command;

use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final readonly class RemoveParticipantCommand implements CommandInterface
{
    public function __construct(
        public string $projectId,
        public string $participantId
    ) {
    }
}
