<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

final class JsonContentDecoder implements ContentDecoderInterface
{
    public function decode(string $content): array
    {
        if ('' === $content) {
            return [];
        }

        $result = json_decode($content, true);

        if (null === $result) {
            throw new \RuntimeException(sprintf('Syntax error in json request content "%s"', $content));
        }

        return $result;
    }
}
