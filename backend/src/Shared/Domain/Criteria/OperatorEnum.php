<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

enum OperatorEnum: string
{
    case Equal = 'eq';
    case NotEqual = 'neq';
    case Greater = 'gt';
    case GreaterOrEqual = 'gte';
    case Less = 'lt';
    case LessOrEqual = 'lte';
    case In = 'in';
    case NotIn = 'nin';
    case Like = 'like';
}
