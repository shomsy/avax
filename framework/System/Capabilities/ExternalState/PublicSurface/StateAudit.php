<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ExternalState\PublicSurface;

use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\MemoryStateAdapter;
use Avax\Framework\System\Capabilities\ExternalState\Capabilities\Adapters\RedisStateAdapter;

final readonly class StateAudit
{
    public function __construct(
        public string $session,
        public string $cache,
        public string $lock,
        public string $rateLimit,
    )
    {
    }

    /**
     * @return array{session: string, cache: string, lock: string, rate_limit: string}
     */
    public function toArray(): array
    {
        return [
            'session' => $this->session,
            'cache' => $this->cache,
            'lock' => $this->lock,
            'rate_limit' => $this->rateLimit,
        ];
    }

    public function isHorizontalReady(): bool
    {
        return $this->session === 'Redis'
            && $this->cache === 'Redis'
            && $this->lock === 'Redis'
            && $this->rateLimit === 'Redis';
    }
}
