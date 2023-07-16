<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Domain\Criteria;

use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

class OperandTest extends TestCase
{
    public function testScalarValueInOperator(): void
    {
        $value = 12345;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Invalid criteria value type "%s"', gettype($value)));

        new Operand('property', OperatorEnum::In, $value);
    }

    public function testScalarValueNotInOperator(): void
    {
        $value = 12345;

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Invalid criteria value type "%s"', gettype($value)));

        new Operand('property', OperatorEnum::NotIn, $value);
    }

    public function testArrayValueEqualOperator(): void
    {
        $value = [1, 2, 3];

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Invalid criteria value type "%s"', gettype($value)));

        new Operand('property', OperatorEnum::Equal, $value);
    }
}
