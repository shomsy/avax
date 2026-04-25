<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Collections\Search;

/**
 * Matches values by metaphone phonetic similarity.
 */
final readonly class MatchTextPhonetically
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $query, string|null $key = null) : array
    {
        return $this->match(query: $query, key: $key);
    }

    public function match(string $query, string|null $key = null) : array
    {
        $queryPhonetic = metaphone(string: strtolower(string: $query));

        $filtered = array_filter(
            array   : $this->items,
            callback: function (mixed $item) use ($queryPhonetic, $key) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string(value: $target)) {
                    return false;
                }

                return metaphone(string: strtolower(string: $target)) === $queryPhonetic;
            }
        );

        return array_values(array: $filtered);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}