<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use RuntimeException;

final class JsonContentDecoder implements ContentDecoderInterface
{
    public function decode(string $content): array
    {
        if ($content === '') {
            return [];
        }

        $result = json_decode($content, true);

        if ($result === null) {
            throw new RuntimeException(sprintf('Syntax error in json request content "%s"', $content));
        }

        return $result;
    }
}
