<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;

class CacheResult
{
    public function __construct(
        public readonly CacheResultState $state,
        public readonly mixed            $value,
        public readonly CacheKey|null    $key = null,
        public readonly string|null      $message = null
    ) {}

    public static function hit(mixed $value, CacheKey|null $key = null) : self
    {
        return new self(
            state: CacheResultState::HIT,
            value: $value,
            key  : $key
        );
    }

    public static function miss(CacheKey|null $key = null) : self
    {
        return new self(
            state: CacheResultState::MISS,
            value: null,
            key  : $key
        );
    }

    public static function expired(mixed $value, CacheKey|null $key = null) : self
    {
        return new self(
            state: CacheResultState::EXPIRED,
            value: $value,
            key  : $key
        );
    }

    public static function stale(mixed $value, CacheKey|null $key = null) : self
    {
        return new self(
            state: CacheResultState::STALE,
            value: $value,
            key  : $key
        );
    }

    public static function stored(bool $success, CacheKey|null $key = null) : self
    {
        return new self(
            state: $success ? CacheResultState::STORED : CacheResultState::STORE_FAILED,
            value: $success,
            key  : $key
        );
    }

    public static function deleted(bool $success, CacheKey|null $key = null) : self
    {
        return new self(
            state: $success ? CacheResultState::DELETED : CacheResultState::DELETE_FAILED,
            value: $success,
            key  : $key
        );
    }

    public static function cleared(bool $success) : self
    {
        return new self(
            state: $success ? CacheResultState::CLEARED : CacheResultState::CLEAR_FAILED,
            value: $success
        );
    }

    public static function error(string $message, CacheKey|null $key = null) : self
    {
        return new self(
            state  : CacheResultState::ERROR,
            value  : null,
            key    : $key,
            message: $message
        );
    }

    public function isMiss() : bool
    {
        return $this->state === CacheResultState::MISS;
    }

    public function isSuccess() : bool
    {
        return in_array($this->state, [
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
        return $this->state === CacheResultState::HIT;
    }

    public function isStale() : bool
    {
        return $this->state === CacheResultState::STALE;
    }
}