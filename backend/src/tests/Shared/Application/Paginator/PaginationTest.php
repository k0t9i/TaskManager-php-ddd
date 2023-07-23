<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Application\Paginator;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Paginator\Pagination;

class PaginationTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testGetItems(): void
    {
        $items = [
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
            $this->faker->regexify('.{255}'),
        ];
        $pagination = new Pagination($items, 20, 0, 10);

        $this->assertEquals($items, $pagination->getItems());
    }

    public function testGetTotalPageCount(): void
    {
        $totalCount = $this->faker->numberBetween(1000);
        $limit = $this->faker->numberBetween(1, $totalCount - 1);
        $pagination = new Pagination([], $totalCount, 0, $limit);

        $this->assertEquals(ceil($totalCount / $limit), $pagination->getTotalPageCount());
    }

    public function testGetTotalPageCountIfEmpty(): void
    {
        $pagination = new Pagination([], 0, 0, $this->faker->numberBetween());

        $this->assertEquals(0, $pagination->getTotalPageCount());
    }

    public function testGetTotalPageCountWithoutLimit(): void
    {
        $emptyPagination = new Pagination([], 0, 0, null);
        $pagination = new Pagination([], 100, 0, null);

        $this->assertEquals(0, $emptyPagination->getTotalPageCount());
        $this->assertEquals(1, $pagination->getTotalPageCount());
    }

    public function testGetNextPage(): void
    {
        $pagination = new Pagination([], 100, 26, 13);

        $this->assertEquals(4, $pagination->getNextPage());
    }

    public function testGetNextPageGreaterThanTotalPage(): void
    {
        $pagination = new Pagination([], 100, 91, 13);

        $this->assertEquals(null, $pagination->getNextPage());
    }

    public function testGetCurrentPage(): void
    {
        $totalCount = $this->faker->numberBetween(1000);
        $limit = $this->faker->numberBetween(1, $totalCount - 1);
        $offset = $this->faker->numberBetween(1, $totalCount - 1);
        $pagination = new Pagination([], $totalCount, $offset, $limit);

        $this->assertEquals(floor($offset / $limit) + 1, $pagination->getCurrentPage());
    }

    public function testGetCurrentPageWithoutLimit(): void
    {
        $pagination = new Pagination([], 100, 0, null);

        $this->assertEquals(1, $pagination->getCurrentPage());
    }

    public function testGetPreviousPage(): void
    {
        $pagination = new Pagination([], 100, 26, 13);

        $this->assertEquals(2, $pagination->getPreviousPage());
    }

    public function testGetPreviousPageLessThanZero(): void
    {
        $pagination = new Pagination([], 100, 0, 13);

        $this->assertEquals(null, $pagination->getPreviousPage());
    }
}
