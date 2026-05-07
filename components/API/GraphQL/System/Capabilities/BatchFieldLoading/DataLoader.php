<?php

declare(strict_types=1);

namespace Avax\Components\API\GraphQL\System\Capabilities\BatchFieldLoading;

use Closure;

final class DataLoader
{
    /**
     * @var list<int|string>
     */
    private array $queuedKeys = [];

    private BatchLoadFields $batchLoadFields;

    /**
     * @param Closure(list<int|string>): array<int|string, mixed> $loadFields
     */
    public function __construct(Closure $loadFields, int $maxBatchSize = 100)
    {
        $this->batchLoadFields = new BatchLoadFields(
            loadFields  : $loadFields,
            maxBatchSize: $maxBatchSize,
        );
    }

    public function queue(int|string $key) : void
    {
        if (! in_array($key, $this->queuedKeys, true)) {
            $this->queuedKeys[] = $key;
        }
    }

    /**
     * @return list<int|string>
     */
    public function queuedKeys() : array
    {
        return $this->queuedKeys;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function dispatch() : array
    {
        $loaded           = $this->batchLoadFields->load(keys: $this->queuedKeys);
        $this->queuedKeys = [];

        return $loaded;
    }

    /**
     * @param list<int|string> $keys
     *
     * @return array<int|string, mixed>
     */
    public function loadMany(array $keys) : array
    {
        return $this->batchLoadFields->load(keys: $keys);
    }
}
