<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidInterface;
use TaskManager\Shared\Infrastructure\Service\RamseyUuid4Generator;

class RamseyUuid4GeneratorTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testGenerate()
    {
        $result = $this->faker->regexify('.{255}');
        $generator = $this->getMockBuilder(UuidInterface::class)
            ->getMock();
        $generator->expects(self::once())
            ->method('toString')
            ->willReturn($result);

        $object = new RamseyUuid4Generator($generator);

        $this->assertEquals($object->generate(), $result);
    }
}
