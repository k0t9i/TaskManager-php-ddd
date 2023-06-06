<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Symfony;

use TaskManager\Shared\Infrastructure\Symfony\ValueResolver;
use TaskManager\Users\Infrastructure\Symfony\DTO\UserRegisterDTO;

final class UserRegisterValueResolver extends ValueResolver
{
    protected function supportClass(): string
    {
        return UserRegisterDTO::class;
    }

    protected function doResolve(array $attributes): iterable
    {
        yield new UserRegisterDTO(
            $attributes['email'] ?? '',
            $attributes['firstname'] ?? '',
            $attributes['lastname'] ?? '',
            $attributes['password'] ?? '',
            $attributes['repeatPassword'] ?? ''
        );
    }
}
