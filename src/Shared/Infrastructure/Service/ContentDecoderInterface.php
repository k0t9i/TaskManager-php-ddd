<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

interface ContentDecoderInterface
{
    public function decode(string $content): array;
}
