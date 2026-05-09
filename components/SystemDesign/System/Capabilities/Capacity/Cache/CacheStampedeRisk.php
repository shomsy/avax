<?php

declare(strict_types=1);

namespace Avax\Components\SystemDesign\System\Capabilities\Capacity\Cache;

/**
 * Cache stampede risk model.
 *
 * @experimental V3 labs
 */
final readonly class CacheStampedeRisk
{
    public function __construct(
        public bool $protectionRequired,
    ) {}

    /**
     * Estimate max simultaneous backend requests on cache expiry without protection.
     */
    public function worstCaseConcurrentRequests(int $cacheMissRps, int $ttlSeconds) : int
    {
        if (! $this->protectionRequired) {
            return 0;
        }

        return $cacheMissRps * $ttlSeconds;
    }

    /**
     * @return array{valid: bool, errors: list<string>}
     */
    public function validate() : array
    {
        return ['valid' => true, 'errors' => []];
    }
}
