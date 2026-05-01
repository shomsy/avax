<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

class CacheResult
{
    public function __construct(
        public readonly CacheResultState $cacheResultState,
        public readonly mixed $value,
        public readonly ?CacheKey $cacheKey = null,
        public readonly ?string $message = null,
    ) {
    }

    public static function hit(mixed $value, ?CacheKey $cacheKey = null): self
    {
        return new self(
            CacheResultState::HIT,
            $value,
            $cacheKey,
            null,
        );
    }

    public static function miss(?CacheKey $cacheKey = null): self
    {
        return new self(
            null,
            CacheResultState::MISS,
            $cacheKey,
            null,
        );
    }

    public static function expired(mixed $value, ?CacheKey $cacheKey = null): self
    {
        return new self(
            CacheResultState::EXPIRED,
            $value,
            $cacheKey,
            null,
        );
    }

    public static function stale(mixed $value, ?CacheKey $cacheKey = null): self
    {
        return new self(
            value: $value,
            state: CacheResultState::STALE,
            key  : $cacheKey,
        );
    }

    public static function stored(bool $success, ?CacheKey $cacheKey = null): self
    {
        return new self(
            $success,
            $success ? CacheResultState::STORED : CacheResultState::STORE_FAILED,
            $cacheKey,
        );
    }

    public static function deleted(bool $success, ?CacheKey $cacheKey = null): self
    {
        return new self(
            $success,
            $success ? CacheResultState::DELETED : CacheResultState::DELETE_FAILED,
            $cacheKey,
        );
    }

    public static function cleared(bool $success): self
    {
        return new self(
            value: $success,
            state: $success ? CacheResultState::CLEARED : CacheResultState::CLEAR_FAILED,
        );
    }

    public static function error(string $message, ?CacheKey $cacheKey = null): self
    {
        return new self(
            CacheResultState::ERROR,
            null,
            $cacheKey,
            $message,
        );
    }

    public function isMiss(): bool
    {
        return $this->cacheResultState === CacheResultState::MISS;
    }

    public function isSuccess(): bool
    {
        return in_array($this->cacheResultState, [
            CacheResultState::HIT,
            CacheResultState::MISS,
            CacheResultState::EXPIRED,
            CacheResultState::STALE,
            CacheResultState::STORED,
            CacheResultState::DELETED,
            CacheResultState::CLEARED,
        ], strict:      true);
    }

    public function getValueOrDefault(mixed $default = null): mixed
    {
        if ($this->isHit() || $this->isStale() || $this->isExpired()) {
            return $this->value;
        }

        return $default;
    }

    public function isHit(): bool
    {
        return $this->cacheResultState === CacheResultState::HIT;
    }

    public function isStale(): bool
    {
        return $this->cacheResultState === CacheResultState::STALE;
    }

    public function isExpired(): bool
    {
        return $this->cacheResultState === CacheResultState::EXPIRED;
    }
}
