<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Application\Criteria\CriteriaFieldValidator;
use TaskManager\Shared\Domain\Criteria\Criteria;
use TaskManager\Shared\Domain\Criteria\Operand;
use TaskManager\Shared\Domain\Criteria\OperatorEnum;
use TaskManager\Shared\Domain\Criteria\Order;
use TaskManager\Shared\Domain\Exception\CriteriaFilterNotExistException;
use TaskManager\Shared\Domain\Exception\CriteriaOrderNotExistException;

class TestClass
{
    public string $abc;
}

class CriteriaFieldValidatorTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testValidate(): void
    {
        $field = 'abc';
        $criteria = new Criteria(
            [new Operand($field, OperatorEnum::Equal, 1)],
            [new Order($field)]
        );
        $validator = new CriteriaFieldValidator();

        $this->expectNotToPerformAssertions();

        $validator->validate($criteria, TestClass::class);
    }

    public function testValidateNonExistingFieldFilter(): void
    {
        $field = $this->faker->regexify('.{255}');
        $criteria = new Criteria([
            new Operand($field, OperatorEnum::Equal, 1),
        ]);
        $validator = new CriteriaFieldValidator();

        $this->expectException(CriteriaFilterNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Filter field "%s" doesn\'t exist',
            $field
        ));

        $validator->validate($criteria, TestClass::class);
    }

    public function testValidateNonExistingFieldOrder(): void
    {
        $field = $this->faker->regexify('.{255}');
        $criteria = new Criteria(
            [],
            [new Order($field)]
        );
        $validator = new CriteriaFieldValidator();

        $this->expectException(CriteriaOrderNotExistException::class);
        $this->expectExceptionMessage(sprintf(
            'Order field "%s" doesn\'t exist',
            $field
        ));

        $validator->validate($criteria, TestClass::class);
    }
}
