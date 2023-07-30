<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service;

use TaskManager\Shared\Infrastructure\Service\ValueResolver;
use TaskManager\Users\Infrastructure\Service\DTO\UserProfileUpdateDTO;

final class UserProfileUpdateValueResolver extends ValueResolver
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
            $attributes['repeatPassword'] ?? '',
            $attributes['version'] ?? ''
        );
    }
}
