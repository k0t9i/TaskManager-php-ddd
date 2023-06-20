<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Persistence\Doctrine;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;

final class CustomPostgreSQLPlatform extends PostgreSQLPlatform
{
    public function getDateTimeTypeDeclarationSQL(array $column): string
    {
        return 'TIMESTAMP(6) WITHOUT TIME ZONE';
    }

    public function getDateTimeTzTypeDeclarationSQL(array $column): string
    {
        return 'TIMESTAMP(6) WITH TIME ZONE';
    }

    public function getTimeTypeDeclarationSQL(array $column): string
    {
        return 'TIME(6) WITHOUT TIME ZONE';
    }

    public function getDateTimeFormatString(): string
    {
        return 'Y-m-d H:i:s.u';
    }

    public function getDateTimeTzFormatString(): string
    {
        return 'Y-m-d H:i:s.uO';
    }

    public function getTimeFormatString(): string
    {
        return 'H:i:s.u';
    }
}
