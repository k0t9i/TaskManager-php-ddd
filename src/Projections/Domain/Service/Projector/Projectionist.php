<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service\Projector;

use TaskManager\Projections\Domain\DTO\ProjectionistResultDTO;
use TaskManager\Projections\Domain\Repository\TransactionManagerInterface;
use TaskManager\Projections\Domain\Service\EventStore\EventStoreInterface;

final readonly class Projectionist implements ProjectionistInterface
{
    private array $projectors;

    /**
     * @param iterable<int, ProjectorInterface> $projectors
     */
    public function __construct(
        iterable $projectors,
        private EventStoreInterface $eventStore,
        private ProjectorPositionHandlerInterface $positionHandler,
        private TransactionManagerInterface $transactionManager
    ) {
        $this->projectors = $this->prioritizeProjectors($projectors);
    }

    /**
     * @return ProjectionistResultDTO[]
     *
     * @throws \Exception
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

            $this->transactionManager->withTransaction(function () use ($streamInfo, $projector) {
                try {
                    while (null !== $event = $streamInfo->stream->next()) {
                        $projector->projectWhen($event);
                    }

                    $this->positionHandler->storePosition($projector, $streamInfo->lastPosition);
                    $this->positionHandler->flushPosition($projector);
                } catch (\Exception $e) {
                    $this->positionHandler->markAsBroken($projector);
                    $this->positionHandler->flushPosition($projector);
                    throw $e;
                }
            });

            $result[] = new ProjectionistResultDTO($projector::class, $streamInfo->stream->eventCount());
        }

        return $result;
    }

    /**
     * @param iterable<int, ProjectorInterface> $projectorsGenerator
     *
     * @return ProjectorInterface[]
     */
    private function prioritizeProjectors(iterable $projectorsGenerator): array
    {
        $projectors = [...$projectorsGenerator];
        usort($projectors, function (ProjectorInterface $left, ProjectorInterface $right) {
            if ($left->priority() === $right->priority()) {
                return 0;
            }

            return ($left->priority() < $right->priority()) ? 1 : -1;
        });

        return $projectors;
    }
}
