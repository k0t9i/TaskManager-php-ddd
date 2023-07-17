<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Application\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\DTO\QueryCriteriaDTO;
use TaskManager\Shared\Application\DTO\QueryCriteriaFilterDTO;
use TaskManager\Shared\Application\Service\CriteriaFromQueryBuilder;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\LogicalOperatorEnum;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\Order;
use TaskManager\Shared\Domain\Exception\CriteriaFilterOperatorNotExistException;

class CriteriaFromQueryBuilderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testBuild(): void
    {
        $defaultOrderProperty = $this->faker->regexify('.{255}');
        $criteria = new Criteria([], [
            new Order($defaultOrderProperty, true),
        ]);
        $dto = new QueryCriteriaDTO(
            [
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'eq', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'neq', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'gt', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'gte', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'lt', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'lte', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'in', [$this->faker->regexify('[a-zA-Z0-9]{255}')]
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'nin', [$this->faker->regexify('[a-zA-Z0-9]{255}')]
                ),
                new QueryCriteriaFilterDTO(
                    $this->faker->regexify('[a-zA-Z0-9]{255}'), 'like', $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
            ],
            [
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
                $this->faker->regexify('.{255}') => $this->faker->boolean(),
            ],
            $this->faker->numberBetween(),
            $this->faker->numberBetween()
        );
        $builder = new CriteriaFromQueryBuilder();

        $builder->build($criteria, $dto);

        $this->assertCount(count($dto->filters), $criteria->getExpression()->getOperands());
        $items = $criteria->getExpression()->getOperands();
        foreach ($dto->filters as $filter) {
            $item = array_shift($items);
            $logicalOperator = $item[0];
            /** @var Operand $operand */
            $operand = $item[1];

            $this->assertEquals(LogicalOperatorEnum::And, $logicalOperator);
            $this->assertEquals($filter->property, $operand->property);
            $this->assertEquals($filter->operator, $operand->operator->value);
            $this->assertEquals($filter->value, $operand->value);
        }
        $this->assertCount(count($dto->orders), $criteria->getOrders());
        foreach ($criteria->getOrders() as $order) {
            $this->assertArrayHasKey($order->property, $dto->orders);
            $this->assertEquals($dto->orders[$order->property], $order->isAsc);
        }
        $this->assertEquals($criteria->getOffset(), $dto->offset);
        $this->assertEquals($criteria->getLimit(), $dto->limit);
    }

    public function testBuildInvalidOperator(): void
    {
        $property = $this->faker->regexify('[a-zA-Z0-9]{255}');
        $operator = $this->faker->regexify('[a-zA-Z0-9]{255}');
        $dto = new QueryCriteriaDTO(
            [
                new QueryCriteriaFilterDTO(
                    $property, $operator, $this->faker->regexify('[a-zA-Z0-9]{255}')
                ),
            ],
            [],
            null,
            null
        );
        $criteria = new Criteria();
        $builder = new CriteriaFromQueryBuilder();

        $this->expectException(CriteriaFilterOperatorNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Filter operator "%s" for field "%s" doesn\'t exist',
            $operator,
            $property
        ));

        $builder->build($criteria, $dto);
    }

    public function testBuildWithEmptyOrders(): void
    {
        $defaultOrderProperty = $this->faker->regexify('.{255}');
        $criteria = new Criteria([], [
            new Order($defaultOrderProperty, true),
        ]);
        $builder = new CriteriaFromQueryBuilder();

        $builder->build($criteria, new QueryCriteriaDTO([], [], null, null));

        $this->assertCount(1, $criteria->getOrders());
        $this->assertEquals($defaultOrderProperty, $criteria->getOrders()[0]->property);
        $this->assertTrue($criteria->getOrders()[0]->isAsc);
    }
}
