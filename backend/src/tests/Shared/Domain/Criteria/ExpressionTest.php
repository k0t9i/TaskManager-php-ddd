<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Domain\Criteria;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Domain\Criteria\Expression;
use TaskManager\Shared\Domain\Criteria\LogicalOperatorEnum;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;

class ExpressionTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testGetOperands(): void
    {
        $operands = [
            [
                LogicalOperatorEnum::And,
                new Operand(
                    $this->faker->regexify('.{255}'),
                    OperatorEnum::Greater,
                    $this->faker->regexify('.{255}')
                ),
            ],
            [
                LogicalOperatorEnum::And,
                new Operand(
                    $this->faker->regexify('.{255}'),
                    OperatorEnum::Less,
                    $this->faker->regexify('.{255}')
                ),
            ],
            [
                LogicalOperatorEnum::Or,
                new Operand(
                    $this->faker->regexify('.{255}'),
                    OperatorEnum::In,
                    [
                        $this->faker->regexify('.{255}'),
                    ]
                ),
            ],
            [
                LogicalOperatorEnum::Or,
                new Operand(
                    $this->faker->regexify('.{255}'),
                    OperatorEnum::Equal,
                    $this->faker->regexify('.{255}')
                ),
            ],
        ];
        $expression = new Expression();
        foreach ($operands as [$operator, $operand]) {
            if (LogicalOperatorEnum::And === $operator) {
                $expression->andOperand($operand);
            } else {
                $expression->orOperand($operand);
            }
        }

        $this->assertEquals($operands, $expression->getOperands());
    }
}
