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

    private array $deletedProjections = [];

    /**
     * @return Hashable[]
     */
    public function getProjections(): array
    {
        return array_filter($this->projections, function ($value) {
            return !isset($this->deletedProjections[$value->getHash()]);
        });
    }

    /**
     * @return Hashable[]
     */
    public function getDeletedProjections(): array
    {
        return array_filter($this->projections, function ($value) {
            return isset($this->deletedProjections[$value->getHash()]);
        });
    }

    public function flush(): void
    {
        $this->projections = [];
        $this->deletedProjections = [];
    }

    public function getProjection(string $hash): ?Hashable
    {
        return $this->projections[$hash] ?? null;
    }

    /**
     * @param Hashable[] $projections
     */
    public function loadProjections(array $projections): void
    {
        foreach ($projections as $projection) {
            $this->loadProjection($projection);
        }
    }

    public function loadProjection(Hashable $projection): void
    {
        $this->projections[$projection->getHash()] = $projection;
    }

    public function deleteProjection(?Hashable $projection): void
    {
        if (null === $projection) {
            return;
        }

        $this->loadProjection($projection);

        $this->deletedProjections[$projection->getHash()] = $projection->getHash();
    }
}
