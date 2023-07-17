<?php

declare(strict_types=1);

namespace TaskManager\Shared\Infrastructure\Service;

use TaskManager\Shared\Application\DTO\QueryCriteriaDTO;
use TaskManager\Shared\Infrastructure\Service\DTO\RequestCriteriaDTO;

interface QueryCriteriaFromRequestConverterInterface
{
    public function convert(RequestCriteriaDTO $dto): QueryCriteriaDTO;
}
