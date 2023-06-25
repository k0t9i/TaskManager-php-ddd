<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\DTO\ProjectionistResultDTO;
use TaskManager\Projections\Domain\Service\EventStore\EventStoreInterface;

final readonly class Projectionist implements ProjectionistInterface
{
    private array $projectors;

    /**
     * @param ProjectorInterface[] $projectors
     */
    public function __construct(
        iterable $projectors,
        private EventStoreInterface $eventStore,
        private ProjectorPositionHandler $positionHandler
    ) {
        $this->prioritizeProjectors($projectors);
    }

    /**
     * @return ProjectionistResultDTO[]
     */
    public function projectAll(): array
    {
        $result = [];

        foreach ($this->projectors as $projector) {
            if ($this->positionHandler->isBroken($projector)) {
                $result[] = new ProjectionistResultDTO($projector::class, 0, true);
                continue;
            }

            $position = $this->positionHandler->getPosition($projector);
            $streamInfo = $this->eventStore->getStreamInfo($position);

            if (0 === $streamInfo->stream->eventCount()) {
                $result[] = new ProjectionistResultDTO($projector::class, 0);
                continue;
            }

            while (null !== $event = $streamInfo->stream->next()) {
                try {
                    $projector->projectWhen($event);
                } catch (\Exception $e) {
                    $this->positionHandler->markAsBroken($projector);
                    // TODO log exception
                }
            }

            if (!$this->positionHandler->isBroken($projector)) {
                $projector->flush();
            }
            $this->positionHandler->storePosition($projector, $streamInfo->lastPosition);
            $this->positionHandler->flush();

            $result[] = new ProjectionistResultDTO($projector::class, $streamInfo->stream->eventCount());
        }

        return $result;
    }

    private function prioritizeProjectors(iterable $projectorsGenerator): void
    {
        $projectors = [...$projectorsGenerator];
        usort($projectors, function (ProjectorInterface $left, ProjectorInterface $right) {
            if ($left->priority() === $right->priority()) {
                return 0;
            }

            return ($left->priority() < $right->priority()) ? 1 : -1;
        });

        $this->projectors = $projectors;
    }
}
