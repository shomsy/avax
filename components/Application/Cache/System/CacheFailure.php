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
        string    $message,
        private readonly int $failureCode = 0,
        Throwable $throwable = null,
    )
    {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }

    public static function storeUnavailable(string $storeName, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('System store "%s" is unavailable', $storeName),
            code    : self::CODE_STORE_UNAVAILABLE,
            previous: $throwable,
        );
    }

    public static function invalidKey(string $key, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('Invalid cache key: "%s"', $key),
            code    : self::CODE_KEY_INVALID,
            previous: $throwable,
        );
    }

    public static function serializationFailed(string $reason, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('System serialization failed: %s', $reason),
            code    : self::CODE_SERIALIZATION_FAILED,
            previous: $throwable,
        );
    }

    public static function capacityExceeded(int $maxSize, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('System capacity exceeded maximum of %d entries', $maxSize),
            code    : self::CODE_CAPACITY_EXCEEDED,
            previous: $throwable,
        );
    }

    public static function networkError(string $message, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('System network error: %s', $message),
            code    : self::CODE_NETWORK_ERROR,
            previous: $throwable,
        );
    }

    public static function sourceUnavailable(string $sourceName, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('System source "%s" is unavailable', $sourceName),
            code    : self::CODE_SOURCE_UNAVAILABLE,
            previous: $throwable,
        );
    }

    public static function lockTimeout(string $key, int $timeoutSeconds, Throwable $throwable = null) : self
    {
        return new self(
            message : sprintf('Lock timeout for key "%s" after %d seconds', $key, $timeoutSeconds),
            code    : self::CODE_LOCK_TIMEOUT,
            previous: $throwable,
        );
    }

    public function failureCode() : int
    {
        return $this->failureCode;
    }
}
