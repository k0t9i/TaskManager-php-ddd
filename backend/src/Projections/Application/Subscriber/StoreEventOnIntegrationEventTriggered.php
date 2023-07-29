<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Subscriber;

use TaskManager\Projections\Domain\Entity\Event;
use TaskManager\Projections\Domain\Repository\EventRepositoryInterface;
use TaskManager\Projections\Domain\Service\EventStore\EventStreamFilterInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventInterface;
use TaskManager\Shared\Application\Bus\Event\IntegrationEventSubscriberInterface;
use TaskManager\Shared\Application\Service\UuidGeneratorInterface;
use TaskManager\Shared\Domain\Service\DomainEventFactoryInterface;

final readonly class StoreEventOnIntegrationEventTriggered implements IntegrationEventSubscriberInterface
{
    public function __construct(
        private EventRepositoryInterface $repository,
        private UuidGeneratorInterface $uuidGenerator,
        private DomainEventFactoryInterface $eventFactory,
        // For development purposes only: app domains pretend to be microservices
        private EventStreamFilterInterface $streamFilter
    ) {
    }

    /**
     * @throws \Exception
     */
    public function __invoke(IntegrationEventInterface $integrationEvent): void
    {
        $domainEvents = $this->eventFactory->create(
            $integrationEvent->getDomainEventName(),
            $integrationEvent->getAggregateId(),
            $integrationEvent->getBody(),
            $integrationEvent->getPerformerId(),
            $integrationEvent->getOccurredOn()
        );

        foreach ($domainEvents as $domainEvent) {
            // For development purposes only: app domains pretend to be microservices
            if (!$this->streamFilter->isSuitable($domainEvent)) {
                continue;
            }

            $event = Event::fromDomainEvent(
                $this->uuidGenerator->generate(),
                $domainEvent,
                $integrationEvent->getVersion()
            );

            $this->repository->save($event);
        }
    }
}
