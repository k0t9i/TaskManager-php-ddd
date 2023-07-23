<?php

declare(strict_types=1);

namespace TaskManager\Projections\Infrastructure\Repository;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use TaskManager\Projections\Domain\Entity\TaskListProjection;
use TaskManager\Projections\Domain\Repository\TaskListProjectionRepositoryInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Infrastructure\Criteria\CriteriaFinderInterface;

final readonly class DoctrineTaskListProjectionRepository implements TaskListProjectionRepositoryInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CriteriaFinderInterface $finder
    ) {
    }

    public function findById(string $id): ?TaskListProjection
    {
        return $this->getRepository()->findOneBy([
            'id' => $id,
        ]);
    }

    /**
     * @return TaskListProjection[]
     */
    public function findAllByOwnerId(string $id): array
    {
        return $this->getRepository()->findBy([
            'ownerId' => $id,
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function countByProjectAndOwnerId(string $projectId, string $ownerId): int
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder('t');

        $queryBuilder->select('count(t.id)')
            ->where('t.projectId = :projectId')
            ->andWhere('t.ownerId = :ownerId');

        $queryBuilder->setParameter('projectId', $projectId);
        $queryBuilder->setParameter('ownerId', $ownerId);

        return $queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function save(TaskListProjection $projection): void
    {
        $this->entityManager->persist($projection);
        $this->entityManager->flush();
    }

    public function delete(TaskListProjection $projection): void
    {
        $this->entityManager->remove($projection);
        $this->entityManager->flush();
    }

    /**
     * @return TaskListProjection[]
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
        return $this->entityManager->getRepository(TaskListProjection::class);
    }
}
