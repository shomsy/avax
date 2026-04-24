<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Search;

use InvalidArgumentException;

/**
 * Matches values by Levenshtein distance.
 */
final readonly class MatchTextByLevenshtein
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $query, int|null $maxDistance = null, ?string $key = null) : array
    {
        $maxDistance ??= 2;

        return $this->match(query: $query, maxDistance: $maxDistance, key: $key);
    }

    public function match(string $query, int|null $maxDistance = null, ?string $key = null) : array
    {
        $maxDistance ??= 2;
        if ($maxDistance < 0) {
            throw new InvalidArgumentException(message: 'Maximum distance cannot be negative.');
        }

        $matched = [];

        foreach ($this->items as $item) {
            $target = $key !== null ? ($item[$key] ?? '') : $item;

            if (! is_string(value: $target)) {
                continue;
            }

            $distance = levenshtein(string1: strtolower(string: $query), string2: strtolower(string: $target));

            if ($distance <= $maxDistance) {
                $matched[$distance][] = $item;
            }
        }

        ksort(array: $matched);

        $sorted = [];

        foreach ($matched as $items) {
            foreach ($items as $item) {
                $sorted[] = $item;
            }
        }

        return $sorted;
    }

    public function getItems() : array
    {
        return $this->items;
    }
}