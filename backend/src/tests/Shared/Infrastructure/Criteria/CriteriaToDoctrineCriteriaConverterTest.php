<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Criteria;

use Doctrine\Common\Collections\Criteria as DoctrineCriteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Expression;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;
use TaskManager\Shared\Infrastructure\Criteria\CriteriaToDoctrineCriteriaConverter;

class CriteriaToDoctrineCriteriaConverterTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testConvert(): void
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
        $criteria = new Criteria($operands, $orders, $offset, $limit);
        $converter = new CriteriaToDoctrineCriteriaConverter();

        $doctrineCriteria = $converter->convert($criteria);

        $this->assertInstanceOf(DoctrineCriteria::class, $doctrineCriteria);
        $comparisons = $this->getFlatComparisonList($doctrineCriteria->getWhereExpression());
        $this->assertCount(count($operands), $comparisons);
        foreach ($comparisons as $key => $comparison) {
            /** @var Operand $operand */
            $operand = $operands[$key];
            $operator = CriteriaToDoctrineCriteriaConverter::getComparisonOperator($operand->operator);
            $this->assertEquals($operand->property, $comparison->getField());
            $this->assertEquals($operand->value, $comparison->getValue()->getValue());
            $this->assertEquals($operator, $comparison->getOperator());
        }
        $orderings = $doctrineCriteria->getOrderings();
        $this->assertCount(count($orders), $orderings);
        foreach ($orders as $order) {
            $this->assertArrayHasKey($order->property, $orderings);
            $this->assertEquals($order->isAsc, 'ASC' === $orderings[$order->property]);
        }
        $this->assertEquals($offset, $doctrineCriteria->getFirstResult());
        $this->assertEquals($limit, $doctrineCriteria->getMaxResults());
    }

    /**
     * @return Comparison[]
     */
    private function getFlatComparisonList(Expression $expression): array
    {
        if (!($expression instanceof CompositeExpression)) {
            return [$expression];
        }

        $result = [];
        foreach ($expression->getExpressionList() as $expr) {
            $result = array_merge($result, $this->getFlatComparisonList($expr));
        }

        return $result;
    }
}
