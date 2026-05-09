<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Foundation\Hashing;

/**
 * StringHash — hash function optimized for string values.
 *
 * Uses PHP's built-in hashing algorithm for string-only input.
 */
final readonly class StringHash implements HashFunction
{
    public function __construct(
        private string $algorithm = 'xxh128',
    ) {}

    public function hash(mixed $value) : string
    {
        $string = match (true) {
            is_string($value)                => $value,
            is_int($value), is_float($value) => (string) $value,
            default                          => serialize(value: $value),
        };

        return hash(algo: $this->algorithm, data: $string, binary: false);
    }
}
