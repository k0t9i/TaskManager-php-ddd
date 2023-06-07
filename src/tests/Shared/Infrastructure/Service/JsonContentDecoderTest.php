<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use TaskManager\Shared\Infrastructure\Service\JsonContentDecoder;

class JsonContentDecoderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testDecodeEmpty()
    {
        $decoder = new JsonContentDecoder();

        $this->assertEquals([], $decoder->decode(""));
    }

    public function testDecodeInvalidJson()
    {
        $json = $this->faker->regexify($this->faker->regexify('.{255}'));
        $decoder = new JsonContentDecoder();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(sprintf('Syntax error in json request content "%s"', $json));

        $decoder->decode($json);
    }

    public function testDecodeValidJson()
    {
        $source = [
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}')
        ];
        $decoder = new JsonContentDecoder();

        $this->assertEquals($source, $decoder->decode(json_encode($source)));
    }
}
