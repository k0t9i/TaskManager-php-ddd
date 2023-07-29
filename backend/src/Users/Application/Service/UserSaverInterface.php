<?php

declare(strict_types=1);

namespace TaskManager\Users\Application\Service;

use TaskManager\Users\Domain\Entity\User;

interface UserSaverInterface
{
    public function save(User $user, int $expectedVersion): int;
}
