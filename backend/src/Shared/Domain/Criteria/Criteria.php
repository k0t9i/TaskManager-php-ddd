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
        $this->expression = new Expression();
        foreach ($filters as $filter) {
            $this->addOperand($filter);
        }
    }

    public function reset(): self
    {
        $this->expression = new Expression();
        $this->resetOrders();
        $this->offset = null;
        $this->limit = null;

        return $this;
    }

    public function resetOrders(): self
    {
        $this->orders = [];

        return $this;
    }

    public function addOperand(Operand $operand): self
    {
        $this->expression->andOperand($operand);

        return $this;
    }

    public function addOrder(Order $order): self
    {
        $this->orders[] = $order;

        return $this;
    }

    public function setLimit(?int $limit): self
    {
        $this->limit = $limit;

        return $this;
    }

    public function setOffset(?int $offset): self
    {
        $this->offset = $offset;

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
