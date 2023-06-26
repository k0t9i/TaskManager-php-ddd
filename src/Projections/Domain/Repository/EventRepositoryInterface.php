<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\Event;
use TaskManager\Shared\Domain\ValueObject\DateTime;

interface EventRepositoryInterface
{
    /**
     * @return Event[]
     */
    public function findOrderedFromLastTime(?DateTime $lastDatetime): array;

    public function save(Event $event): void;
}
