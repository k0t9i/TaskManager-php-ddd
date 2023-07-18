<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\ORM\EntityRepository;
use TaskManager\Shared\Application\Criteria\CriteriaFieldValidatorInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;

final readonly class CriteriaFinder implements CriteriaFinderInterface
{
    public function __construct(
        private CriteriaToDoctrineCriteriaConverterInterface $converter,
        private CriteriaFieldValidatorInterface $validator
    ) {
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findAllByCriteria(EntityRepository $repository, Criteria $criteria): array
    {
        $this->validator->validate($criteria, $repository->getClassName());

        return $repository->createQueryBuilder('t')
            ->addCriteria($this->converter->convert($criteria))
            ->getQuery()
            ->getResult();
    }

    /**
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findCountByCriteria(EntityRepository $repository, Criteria $criteria): int
    {
        $this->validator->validate($criteria, $repository->getClassName());

        $doctrineCriteria = $this->converter->convert($criteria);

        $doctrineCriteria->setFirstResult(null);
        $doctrineCriteria->setMaxResults(null);
        $doctrineCriteria->orderBy([]);

        return $repository->createQueryBuilder('t')
            ->select('count(t)')
            ->addCriteria($doctrineCriteria)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findByCriteria(EntityRepository $repository, Criteria $criteria): mixed
    {
        $this->validator->validate($criteria, $repository->getClassName());

        return $repository->createQueryBuilder('t')
            ->addCriteria($this->converter->convert($criteria))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
