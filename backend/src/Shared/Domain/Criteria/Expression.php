<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

final class Expression
{
    private array $operands = [];

    public function __construct(Operand $operand = null)
    {
        if (null !== $operand) {
            $this->andOperand($operand);
        }
    }

    public function andOperand(Operand $operand): self
    {
        $this->operands[] = [
            LogicalOperatorEnum::And,
            $operand,
        ];

        return $this;
    }

    public function orOperand(Operand $operand): self
    {
        $this->operands[] = [
            LogicalOperatorEnum::Or,
            $operand,
        ];

        return $this;
    }

    /**
     * @return array<array-key, array{0: LogicalOperatorEnum, 1: Operand}>
     */
    public function getOperands(): array
    {
        return $this->operands;
    }
}
