<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Repository;

use TaskManager\Projections\Domain\Entity\Event;

interface EventRepositoryInterface
{
    /**
     * @return Event[]
     */
    public function findOrderedFromLastTime(\DateTimeImmutable $lastDatetime): array;

    public function save(Event $event): void;
}
