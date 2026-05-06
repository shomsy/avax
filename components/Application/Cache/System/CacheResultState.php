<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

enum CacheResultState: string
{
    case HIT = 'hit';
    case MISS = 'miss';
    case EXPIRED = 'expired';
    case STALE = 'stale';
    case STORED = 'stored';
    case STORE_FAILED = 'store_failed';
    case DELETED = 'deleted';
    case DELETE_FAILED = 'delete_failed';
    case CLEARED = 'cleared';
    case CLEAR_FAILED = 'clear_failed';
    case ERROR = 'error';

    public function isUsable(): bool
    {
        return match ($this) {
            self::HIT, self::STALE, self::EXPIRED => true,
            default => false,
        };
    }

    public function indicatesStoreSuccess(): bool
    {
        return match ($this) {
            self::STORED, self::DELETED, self::CLEARED => true,
            default => false,
        };
    }

    public function indicatesFailure(): bool
    {
        return match ($this) {
            self::STORE_FAILED, self::DELETE_FAILED, self::CLEAR_FAILED, self::ERROR => true,
            default => false,
        };
    }
}
