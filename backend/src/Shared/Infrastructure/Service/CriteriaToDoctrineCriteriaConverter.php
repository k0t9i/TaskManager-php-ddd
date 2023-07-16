<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use Doctrine\Common\Collections\Expr\Comparison;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\LogicalOperatorEnum;

final class CriteriaToDoctrineCriteriaConverter implements CriteriaToDoctrineCriteriaConverterInterface
{
    public function convert(Criteria $criteria): DoctrineCriteria
    {
        $result = new DoctrineCriteria();

        foreach ($criteria->getExpression()->getOperands() as $item) {
            if (LogicalOperatorEnum::And === $item[0]) {
                $result->andWhere(new Comparison($item[1]->property, $item[1]->operator->value, $item[1]->value));
            } else {
                $result->orWhere(new Comparison($item[1]->property, $item[1]->operator->value, $item[1]->value));
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
}
