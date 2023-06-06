<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use Symfony\Component\HttpFoundation\JsonResponse;
use TaskManager\Shared\Infrastructure\Service\JsonExceptionResponseBuilder;
use PHPUnit\Framework\TestCase;

class JsonExceptionResponseBuilderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testBuild()
    {
        $message = $this->faker->regexify('.{255}');
        $code = $this->faker->numberBetween(200, 599);
        $trace = [
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}')
        ];
        $data = [
            'code' => $code,
            'message' => $message,
            'trace' => $trace
        ];
        $response = new JsonResponse($data, $code);
        $builder = new JsonExceptionResponseBuilder();

        $result = $builder->build($message, $code, $trace);

        $this->assertEquals($response, $result);
    }

    public function testBuildEmptyTrace()
    {
        $message = $this->faker->regexify('.{255}');
        $code = $this->faker->numberBetween(200, 599);
        $data = [
            'code' => $code,
            'message' => $message
        ];
        $response = new JsonResponse($data, $code);
        $builder = new JsonExceptionResponseBuilder();

        $result = $builder->build($message, $code, []);

        $this->assertEquals($response, $result);
    }
}
