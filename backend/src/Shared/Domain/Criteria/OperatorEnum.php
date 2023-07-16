<?php

declare(strict_types=1);

namespace TaskManager\Shared\Domain\Criteria;

enum OperatorEnum: string
{
    case Equal = '=';
    case NotEqual = '<>';
    case Greater = '>';
    case GreaterOrEqual = '>=';
    case Less = '<';
    case LessOrEqual = '<=';
    case In = 'IN';
    case NotIn = 'NIN';
}
