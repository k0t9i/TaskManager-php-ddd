<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use TaskManager\Shared\Application\Service\DomainEventMapperInterface;
use TaskManager\Shared\Domain\Event\DomainEvent;
use TaskManager\Shared\Domain\Event\UserWasCreatedDomainEvent;
use TaskManager\Shared\Domain\Exception\InvalidArgumentException;

final class DomainEventMapper implements DomainEventMapperInterface
{
    private array $map = [];

    public function __construct(private readonly array $events)
    {
        $this->indexMap();
    }

    /**
     * @inheritDoc
     */
    public function getEventMap(): array
    {
        return $this->map;
    }

    private function indexMap(): void
    {
        if (empty($this->map)) {
            foreach ($this->events as $eventClass) {
                if (!is_subclass_of($eventClass, DomainEvent::class)) {
                    throw new InvalidArgumentException(
                        sprintf('"%s" must be instance of DomainEvent', $eventClass)
                    );
                }
                $eventName = $eventClass::getEventName();
                $this->map[$eventName][] = $eventClass;
            }
        }
    }
}