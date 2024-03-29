<?php

declare(strict_types=1);

namespace TaskManager\Shared\Application\Criteria;

use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Exception\CriteriaFilterNotExistException;
use TaskManager\Shared\Domain\Exception\CriteriaOrderNotExistException;

final class CriteriaFieldValidator implements CriteriaFieldValidatorInterface
{
    /**
     * @param class-string $class
     *
     * @throws \ReflectionException
     */
    public function validate(Criteria $criteria, string $class): void
    {
        $reflection = new \ReflectionClass($class);
        /**
         * @var Operand $operand
         */
        foreach ($criteria->getExpression()->getOperands() as [$operator, $operand]) {
            if (!$this->checkProperty($reflection, $operand->property)) {
                throw new CriteriaFilterNotExistException($operand->property);
            }
        }

        foreach ($criteria->getOrders() as $order) {
            if (!$this->checkProperty($reflection, $order->property)) {
                throw new CriteriaOrderNotExistException($order->property);
            }
        }
    }

    private function checkProperty(\ReflectionClass $reflection, string $propertyName): bool
    {
        return $reflection->hasProperty($propertyName);
    }
}
