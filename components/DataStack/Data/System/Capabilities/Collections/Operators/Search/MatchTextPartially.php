<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Operators\Search;

/**
 * Matches values by partial string match.
 */
final readonly class MatchTextPartially
{
    public function __construct(private array $items = []) {}

    public function __invoke(string $query, ?string $key = null, bool $caseSensitive = false): array
    {
        return array_values(array_filter(
            $this->items,
            static function ($item) use ($query, $key, $caseSensitive): bool {
                $target = $key !== null ? ($item[$key] ?? '') : $item;
                if (! is_string($target)) {
                    return false;
                }

                return $caseSensitive
                    ? str_contains($target, $query)
                    : str_contains(strtolower($target), strtolower($query));
            },
        ));
    }
}
