<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\ORM\EntityRepository;
use TaskManager\Shared\Application\Criteria\CriteriaFieldValidatorInterface;
use TaskManager\Shared\Domain\Criteria\Criteria;

final readonly class CriteriaFinder implements CriteriaFinderInterface
{
    public function __construct(
        private DoctrineExpressionFromCriteriaBuilderInterface $builder,
        private CriteriaFieldValidatorInterface $validator
    ) {
    }

    /**
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function findAllByCriteria(EntityRepository $repository, Criteria $criteria): array
    {
        $this->validator->validate($criteria, $repository->getClassName());

        return $this->builder->build($repository, $criteria)
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

        $operands = [];
        foreach ($criteria->getExpression()->getOperands() as $item) {
            $operands[] = $item[1];
        }
        $countCriteria = new Criteria($operands);

        return $this->builder->build($repository, $countCriteria)
            ->select('count(t)')
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

        return $this->builder->build($repository, $criteria)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
