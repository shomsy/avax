<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Lifecycle\InvalidateCachedValues;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheVersion;
use Override;

final class VersionedKeyInvalidation implements InvalidationStrategy
{
    private ?CacheVersion $cacheVersion = null;

    public function __construct(
        CacheVersion $initialVersion,
        ?CacheVersion $currentVersion = null,
    )
    {
        $this->cacheVersion = $currentVersion ?? $initialVersion;
    }

    #[Override]
    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if ($reason === 'version_change') {
            return true;
        }

        $keyVersion = $context['key_version'] ?? null;

        return $keyVersion instanceof CacheVersion && $this->cacheVersion instanceof CacheVersion && ! $keyVersion->isCompatibleWith(other: $this->cacheVersion);
    }

    #[Override]
    public function strategyName() : string
    {
        return 'versioned_key';
    }

    public function bumpVersion() : self
    {
        $new = clone $this;
        $new->cacheVersion = $this->cacheVersion->incrementMajor();

        return $new;
    }

    public function getCurrentVersion() : ?CacheVersion
    {
        return $this->cacheVersion;
    }
}
