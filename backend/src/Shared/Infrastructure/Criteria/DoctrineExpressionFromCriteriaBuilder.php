<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\MappingException;
use Doctrine\ORM\Query\Expr\Comparison;
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
        $paramPlaceholder = ':'.str_replace('.', '_', $property);
        if (OperatorEnum::Like === $operand->operator) {
            $value = '%'.$operand->value.'%';
        } else {
            $fieldMapping = $classMetadata->getFieldMapping($operand->property);
            $type = Type::getType($fieldMapping['type']);
            $value = $this->sanitizer->sanitize($type, $operand->value);
        }

        $condition = match ($operand->operator) {
            OperatorEnum::Equal => null !== $value ? $expr->eq($property, $paramPlaceholder) : $expr->isNull($property),
            OperatorEnum::NotEqual => null !== $value ? $expr->neq($property, $paramPlaceholder) : $expr->isNotNull($property),
            OperatorEnum::Greater => $expr->gt($property, $paramPlaceholder),
            OperatorEnum::GreaterOrEqual => $expr->gte($property, $paramPlaceholder),
            OperatorEnum::Less => $expr->lt($property, $paramPlaceholder),
            OperatorEnum::LessOrEqual => $expr->lte($property, $paramPlaceholder),
            OperatorEnum::In => $expr->in($property, $paramPlaceholder),
            OperatorEnum::NotIn => $expr->notIn($property, $paramPlaceholder),
            OperatorEnum::Like => new Comparison($this->castAsString($property), 'LIKE', $paramPlaceholder)
        };

        if (LogicalOperatorEnum::And === $logicalOperator) {
            $builder->andWhere($condition);
        } else {
            $builder->orWhere($condition);
        }
        if (!is_string($condition) || null !== $value) {
            $builder->setParameter($paramPlaceholder, $value);
        }
    }

    private function castAsString(string $property): Func
    {
        return new Func('CAST', $property.' AS string');
    }
}
