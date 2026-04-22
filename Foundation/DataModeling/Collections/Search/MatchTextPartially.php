<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Search;

/**
 * Matches values by partial text match.
 */
final readonly class MatchTextPartially
{
    public function __construct(
        private array $items = [],
    ) {}

    public function __invoke(string $query, ?string $key = null) : array
    {
        return $this->match(query: $query, key: $key);
    }

    public function match(string $query, ?string $key = null) : array
    {
        $filtered = array_filter(
            array   : $this->items,
            callback: function (mixed $item) use ($query, $key) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string(value: $target)) {
                    return false;
                }

                return stripos(haystack: $target, needle: $query) !== false;
            }
        );

        return array_values(array: $filtered);
    }

    public function getItems() : array
    {
        return $this->items;
    }
}