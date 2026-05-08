<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collection\Internal;

use JsonException;
use Stringable;

/**
 * Shared scalar-friendly comparison and hashing rules.
 */
final readonly class Comparator
{
    /**
     * @throws JsonException
     */
    public static function compare(mixed $left, mixed $right): int
    {
        $leftHash = self::hash(value: $left);
        $rightHash = self::hash(value: $right);

        return $leftHash <=> $rightHash;
    }

    /**
     * @throws JsonException
     */
    public static function hash(mixed $value): string
    {
        return match (true) {
            $value === null => 'null',
            is_bool($value) => 'bool:'.($value ? '1' : '0'),
            is_int($value) => 'int:'.$value,
            is_float($value) => 'float:'.sprintf('%.14F', $value),
            is_string($value) => 'string:'.$value,
            $value instanceof Stringable => 'stringable:'.$value,
            is_array($value) => 'array:'.json_encode($value, JSON_THROW_ON_ERROR),
            is_object($value) => 'object:'.serialize($value),
            default => 'value:'.serialize($value),
        };
    }
}
