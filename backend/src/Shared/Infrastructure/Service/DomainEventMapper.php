<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use TaskManager\Shared\Domain\Event\DomainEventInterface;

final class DomainEventMapper implements DomainEventMapperInterface
{
    private array $map = [];

    public function __construct(private readonly array $events)
    {
        $this->indexMap();
    }

    /**
     * @return array<array-key, class-string>
     */
    public function getEventClasses(string $eventName): array
    {
        return $this->map[$eventName] ?? [];
    }

    private function indexMap(): void
    {
        if (empty($this->map)) {
            foreach ($this->events as $eventClass) {
                if (!is_subclass_of($eventClass, DomainEventInterface::class)) {
                    throw new \LogicException(sprintf('"%s" must be instance of DomainEvent', $eventClass));
                }
                $eventName = $eventClass::getEventName();
                $this->map[$eventName][] = $eventClass;
            }
        }
    }
}
