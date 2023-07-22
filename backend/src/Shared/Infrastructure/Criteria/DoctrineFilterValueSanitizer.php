<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Criteria;

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

final class DoctrineFilterValueSanitizer implements DoctrineFilterValueSanitizerInterface
{
    public function sanitize(Type $type, mixed $value): mixed
    {
        switch (true) {
            case $type instanceof SmallIntType:
            case $type instanceof IntegerType:
            case $type instanceof BigIntType:
                return (int) $value;
            case $type instanceof DateTimeType:
            case $type instanceof DateTimeTzType:
            case $type instanceof TimeType:
            case $type instanceof DateType:
                return false !== strtotime((string) $value) ? $value : null;
            case $type instanceof BooleanType:
                return (bool) $value;
            case $type instanceof FloatType:
            case $type instanceof DecimalType:
                return (float) $value;
            case $type instanceof DateIntervalType:
            case $type instanceof BlobType:
            case $type instanceof BinaryType:
            case $type instanceof StringType:
            case $type instanceof TextType:
            case $type instanceof SimpleArrayType:
            case $type instanceof JsonType:
            case $type instanceof ObjectType:
            case $type instanceof ArrayType:
                return $value;
        }

        throw new \LogicException(sprintf('Unknown db type "%s"', get_class($type)));
    }
}
