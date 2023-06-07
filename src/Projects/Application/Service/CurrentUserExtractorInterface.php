<?php

declare(strict_types=1);

namespace TaskManager\Projects\Application\Service;

use TaskManager\Projects\Domain\ValueObject\ProjectUser;

interface CurrentUserExtractorInterface
{
    public function extract(): ProjectUser;
}
