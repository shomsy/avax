<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading;

use Closure;
use InvalidArgumentException;

final readonly class BatchLoadFields
{
    /**
     * @param Closure(list<int|string>): array<int|string, mixed> $loadFields
     */
    public function __construct(
        private Closure $loadFields,
        private int     $maxBatchSize,
    )
    {
        if ($this->maxBatchSize < 1) {
            throw new InvalidArgumentException('GraphQL max batch size must be at least 1.');
        }
    }

    /**
     * @param list<int|string> $keys
     *
     * @return array<int|string, mixed>
     */
    public function load(array $keys) : array
    {
        $loaded     = [];
        $uniqueKeys = array_values(array_unique($keys, SORT_REGULAR));

        foreach (array_chunk($uniqueKeys, max(1, $this->maxBatchSize)) as $chunk) {
            $results = ($this->loadFields)($chunk);

            foreach ($chunk as $key) {
                $loaded[$key] = $results[$key] ?? null;
            }
        }

        return $loaded;
    }
}
