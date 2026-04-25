<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCacheLifecycle\InvalidationStrategies;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheVersion;

final class VersionedKeyInvalidation implements InvalidationStrategy
{
    private ?CacheVersion $currentVersion = null;

    public function __construct(
        private CacheVersion $initialVersion,
        ?CacheVersion        $currentVersion = null
    )
    {
        $this->currentVersion = $currentVersion ?? $initialVersion;
    }

    public function shouldInvalidate(string $key, string $reason, array $context = []) : bool
    {
        if ($reason === 'version_change') {
            return true;
        }

        $keyVersion = $context['key_version'] ?? null;

        if ($keyVersion instanceof CacheVersion && $this->currentVersion instanceof CacheVersion) {
            if (! $keyVersion->isCompatibleWith($this->currentVersion)) {
                return true;
            }
        }

        return false;
    }

    public function strategyName() : string
    {
        return 'versioned_key';
    }

    public function bumpVersion() : self
    {
        $new                 = clone $this;
        $new->currentVersion = $this->currentVersion->incrementMajor();

        return $new;
    }

    public function getCurrentVersion() : ?CacheVersion
    {
        return $this->currentVersion;
    }
}