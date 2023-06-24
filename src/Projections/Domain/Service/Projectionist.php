<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

final readonly class Projectionist implements ProjectionistInterface
{
    /**
     * @param ProjectorInterface[] $projectors
     */
    public function __construct(
        private iterable $projectors,
        private EventStoreInterface $eventStore,
        private ProjectorPositionHandler $positionHandler
    ) {
    }

    /**
     * @throws \Exception
     */
    public function projectAll(): void
    {
        foreach ($this->projectors as $projector) {
            if ($this->positionHandler->isBroken($projector)) {
                continue;
            }

            $position = $this->positionHandler->getPosition($projector);
            $stream = $this->eventStore->getStream($position);

            if (0 === $stream->eventCount()) {
                continue;
            }

            while (null !== $event = $stream->next()) {
                $position = new \DateTimeImmutable($event->getOccurredOn());
                try {
                    $projector->projectWhen($event);
                } catch (\Exception $e) {
                    $this->positionHandler->markAsBroken($projector);
                    //TODO log exception
                }
            }

            $projector->flush();
            $this->positionHandler->storePosition($projector, $position);
            $this->positionHandler->flush();
        }
    }
}
