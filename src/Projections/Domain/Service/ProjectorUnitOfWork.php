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
    public function getProjections(callable $callback = null): array
    {
        $result = array_filter($this->projections, function ($value) {
            return !isset($this->deletedProjections[$value->getHash()]);
        });

        if (null === $callback) {
            return $result;
        }

        return array_filter($result, function ($value) use ($callback) {
            return call_user_func($callback, $value);
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

    public function createProjection(Hashable $projection): void
    {
        $this->loadProjection($projection);
        unset($this->deletedProjections[$projection->getHash()]);
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
