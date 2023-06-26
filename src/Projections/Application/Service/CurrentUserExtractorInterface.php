<?php

declare(strict_types=1);

namespace TaskManager\Projections\Application\Service;

use TaskManager\Projections\Domain\Entity\UserProjection;

interface CurrentUserExtractorInterface
{
    public function extract(): UserProjection;
}
