<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Search;

/**
 * Matches values by fuzzy similarity.
 */
final readonly class MatchTextFuzzily
{
    public function __construct(private array $items = []) {}

    public function __invoke(string $query, int $threshold = 70, string|null $key = null) : array
    {
        return array_values(array_filter(
                                $this->items,
                                function ($item) use ($query, $threshold, $key) {
                                    $target = $key !== null ? ($item[$key] ?? '') : $item;
                                    if (! is_string($target)) return false;

                                    // Simple similarity implementation if FuzzyWuzzy is missing
                                    similar_text(strtolower($query), strtolower($target), $percent);

                                    return $percent >= $threshold;
                                }
                            ));
    }
}
