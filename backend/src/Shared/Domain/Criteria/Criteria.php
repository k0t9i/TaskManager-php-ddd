<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

final class Criteria
{
    private Expression $expression;

    /**
     * @param Operand[] $filters
     * @param Order[]   $orders
     */
    public function __construct(
        array $filters = [],
        private array $orders = [],
        private ?int $offset = null,
        private ?int $limit = null
    ) {
        $first = array_shift($filters);
        $this->expression = new Expression($first);
        foreach ($filters as $filter) {
            $this->expression->andOperand($filter);
        }
    }

    public function reset(): self
    {
        $this->expression = new Expression();
        $this->orders = [];
        $this->offset = null;
        $this->limit = null;

        return $this;
    }

    public function loadScalarFilters(array $filters): self
    {
        foreach ($filters as $name => $value) {
            $operator = OperatorEnum::Equal;
            if (is_array($value)) {
                $operator = OperatorEnum::In;
            }
            $this->expression->andOperand(new Operand((string) $name, $operator, $value));
        }

        return $this;
    }

    public function loadScalarOrders(array $orders): self
    {
        foreach ($orders as $name => $isAsc) {
            $this->orders[] = new Order((string) $name, $isAsc);
        }

        return $this;
    }

    public function loadOffsetAndLimit(?int $offset, ?int $limit): self
    {
        $this->offset = $offset;
        $this->limit = $limit;

        return $this;
    }

    public function getExpression(): Expression
    {
        return $this->expression;
    }

    public function getOrders(): array
    {
        return $this->orders;
    }

    public function getOffset(): ?int
    {
        return $this->offset;
    }

    public function getLimit(): ?int
    {
        return $this->limit;
    }
}
