<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

final readonly class Operand
{
    public function __construct(
        public string $property,
        public OperatorEnum $operator,
        public mixed $value
    ) {
        $this->ensureIsValidValueType($this->operator, $this->value);
    }

    private function ensureIsValidValueType(OperatorEnum $operator, mixed $value): void
    {
        $isArrayOperator = in_array($operator, [OperatorEnum::In, OperatorEnum::NotIn]);
        if ($isArrayOperator && !is_array($value) || !$isArrayOperator && is_array($value)) {
            throw new \LogicException(sprintf('Invalid criteria value type "%s"', gettype($value)));
        }
    }
}
