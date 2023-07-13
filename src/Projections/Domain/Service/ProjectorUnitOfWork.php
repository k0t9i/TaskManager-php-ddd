<?php

declare(strict_types=1);

namespace TaskManager\Projections\Domain\Service;

use TaskManager\Shared\Domain\Hashable;

final class ProjectorUnitOfWork
{
    /**
     * @var Hashable[]
     */
    private array $projections = [];

    /**
     * @var Hashable[]
     */
    private array $deletedProjections = [];

    public function getProjections(): array
    {
        return $this->projections;
    }

    public function getDeletedProjections(): array
    {
        return $this->deletedProjections;
    }

    public function flush(): void
    {
        $this->projections = [];
        $this->deletedProjections = [];
    }

    public function getProjection(string $hash): ?Hashable
    {
        return $this->projections[$hash] ?? $this->deletedProjections[$hash] ?? null;
    }

    /**
     * @param Hashable[] $projections
     */
    public function loadProjections(array $projections, bool $force = false): void
    {
        foreach ($projections as $projection) {
            $this->addProjection($projection);
        }
    }

    public function loadProjection(Hashable $projection, bool $force = false): void
    {
        if (!$force && isset($this->projections[$projection->getHash()])) {
            return;
        }
        $this->projections[$projection->getHash()] = $projection;
    }

    public function addProjection(Hashable $projection): void
    {
        if (isset($this->projections[$projection->getHash()])) {
            throw new \RuntimeException(sprintf('Projection %s %s already exists', get_class($projection), $projection->getHash()));
        }
        $this->projections[$projection->getHash()] = $projection;
    }

    public function deleteProjection(?Hashable $projection): void
    {
        if (null === $projection) {
            return;
        }

        $this->deletedProjections[$projection->getHash()] = $projection;

        unset($this->projections[$projection->getHash()]);
    }
}
