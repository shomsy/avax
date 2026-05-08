<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Search;

/**
 * Matches values by fuzzy similarity using PHP's similar_text.
 */
final readonly class MatchTextFuzzily
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param int<0, 100> $threshold Similarity percentage required.
     * @param string|null $key       Dot-path or array key to match against; null for scalar items.
     *
     * @return array<mixed>
     */
    public function __invoke(string $query, int $threshold = 70, string|null $key = null) : array
    {
        return $this->match(query: $query, threshold: $threshold, key: $key);
    }

    /**
     * @param int<0, 100> $threshold Similarity percentage required.
     * @param string|null $key       Dot-path or array key to match against; null for scalar items.
     *
     * @return array<mixed>
     */
    public function match(string $query, int $threshold = 70, string|null $key = null) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->items,
                                       callback: static function (mixed $item) use ($query, $threshold, $key) : bool {
                                           $target = is_array(value: $item) && $key !== null
                                               ? ($item[$key] ?? '')
                                               : $item;

                                           if (! is_string(value: $target)) {
                                               return false;
                                           }

                                           similar_text(string1: strtolower(string: $query), string2: strtolower(string: $target), percent: $percent);

                                           return $percent >= $threshold;
                                       },
                                   ));
    }

    /**
     * @return array<mixed>
     */
    public function getItems() : array
    {
        return $this->items;
    }
}
