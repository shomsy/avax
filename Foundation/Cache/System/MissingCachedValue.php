<?php

declare(strict_types=1);

namespace Avax\Cache\System;

final class MissingCachedValue
{
    public function __construct(
        public readonly string $key,
        public readonly int    $attemptedAt
    ) {}

    public static function forKey(string $key) : self
    {
        return new self(
            key        : $key,
            attemptedAt: time()
        );
    }

    public function toException() : CacheFailure
    {
        return CacheFailure::invalidKey($this->key);
    }
}