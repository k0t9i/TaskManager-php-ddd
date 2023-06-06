<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Symfony;

use TaskManager\Shared\Infrastructure\Symfony\ValueResolver;
use TaskManager\Users\Infrastructure\Symfony\DTO\UserLoginDTO;

final class UserLoginValueResolver extends ValueResolver
{
    protected function supportClass(): string
    {
        return UserLoginDTO::class;
    }

    protected function doResolve(array $attributes): iterable
    {
        yield new UserLoginDTO(
            $attributes['email'] ?? '',
            $attributes['password'] ?? ''
        );
    }
}
