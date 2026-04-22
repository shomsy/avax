<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Search;

use Avax\DataModeling\Collections\Internal\SearchThreshold;
use FuzzyWuzzy\Fuzz;

/**
 * Matches values by fuzzy similarity.
 */
final readonly class MatchTextFuzzily
{
    private ?Fuzz $fuzz;

    public function __construct(
        private array $items = [],
    )
    {
        $this->fuzz = null;
    }

    public function __invoke(string $query, int $threshold = 70, ?string $key = null) : array
    {
        return $this->match(query: $query, threshold: $threshold, key: $key);
    }

    public function match(string $query, int $threshold = 70, ?string $key = null) : array
    {
        new SearchThreshold(value: $threshold);

        $filtered = array_filter(
            array   : $this->items,
            callback: function (mixed $item) use ($query, $threshold, $key) : bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;

                if (! is_string(value: $target)) {
                    return false;
                }

                return $this->getFuzz()->ratio(
                        strtolower(string: $query),
                        strtolower(string: $target)
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