<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Domain\Criteria;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Expression;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;

class CriteriaTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testCreate(): void
    {
        $operands = [
            new Operand(
                $this->faker->regexify('.{255}'),
                OperatorEnum::Greater,
                $this->faker->regexify('.{255}')
            ),
            new Operand(
                $this->faker->regexify('.{255}'),
                OperatorEnum::Less,
                $this->faker->regexify('.{255}')
            ),
            new Operand(
                $this->faker->regexify('.{255}'),
                OperatorEnum::In,
                [
                    $this->faker->regexify('.{255}'),
                ]
            ),
            new Operand(
                $this->faker->regexify('.{255}'),
                OperatorEnum::Equal,
                $this->faker->regexify('.{255}')
            ),
        ];
        $orders = [
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
        ];
        $offset = $this->faker->numberBetween();
        $limit = $this->faker->numberBetween();
        $expectedExpression = new Expression();
        foreach ($operands as $operand) {
            $expectedExpression->andOperand($operand);
        }
        $criteria = new Criteria($operands, $orders, $offset, $limit);

        $this->assertEquals($expectedExpression, $criteria->getExpression());
        $this->assertEquals($orders, $criteria->getOrders());
        $this->assertEquals($offset, $criteria->getOffset());
        $this->assertEquals($limit, $criteria->getLimit());
    }
}
