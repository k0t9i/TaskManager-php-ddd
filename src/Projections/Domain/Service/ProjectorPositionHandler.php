<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Projections\Domain\Entity\ProjectorPosition;
use TaskManager\Projections\Domain\Repository\ProjectorPositionRepositoryInterface;

final class ProjectorPositionHandler implements ProjectorPositionHandlerInterface
{
    /**
     * @var array ProjectorPosition[]
     */
    private array $positions;

    public function __construct(private readonly ProjectorPositionRepositoryInterface $repository)
    {
    }

    public function getPosition(ProjectorInterface $projector): ?\DateTimeImmutable
    {
        $position = $this->getPositionInternal($projector);

        return $position->getPosition();
    }

    public function storePosition(ProjectorInterface $projector, ?\DateTimeImmutable $position): void
    {
        $positionObject = $this->getPositionInternal($projector);

        $positionObject->adjustPosition(
            null !== $position ? \DateTime::createFromImmutable($position) : null
        );
    }

    public function isBroken(ProjectorInterface $projector): bool
    {
        $position = $this->getPositionInternal($projector);

        return $position->isBroken();
    }

    public function markAsBroken(ProjectorInterface $projector): void
    {
        $positionObject = $this->getPositionInternal($projector);

        $positionObject->markAsBroken();
    }

    public function flush(): void
    {
        foreach ($this->positions as $position) {
            $this->repository->save($position);
        }
        $this->positions = [];
    }

    private function getProjectorName(ProjectorInterface $projector): string
    {
        return $projector::class;
    }

    private function getPositionInternal(ProjectorInterface $projector): ProjectorPosition
    {
        $projectorName = $this->getProjectorName($projector);

        if (!isset($this->positions[$projectorName])) {
            $position = $this->repository->findByProjectorName($projectorName);

            if (null === $position) {
                $position = new ProjectorPosition($projectorName);
            }

            $this->positions[$projectorName] = $position;
        }

        return $this->positions[$projectorName];
    }
}
