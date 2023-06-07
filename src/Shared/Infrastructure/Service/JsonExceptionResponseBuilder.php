<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use TaskManager\Shared\Infrastructure\Service\DTO\ExceptionDTO;

final class JsonExceptionResponseBuilder implements ExceptionResponseBuilderInterface
{
    public function build(ExceptionDTO $dto, bool $verbose = false): Response
    {
        $data = [
            'code' => $dto->httpCode,
            'message' => $dto->message,
        ];

        if ($verbose) {
            $data['file'] = $dto->file;
            $data['line'] = $dto->line;
            $data['trace'] = $dto->trace;
        }

        return new JsonResponse($data, $dto->httpCode);
    }
}
