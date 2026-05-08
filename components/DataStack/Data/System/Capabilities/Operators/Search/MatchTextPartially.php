<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Operators\Search;

/**
 * Matches values by partial string containment.
 */
final readonly class MatchTextPartially
{
    /**
     * @param array<mixed> $items
     */
    public function __construct(
        private array $items = [],
    ) {}

    /**
     * @param string|null $key Dot-path or array key to match against; null for scalar items.
     *
     * @return array<mixed>
     */
    public function __invoke(string $query, string|null $key = null, bool $caseSensitive = false) : array
    {
        return $this->match(query: $query, key: $key, caseSensitive: $caseSensitive);
    }

    /**
     * @param string|null $key Dot-path or array key to match against; null for scalar items.
     *
     * @return array<mixed>
     */
    public function match(string $query, string|null $key = null, bool $caseSensitive = false) : array
    {
        return array_values(array: array_filter(
                                       array   : $this->items,
                                       callback: static function (mixed $item) use ($query, $key, $caseSensitive) : bool {
                                           $target = is_array(value: $item) && $key !== null
                                               ? ($item[$key] ?? '')
                                               : $item;

                                           if (! is_string(value: $target)) {
                                               return false;
                                           }

                                           return $caseSensitive
                                               ? str_contains(haystack: $target, needle: $query)
                                               : str_contains(haystack: strtolower(string: $target), needle: strtolower(string: $query));
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
