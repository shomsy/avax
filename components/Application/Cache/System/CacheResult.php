<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

class CacheResult
{
    public readonly CacheResultState $cacheResultState;

    public readonly ?CacheKey $cacheKey;

    public function __construct(
        public readonly CacheResultState $cacheResultState,
        public readonly mixed     $value,
        public readonly ?CacheKey $cacheKey = null,
        public readonly ?string   $message = null,
    )
    {
        $this->cacheResultState = $cacheResultState;
        $this->cacheKey = $cacheKey;
    }

    public static function hit(mixed $value, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: CacheResultState::HIT,
            value: $value,
            key  : $cacheKey,
        );
    }

    public static function miss(?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: CacheResultState::MISS,
            value: null,
            key  : $cacheKey,
        );
    }

    public static function expired(mixed $value, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: CacheResultState::EXPIRED,
            value: $value,
            key  : $cacheKey,
        );
    }

    public static function stale(mixed $value, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: CacheResultState::STALE,
            value: $value,
            key  : $cacheKey,
        );
    }

    public static function stored(bool $success, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: $success ? CacheResultState::STORED : CacheResultState::STORE_FAILED,
            value: $success,
            key  : $cacheKey,
        );
    }

    public static function deleted(bool $success, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state: $success ? CacheResultState::DELETED : CacheResultState::DELETE_FAILED,
            value: $success,
            key  : $cacheKey,
        );
    }

    public static function cleared(bool $success) : self
    {
        return new self(
            state: $success ? CacheResultState::CLEARED : CacheResultState::CLEAR_FAILED,
            value: $success,
        );
    }

    public static function error(string $message, ?CacheKey $cacheKey = null) : self
    {
        return new self(
            state  : CacheResultState::ERROR,
            value  : null,
            key    : $cacheKey,
            message: $message,
        );
    }

    public function isMiss() : bool
    {
        return $this->cacheResultState === CacheResultState::MISS;
    }

    public function isSuccess() : bool
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

    public function getValueOrDefault(mixed $default = null) : mixed
    {
        if ($this->isHit() || $this->isStale() || $this->isExpired()) {
            return $this->value;
        }

        return $default;
    }

    public function isHit() : bool
    {
        return $this->cacheResultState === CacheResultState::HIT;
    }

    public function isStale() : bool
    {
        return $this->cacheResultState === CacheResultState::STALE;
    }

    public function isExpired() : bool
    {
        return $this->cacheResultState === CacheResultState::EXPIRED;
    }
}
