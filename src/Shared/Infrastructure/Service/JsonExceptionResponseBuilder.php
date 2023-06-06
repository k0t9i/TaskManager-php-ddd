<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

final class JsonExceptionResponseBuilder implements ExceptionResponseBuilderInterface
{
    public function build(string $message, int $code, array $trace): Response
    {
        $data = [
            'code' => $code,
            'message' => $message,
        ];

        if ($trace) {
            $data['trace'] = $trace;
        }

        return new JsonResponse($data, $code);
    }
}
