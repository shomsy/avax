<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Hashing;

use JsonException;
use Stringable;

/**
 * StableHash — canonical stable hash implementation for scalar and composite values.
 *
 * Produces deterministic hashes that survive process restarts.
 * Type prefixes prevent collisions across types (e.g., int 1 vs string "1").
 */
final readonly class StableHash implements HashFunction
{
    /**
     * @throws JsonException
     */
    public function hash(mixed $value) : string
    {
        return match (true) {
            $value === null              => 'null',
            is_bool($value)              => 'bool:' . ($value ? '1' : '0'),
            is_int($value)               => 'int:' . $value,
            is_float($value)             => 'float:' . sprintf('%.14F', $value),
            is_string($value)            => 'string:' . $value,
            $value instanceof Stringable => 'stringable:' . $value,
            is_array($value)             => 'array:' . json_encode(value: $value, flags: JSON_THROW_ON_ERROR),
            is_object($value)            => 'object:' . serialize(value: $value),
            default                      => 'value:' . serialize(value: $value),
        };
    }
}
