<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Criteria;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Paginator\Pagination;
use TaskManager\Shared\Infrastructure\Criteria\QueryCriteriaFromRequestConverter;
use TaskManager\Shared\Infrastructure\Criteria\RequestCriteriaDTO;

class QueryCriteriaFromRequestConverterTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testConvert(): void
    {
        $filtersData = [
            [
                'key' => [$this->faker->regexify('[a-zA-Z0-9]{255}'), $this->faker->regexify('[a-zA-Z0-9]{255}')],
                'value' => $this->faker->regexify('[a-zA-Z0-9]{255}'),
            ],
            [
                'key' => [$this->faker->regexify('[a-zA-Z0-9]{255}'), $this->faker->regexify('[a-zA-Z0-9]{255}')],
                'value' => $this->faker->regexify('[a-zA-Z0-9]{255}'),
            ],
            [
                'key' => [$this->faker->regexify('[a-zA-Z0-9]{255}'), $this->faker->regexify('[a-zA-Z0-9]{255}')],
                'value' => $this->faker->regexify('[a-zA-Z0-9]{255}'),
            ],
            [
                'key' => [$this->faker->regexify('[a-zA-Z0-9]{255}'), $this->faker->regexify('[a-zA-Z0-9]{255}')],
                'value' => $this->faker->regexify('[a-zA-Z0-9]{255}'),
            ],
            [
                'key' => [$this->faker->regexify('[a-zA-Z0-9]{255}')],
                'value' => $this->faker->regexify('[a-zA-Z0-9]{255}'),
            ],
        ];
        $filters = [];
        foreach ($filtersData as $item) {
            $filters[implode(':', $item['key'])] = $item['value'];
        }
        $ordersData = [
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '-',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '+',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '+',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '-',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '-',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '+',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => '-',
            $this->faker->regexify('[a-zA-Z0-9]{255}') => null,
        ];
        $orders = [];
        foreach ($ordersData as $key => $value) {
            $orders[$key] = $key;
            if (null !== $value) {
                $orders[$key] = $value.$orders[$key];
            }
        }
        $page = $this->faker->numberBetween();
        $requestCriteria = new RequestCriteriaDTO($filters, array_values($orders), $page);
        $converter = new QueryCriteriaFromRequestConverter();

        $queryCriteria = $converter->convert($requestCriteria);

        $this->assertCount(count($filtersData), $queryCriteria->filters);
        foreach ($filtersData as $key => $item) {
            $filter = $queryCriteria->filters[$key];
            if (1 === count($item['key'])) {
                $item['key'][1] = QueryCriteriaFromRequestConverter::DEFAULT_OPERATOR;
            }
            $this->assertEquals($item['key'][0], $filter->property);
            $this->assertEquals(mb_strtolower($item['key'][1]), $filter->operator);
            $this->assertEquals($item['value'], $filter->value);
        }
        $this->assertCount(count($ordersData), $queryCriteria->orders);
        foreach ($ordersData as $key => $value) {
            $this->assertArrayHasKey($key, $queryCriteria->orders);
            $this->assertEquals($queryCriteria->orders[$key], '-' === $value);
        }
        $this->assertEquals(($page - 1) * Pagination::PAGE_SIZE, $queryCriteria->offset);
        $this->assertEquals(Pagination::PAGE_SIZE, $queryCriteria->limit);
    }
}
