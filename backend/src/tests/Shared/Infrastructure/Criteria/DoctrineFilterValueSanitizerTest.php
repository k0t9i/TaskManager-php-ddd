<?php

declare(strict_types=1);

namespace TaskManager\Tests\Shared\Infrastructure\Criteria;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ArrayType;
use Doctrine\DBAL\Types\BigIntType;
use Doctrine\DBAL\Types\BinaryType;
use Doctrine\DBAL\Types\BlobType;
use Doctrine\DBAL\Types\BooleanType;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\DBAL\Types\DateTimeType;
use Doctrine\DBAL\Types\DateTimeTzType;
use Doctrine\DBAL\Types\DateType;
use Doctrine\DBAL\Types\DecimalType;
use Doctrine\DBAL\Types\FloatType;
use Doctrine\DBAL\Types\IntegerType;
use Doctrine\DBAL\Types\JsonType;
use Doctrine\DBAL\Types\ObjectType;
use Doctrine\DBAL\Types\SimpleArrayType;
use Doctrine\DBAL\Types\SmallIntType;
use Doctrine\DBAL\Types\StringType;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Types\TimeType;
use Doctrine\DBAL\Types\Type;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\TestCase;
use TaskManager\Shared\Infrastructure\Criteria\DoctrineFilterValueSanitizer;

class UnknownType extends Type
{
    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return '';
    }

    public function getName(): string
    {
        return '';
    }
}

class DoctrineFilterValueSanitizerTest extends TestCase
{
    private Generator $faker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
    }

    public function testSanitizeIntType(): void
    {
        $valid = $this->faker->numberBetween();
        $random = $this->faker->regexify('.{255}');
        $types = [
            new IntegerType(),
            new SmallIntType(),
            new BigIntType(),
        ];
        $sanitizer = new DoctrineFilterValueSanitizer();

        foreach ($types as $type) {
            $this->assertEquals($valid, $sanitizer->sanitize($type, $valid));
        }
        foreach ($types as $type) {
            $this->assertEquals((int) $random, $sanitizer->sanitize($type, $random));
        }
    }

    public function testSanitizeDateType(): void
    {
        $valid = $this->faker->date();
        $random = $this->faker->regexify('.{255}');
        $types = [
            new DateTimeType(),
            new DateTimeTzType(),
            new TimeType(),
            new DateType(),
        ];
        $sanitizer = new DoctrineFilterValueSanitizer();

        foreach ($types as $type) {
            $this->assertEquals($valid, $sanitizer->sanitize($type, $valid));
        }
        foreach ($types as $type) {
            $this->assertEquals(null, $sanitizer->sanitize($type, $random));
        }
    }

    public function testSanitizeBooleanType(): void
    {
        $valid = $this->faker->boolean();
        $random = $this->faker->regexify('.{255}');
        $types = [
            new BooleanType(),
        ];
        $sanitizer = new DoctrineFilterValueSanitizer();

        foreach ($types as $type) {
            $this->assertEquals($valid, $sanitizer->sanitize($type, $valid));
        }
        foreach ($types as $type) {
            $this->assertEquals((bool) $random, $sanitizer->sanitize($type, $random));
        }
    }

    public function testSanitizeFloatType(): void
    {
        $valid = $this->faker->randomFloat();
        $random = $this->faker->regexify('.{255}');
        $types = [
            new FloatType(),
            new DecimalType(),
        ];
        $sanitizer = new DoctrineFilterValueSanitizer();

        foreach ($types as $type) {
            $this->assertEquals($valid, $sanitizer->sanitize($type, $valid));
        }
        foreach ($types as $type) {
            $this->assertEquals((float) $random, $sanitizer->sanitize($type, $random));
        }
    }

    public function testSanitizeOtherType(): void
    {
        $random = $this->faker->regexify('.{255}');
        $types = [
            new DateIntervalType(),
            new BlobType(),
            new BinaryType(),
            new StringType(),
            new TextType(),
            new SimpleArrayType(),
            new JsonType(),
            new ObjectType(),
            new ArrayType(),
        ];
        $sanitizer = new DoctrineFilterValueSanitizer();

        foreach ($types as $type) {
            $this->assertEquals($random, $sanitizer->sanitize($type, $random));
        }
    }

    public function testSanitizeUnknownType(): void
    {
        $sanitizer = new DoctrineFilterValueSanitizer();
        $type = new UnknownType();

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage(sprintf('Unknown db type "%s"', get_class($type)));

        $sanitizer->sanitize($type, null);
    }
}
