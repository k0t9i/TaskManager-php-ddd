<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

interface DomainEventMapperInterface
{
    /**
     * @return array
     */
    public function getEventMap(): array;
}
