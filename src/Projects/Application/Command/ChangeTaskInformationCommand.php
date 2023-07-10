<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Command;

use TaskManager\Shared\Application\Bus\Command\CommandInterface;

final readonly class ChangeTaskInformationCommand implements CommandInterface
{
    public function __construct(
        public string $id,
        public string $name,
        public string $brief,
        public string $description,
        public string $startDate,
        public string $finishDate
    ) {
    }
}
