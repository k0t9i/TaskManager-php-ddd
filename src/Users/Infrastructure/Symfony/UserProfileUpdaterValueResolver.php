<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Symfony;

use TaskManager\Shared\Infrastructure\Symfony\ValueResolver;
use TaskManager\Users\Infrastructure\Symfony\DTO\UserProfileUpdateDTO;

final class UserProfileUpdaterValueResolver extends ValueResolver
{
    protected function supportClass(): string
    {
        return UserProfileUpdateDTO::class;
    }

    protected function doResolve(array $attributes): iterable
    {
        yield new UserProfileUpdateDTO(
            $attributes['firstname'] ?? '',
            $attributes['lastname'] ?? '',
            $attributes['password'] ?? '',
            $attributes['repeatPassword'] ?? ''
        );
    }
}
