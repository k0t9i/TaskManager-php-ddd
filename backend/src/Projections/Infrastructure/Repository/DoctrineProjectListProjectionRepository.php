<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use TaskManager\Projections\Domain\Entity\ProjectListProjection;
use TaskManager\Projections\Domain\Repository\ProjectListProjectionRepositoryInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Infrastructure\Criteria\CriteriaFinderInterface;

final readonly class DoctrineProjectListProjectionRepository implements ProjectListProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CriteriaFinderInterface $finder
    ) {
    }

    /**
     * @return ProjectListProjection[]
     */
    public function findAllById(string $id): array
    {
        return $this->getRepository()->findBy([
            'id' => $id,
        ]);
    }

    /**
     * @return ProjectListProjection[]
     */
    public function findAllByOwnerId(string $id): array
    {
        return $this->getRepository()->findBy([
            'ownerId' => $id,
        ]);
    }

    /**
     * @return ProjectListProjection[]
     */
    public function findAllOwnersProjects(): array
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('t');

        $queryBuilder->where('t.userId = t.ownerId');

        return $queryBuilder->getQuery()->getResult();
    }

    public function save(ProjectListProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(ProjectListProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    /**
     * @return ProjectListProjection[]
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
        return $this->entityManager->getRepository(ProjectListProjection::class);
    }
}
