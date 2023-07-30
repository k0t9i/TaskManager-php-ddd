<?php

declare(strict_types=1);

namespace TaskManager\Projects\Infrastructure\Service;

use TaskManager\Projects\Infrastructure\Service\DTO\ProjectInformationDTO;
use TaskManager\Shared\Infrastructure\Service\ValueResolver;

final class ProjectInformationValueResolver extends ValueResolver
{
    protected function supportClass(): string
    {
        return ProjectInformationDTO::class;
    }

    protected function doResolve(array $attributes): iterable
    {
        yield new ProjectInformationDTO(
            $attributes['name'] ?? '',
            $attributes['description'] ?? '',
            $attributes['finishDate'] ?? '',
            (string) $attributes['version']
        );
    }
}
