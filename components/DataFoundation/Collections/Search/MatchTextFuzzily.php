<?php

declare(strict_types=1);

namespace components\DataFoundation\Collections\Search;

use components\DataFoundation\Internal\Support\Threshold;
use FuzzyWuzzy\Fuzz;

/**
 * Matches values by fuzzy similarity.
 */
final readonly class MatchTextFuzzily
{
    private Fuzz|null $fuzz;

    public function __construct(
        private array $items = [],
    )
    {
        $this->fuzz = null;
    }

    public function __invoke(string $query, int|null $threshold = null, string|null $key = null) : array
    {
        $threshold ??= 70;

        return $this->match(query: $query, threshold: $threshold, key: $key);
    }

    public function match(string $query, int|null $threshold = null, string|null $key = null) : array
    {
        $threshold ??= 70;
        new Threshold(value: $threshold);

        $filtered = array_filter(
            array   : $this->items,
            callback: function (mixed $item) use ($query, $threshold, $key) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string(value: $target)) {
                    return false;
                }

                return $this->getFuzz()->ratio(
                        s1: strtolower(string: $query),
                        s2: strtolower(string: $target)
                    ) >= $threshold;
            }
        );

        return array_values(array: $filtered);
    }

    private function getFuzz() : Fuzz
    {
        return $this->fuzz ??= new Fuzz();
    }

    public function getItems() : array
    {
        return $this->items;
    }
}