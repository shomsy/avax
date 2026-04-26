<?php

declare(strict_types=1);

namespace components\DataLayer\AccelerateDataReads;

enum DataCacheType: string
{
    case IN_MEMORY   = 'in_memory';
    case DISTRIBUTED = 'distributed';
    case HYBRID      = 'hybrid';
}

enum CacheTier: string
{
    case L1 = 'l1';
    case L2 = 'l2';
}

final readonly class ReadCacheKeyBuilder
{
    public function __construct(
        private string $tenantId,
        private string $table,
        private bool   $includeVersion
    ) {}

    public function build(array $query) : string
    {
        $parts = [
            't'   => $this->tenantId,
            'tbl' => $this->table,
            'q'   => $query['fingerprint'] ?? '',
            'p'   => $query['params'] ?? [],
        ];

        if ($this->includeVersion) {
            $parts['v'] = $query['version'] ?? 0;
        }

        return hash('xxh64', serialize($parts));
    }

    public function buildPrefix(string|null $tenantId = null, string|null $table = null) : string
    {
        return sprintf(
            'cache:%s:%s',
            $tenantId ?? $this->tenantId,
            $table ?? $this->table
        );
    }
}

final readonly class UseReadCache
{
    public function __construct(
        private ReadCachePolicy $policy
    ) {}

    public function describeResponsibility() : string
    {
        return 'determines whether to use read cache based on query patterns and cache policy.';
    }

    public function willCache() : bool
    {
        return $this->policy->type->value !== 'query';
    }

    public function plan(array $queryContext) : UseReadCacheResult
    {
        $queryType    = $queryContext['query_type'] ?? 'select';
        $isIdempotent = $queryContext['is_idempotent'] ?? true;
        $frequency    = $queryContext['frequency'] ?? 0;

        $willCache = $queryType === 'select' && $isIdempotent && $frequency > 1;

        return new UseReadCacheResult(
            willCache       : $willCache,
            cacheKey        : $willCache ? $this->generateCacheKey(queryContext: $queryContext) : null,
            ttlSeconds      : $willCache ? $this->policy->ttlSeconds : 0,
            estimatedHitRate: $this->estimateHitRate(queryContext: $queryContext)
        );
    }

    private function generateCacheKey(array $queryContext) : string
    {
        $fingerprint = $queryContext['query_fingerprint'] ?? '';
        $tenantId    = $queryContext['tenant_id'] ?? '';

        return hash('sha256', "{$tenantId}:{$fingerprint}");
    }

    private function estimateHitRate(array $queryContext) : float
    {
        $frequency = $queryContext['frequency'] ?? 0;

        if ($frequency > 10) {
            return 0.9;
        }
        if ($frequency > 5) {
            return 0.7;
        }
        if ($frequency > 1) {
            return 0.5;
        }

        return 0.1;
    }

    public function toMetadata() : array
    {
        return ['policy' => $this->policy->toMetadata()];
    }
}

final readonly class UseReadCacheResult
{
    public function __construct(
        public bool        $willCache,
        public string|null $cacheKey,
        public int         $ttlSeconds,
        public float       $estimatedHitRate
    ) {}
}