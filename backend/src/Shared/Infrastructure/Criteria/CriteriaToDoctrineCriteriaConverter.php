<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use Doctrine\Common\Collections\Expr\Comparison;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\LogicalOperatorEnum;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

final class CriteriaToDoctrineCriteriaConverter implements CriteriaToDoctrineCriteriaConverterInterface
{
    public function convert(Criteria $criteria): DoctrineCriteria
    {
        $result = new DoctrineCriteria();

        foreach ($criteria->getExpression()->getOperands() as $item) {
            $operator = self::getComparisonOperator($item[1]->operator);
            if (LogicalOperatorEnum::And === $item[0]) {
                $result->andWhere(new Comparison($item[1]->property, $operator, $item[1]->value));
            } else {
                $result->orWhere(new Comparison($item[1]->property, $operator, $item[1]->value));
            }
        }
        $orderings = [];
        foreach ($criteria->getOrders() as $order) {
            $orderings[$order->property] = $order->isAsc ? DoctrineCriteria::ASC : DoctrineCriteria::DESC;
        }
        $result->orderBy($orderings);

        $result->setFirstResult($criteria->getOffset() ?? 0);
        $result->setMaxResults($criteria->getLimit());

        return $result;
    }

    public static function getComparisonOperator(OperatorEnum $operator): string
    {
        return match ($operator) {
            OperatorEnum::Equal => Comparison::EQ,
            OperatorEnum::NotEqual => Comparison::NEQ,
            OperatorEnum::Greater => Comparison::GT,
            OperatorEnum::GreaterOrEqual => Comparison::GTE,
            OperatorEnum::Less => Comparison::LT,
            OperatorEnum::LessOrEqual => Comparison::LTE,
            OperatorEnum::In => Comparison::IN,
            OperatorEnum::NotIn => Comparison::NIN,
            OperatorEnum::Like => Comparison::CONTAINS
        };
    }
}
