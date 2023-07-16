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

    public function testLoadScalarOrders(): void
    {
        $expectedOrders = [
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
            new Order($this->faker->regexify('.{255}'), $this->faker->boolean()),
        ];
        $scalarOrders = [];
        foreach ($expectedOrders as $order) {
            $scalarOrders[$order->property] = $order->isAsc;
        }
        $criteria = new Criteria();

        $criteria->loadScalarOrders($scalarOrders);

        $this->assertEquals(new Expression(), $criteria->getExpression());
        $this->assertEquals($expectedOrders, $criteria->getOrders());
        $this->assertEquals(null, $criteria->getOffset());
        $this->assertEquals(null, $criteria->getLimit());
    }

    public function testLoadOffsetAndLimit(): void
    {
        $offset = $this->faker->numberBetween();
        $limit = $this->faker->numberBetween();
        $criteria = new Criteria();

        $criteria->loadOffsetAndLimit($offset, $limit);

        $this->assertEquals(new Expression(), $criteria->getExpression());
        $this->assertEquals([], $criteria->getOrders());
        $this->assertEquals($offset, $criteria->getOffset());
        $this->assertEquals($limit, $criteria->getLimit());
    }

    public function testLoadScalarFilters(): void
    {
        $filters = [
            $this->faker->regexify('.{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}') => [
                $this->faker->regexify('.{255}'),
            ],
            $this->faker->regexify('.{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}') => [
                $this->faker->regexify('.{255}'),
                $this->faker->regexify('.{255}'),
                $this->faker->regexify('.{255}'),
                $this->faker->regexify('.{255}'),
            ],
            $this->faker->regexify('.{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}') => $this->faker->regexify('.{255}'),
        ];
        $expectedExpression = new Expression();
        foreach ($filters as $name => $value) {
            $operator = OperatorEnum::Equal;
            if (is_array($value)) {
                $operator = OperatorEnum::In;
            }
            $expectedExpression->andOperand(new Operand((string) $name, $operator, $value));
        }
        $criteria = new Criteria();

        $criteria->loadScalarFilters($filters);

        $this->assertEquals($expectedExpression, $criteria->getExpression());
        $this->assertEquals([], $criteria->getOrders());
        $this->assertEquals(null, $criteria->getOffset());
        $this->assertEquals(null, $criteria->getLimit());
    }
}
