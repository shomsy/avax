<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System;

use RuntimeException;
use Throwable;

final class CacheFailure extends RuntimeException
{
    public const int CODE_STORE_UNAVAILABLE = 1001;

    public const int CODE_KEY_INVALID = 1002;

    public const int CODE_SERIALIZATION_FAILED = 1003;

    public const int CODE_CAPACITY_EXCEEDED = 1004;

    public const int CODE_NETWORK_ERROR = 1005;

    public const int CODE_SOURCE_UNAVAILABLE = 1006;

    public const int CODE_LOCK_TIMEOUT = 1007;

    public const int CODE_VALIDATION_FAILED = 1008;

    public function __construct(
        string $message,
        private readonly int $failureCode = 0, Throwable|null $throwable = null,
    ) {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }

    public static function storeUnavailable(string $storeName, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('System store "%s" is unavailable', $storeName),
            failureCode : self::CODE_STORE_UNAVAILABLE,
            throwable   : $throwable,
        );
    }

    public static function invalidKey(string $key, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('Invalid cache cacheKey "%s"', $key),
            failureCode : self::CODE_KEY_INVALID,
            throwable   : $throwable,
        );
    }

    public static function serializationFailed(string $reason, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('System serialization failed: %s', $reason),
            failureCode : self::CODE_SERIALIZATION_FAILED,
            throwable   : $throwable,
        );
    }

    public static function capacityExceeded(int $maxSize, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('System capacity exceeded maximum of %d entries', $maxSize),
            failureCode : self::CODE_CAPACITY_EXCEEDED,
            throwable   : $throwable,
        );
    }

    public static function networkError(string $message, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('System network error: %s', $message),
            failureCode : self::CODE_NETWORK_ERROR,
            throwable   : $throwable,
        );
    }

    public static function sourceUnavailable(string $sourceName, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('System source "%s" is unavailable', $sourceName),
            failureCode : self::CODE_SOURCE_UNAVAILABLE,
            throwable   : $throwable,
        );
    }

    public static function lockTimeout(string $key, int $timeoutSeconds, Throwable|null $throwable = null) : self
    {
        return new self(
            message     : sprintf('Lock timeout for key "%s" after %d seconds', $key, $timeoutSeconds),
            failureCode : self::CODE_LOCK_TIMEOUT,
            throwable   : $throwable,
        );
    }

    public function failureCode(): int
    {
        return $this->failureCode;
    }
}
