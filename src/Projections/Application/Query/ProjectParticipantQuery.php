<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Query;

use TaskManager\Shared\Application\Bus\Query\QueryInterface;

final readonly class ProjectParticipantQuery implements QueryInterface
{
    public function __construct(public string $projectId)
    {
    }
}
