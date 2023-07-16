<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

enum LogicalOperatorEnum
{
    case And;
    case Or;
}
