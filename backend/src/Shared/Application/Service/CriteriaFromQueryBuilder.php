<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Service;

use TaskManager\Shared\Application\DTO\QueryCriteriaDTO;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;
use TaskManager\Shared\Domain\Exception\CriteriaFilterOperatorNotExistException;

final class CriteriaFromQueryBuilder implements CriteriaFromQueryBuilderInterface
{
    public function build(Criteria $criteria, QueryCriteriaDTO $dto): Criteria
    {
        foreach ($dto->filters as $filterMetadata => $value) {
            $parts = explode(':', $filterMetadata);

            $operator = OperatorEnum::Equal;
            $property = $parts[0];
            if (count($parts) > 1) {
                $operator = OperatorEnum::tryFrom(mb_strtolower($parts[1]));
                if (null === $operator) {
                    throw new CriteriaFilterOperatorNotExistException($parts[1], $property);
                }
            }

            $criteria->addOperand(new Operand($property, $operator, $value));
        }

        // Reset default ordering
        $criteria->resetOrders();
        foreach ($dto->orders as $name => $isAsc) {
            $criteria->addOrder(new Order($name, (bool) $isAsc));
        }

        $criteria->setOffset($dto->offset)
            ->setLimit($dto->limit);

        return $criteria;
    }
}
