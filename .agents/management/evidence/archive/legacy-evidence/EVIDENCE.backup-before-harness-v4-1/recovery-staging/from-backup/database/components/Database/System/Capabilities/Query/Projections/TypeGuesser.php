<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\Projections;

use DateTimeInterface;

final class TypeGuesser
{
    public function fromValue(mixed $value) : string
    {
        return match (true) {
            is_int(value: $value)               => 'int',
            is_float(value: $value)             => 'float',
            is_bool(value: $value)              => 'bool',
            $value instanceof DateTimeInterface => DateTimeInterface::class,
            is_array(value: $value)             => 'array',
            is_object(value: $value)            => $value::class,
            $value === null                     => 'null',
            default                             => 'string',
        };
    }

    public function fromColumnName(string $column) : string
    {
        $normalized = strtolower(string: $column);

        return match (true) {
            str_ends_with(haystack: $normalized, needle: '_id') || $normalized === 'id'                                     => 'int',
            str_starts_with(haystack: $normalized, needle: 'is_') || str_starts_with(haystack: $normalized, needle: 'has_') => 'bool',
            str_ends_with(haystack: $normalized, needle: '_at') || str_ends_with(haystack: $normalized, needle: '_date')    => DateTimeInterface::class,
            str_contains(haystack: $normalized, needle: 'count') || str_contains(haystack: $normalized, needle: 'total')    => 'int',
            str_contains(haystack: $normalized, needle: 'amount') || str_contains(haystack: $normalized, needle: 'price')   => 'float',
            default                                                                                                         => 'string',
        };
    }
}
