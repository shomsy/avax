<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\IR\Nodes;

enum ComparisonOperator: string
{
    case EQUAL              = '=';
    case NOT_EQUAL          = '!=';
    case LESS_THAN          = '<';
    case LESS_THAN_OR_EQUAL = '<=';
    case GREATER_THAN       = '>';
    case GREATER_THAN_OR_EQUAL = '>=';
    case LIKE               = 'LIKE';
    case NOT_LIKE           = 'NOT LIKE';
    case ILIKE              = 'ILIKE';
    case IN                 = 'IN';
    case NOT_IN             = 'NOT IN';
    case BETWEEN            = 'BETWEEN';
    case IS_NULL            = 'IS NULL';
    case IS_NOT_NULL        = 'IS NOT NULL';
}
