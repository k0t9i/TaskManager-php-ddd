<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\UuidFactoryInterface;
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

    public function testGenerate(): void
    {
        set_error_handler(
            static function ($errno, $errstr) {
                restore_error_handler();
                throw new \Exception($errstr, $errno);
            },
            E_DEPRECATED
        );
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(
            'implements the Serializable interface, which is deprecated. Implement'.
            ' __serialize() and __unserialize() instead (or in addition, if support for old PHP versions is necessary)'
        );

        $result = $this->faker->regexify('.{255}');
        $generator = $this->getMockBuilder(UuidInterface::class)
            ->getMock();
        $generator->expects(self::once())
            ->method('toString')
            ->willReturn($result);
        $factory = $this->getMockBuilder(UuidFactoryInterface::class)
            ->getMock();
        $factory->expects(self::once())
            ->method('uuid4')
            ->willReturn($generator);

        $object = new RamseyUuid4Generator($factory);

        $this->assertEquals($object->generate(), $result);
    }
}
