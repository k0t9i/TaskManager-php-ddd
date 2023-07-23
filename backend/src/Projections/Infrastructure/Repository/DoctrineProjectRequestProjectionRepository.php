<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use TaskManager\Projections\Domain\Entity\ProjectRequestProjection;
use TaskManager\Projections\Domain\Repository\ProjectRequestProjectionRepositoryInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Infrastructure\Criteria\CriteriaFinderInterface;

final readonly class DoctrineProjectRequestProjectionRepository implements ProjectRequestProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CriteriaFinderInterface $finder
    ) {
    }

    public function findById(string $id): ?ProjectRequestProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByUserId(string $id): array
    {
        return $this->getRepository()->findBy([
            'userId' => $id,
        ]);
    }

    public function save(ProjectRequestProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    /**
     * @return ProjectRequestProjection[]
     */
    public function findAllByCriteria(Criteria $criteria): array
    {
        return $this->finder->findAllByCriteria($this->getRepository(), $criteria);
    }

    public function findCountByCriteria(Criteria $criteria): int
    {
        return $this->finder->findCountByCriteria($this->getRepository(), $criteria);
    }

    private function getRepository(): EntityRepository
    {
        return $this->entityManager->getRepository(ProjectRequestProjection::class);
    }
}
