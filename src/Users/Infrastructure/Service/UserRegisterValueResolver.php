<?php

declare(strict_types=1);

namespace TaskManager\Users\Infrastructure\Service;

use TaskManager\Shared\Infrastructure\Service\ValueResolver;
use TaskManager\Users\Infrastructure\Service\DTO\UserRegisterDTO;

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
