<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\ORM\QueryBuilder;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\LogicalOperatorEnum;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final readonly class DoctrineExpressionFromCriteriaBuilder implements DoctrineExpressionFromCriteriaBuilderInterface
{
    public function __construct(
        private DoctrineFilterValueSanitizerInterface $sanitizer
    ) {
    }

    /**
     * @throws Exception
     * @throws MappingException
     */
    public function build(EntityRepository $repository, Criteria $criteria, string $alias = 't'): QueryBuilder
    {
        $builder = $repository->createQueryBuilder($alias);

        $classMetadata = $builder->getEntityManager()->getClassMetadata($repository->getClassName());

        foreach ($criteria->getExpression()->getOperands() as $item) {
            $this->processOperand($builder, $item[0], $item[1], $classMetadata, $alias);
        }

        foreach ($criteria->getOrders() as $order) {
            $builder->addOrderBy($alias.'.'.$order->property, $order->isAsc ? 'ASC' : 'DESC');
        }

        if (null !== $criteria->getOffset()) {
            $builder->setFirstResult($criteria->getOffset());
        }
        if (null !== $criteria->getLimit()) {
            $builder->setMaxResults($criteria->getLimit());
        }

        return $builder;
    }

    /**
     * @throws Exception
     * @throws MappingException
     */
    private function processOperand(
        QueryBuilder $builder,
        LogicalOperatorEnum $logicalOperator,
        Operand $operand,
        ClassMetadata $classMetadata,
        string $alias): void
    {
        $expr = $builder->expr();

        $property = $alias.'.'.$operand->property;
        $param = ':'.str_replace('.', '_', $property);
        $value = $operand->value;
        if (OperatorEnum::Like === $operand->operator) {
            $property = $this->castAsString($property);
            $value = '%'.$value.'%';
        } else {
            $fieldMapping = $classMetadata->getFieldMapping($operand->property);
            $type = Type::getType($fieldMapping['type']);

            $value = $this->sanitizer->sanitize($type, $value);
        }

        $condition = match ($operand->operator) {
            OperatorEnum::Equal => null !== $value ? $expr->eq($property, $param) : $expr->isNull($property),
            OperatorEnum::NotEqual => null !== $value ? $expr->neq($property, $param) : $expr->isNotNull($property),
            OperatorEnum::Greater => $expr->gt($property, $param),
            OperatorEnum::GreaterOrEqual => $expr->gte($property, $param),
            OperatorEnum::Less => $expr->lt($property, $param),
            OperatorEnum::LessOrEqual => $expr->lte($property, $param),
            OperatorEnum::In => $expr->in($property, $param),
            OperatorEnum::NotIn => $expr->notIn($property, $param),
            OperatorEnum::Like => $expr->like($property, $param)
        };

        if (LogicalOperatorEnum::And === $logicalOperator) {
            $builder->andWhere($condition);
        } else {
            $builder->orWhere($condition);
        }
        if (!is_string($condition) || null !== $value) {
            $builder->setParameter($param, $value);
        }
    }

    private function castAsString(string $property): Func
    {
        return new Func('CAST', $property.' AS string');
    }
}
