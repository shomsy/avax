<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Enums;

/**
 * Domain-fluent enumeration of supported SQL comparison operators.
 *
 * -- intent: provide a type-safe way to reference logical comparison strings.
 */
enum Operator: string
{
    case EQUAL = '=';
    case NOT_EQUAL = '!=';
    case GREATER_THAN = '>';
    case LESS_THAN = '<';
    case GREATER_EQUAL = '>=';
    case LESS_EQUAL = '<=';
    case LIKE = 'LIKE';
    case NOT_LIKE = 'NOT LIKE';
    case IN = 'IN';
    case NOT_IN = 'NOT IN';
}
