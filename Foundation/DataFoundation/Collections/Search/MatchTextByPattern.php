<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Search;

use InvalidArgumentException;

/**
 * Matches values by regex pattern.
 */
final readonly class MatchTextByPattern
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $pattern, string|null $key = null) : array
    {
        return $this->match(pattern: $pattern, key: $key);
    }

    public function match(string $pattern, string|null $key = null) : array
    {
        if (@preg_match(pattern: $pattern, subject: '') === false) {
            throw new InvalidArgumentException(message: 'Invalid regular expression pattern.');
        }

        $filtered = array_filter(
            array   : $this->items,
            callback: function (mixed $item) use ($pattern, $key) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string(value: $target)) {
                    return false;
                }

                return preg_match(pattern: $pattern, subject: $target) === 1;
            }
        );

        return array_values(array: $filtered);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}