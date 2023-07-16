<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Service;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use TaskManager\Shared\Infrastructure\Service\DTO\ExceptionDTO;
use TaskManager\Shared\Infrastructure\Service\JsonExceptionResponseBuilder;

class JsonExceptionResponseBuilderTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testBuild(): void
    {
        $message = $this->faker->regexify('.{255}');
        $code = $this->faker->numberBetween(200, 599);
        $dto = new ExceptionDTO(
            $message,
            $code
        );
        $data = [
            'code' => $code,
            'message' => $message,
        ];
        $response = new JsonResponse($data, $code);
        $builder = new JsonExceptionResponseBuilder();

        $result = $builder->build($dto);

        $this->assertInstanceOf(JsonResponse::class, $result);
        $this->assertEquals($response->getContent(), $result->getContent());
        $this->assertEquals($response->getStatusCode(), $result->getStatusCode());
    }

    public function testBuildVerbose(): void
    {
        $message = $this->faker->regexify('.{255}');
        $code = $this->faker->numberBetween(200, 599);
        $file = $this->faker->regexify('.{255}');
        $line = $this->faker->numberBetween(0, 1000);
        $trace = [
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
            $this->faker->regexify('[a-z][A-Z]{255}') => $this->faker->regexify('.{255}'),
        ];
        $dto = new ExceptionDTO(
            $message,
            $code,
            $file,
            $line,
            $trace
        );
        $data = [
            'code' => $code,
            'message' => $message,
            'file' => $file,
            'line' => $line,
            'trace' => $trace,
        ];
        $response = new JsonResponse($data, $code);
        $builder = new JsonExceptionResponseBuilder();

        $result = $builder->build($dto, true);

        $this->assertEquals($response, $result);
    }
}
