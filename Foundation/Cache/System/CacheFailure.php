<?php

declare(strict_types=1);

namespace Avax\Cache\System;

use RuntimeException;
use Throwable;

final class CacheFailure extends RuntimeException
{
    public const CODE_STORE_UNAVAILABLE    = 1001;
    public const CODE_KEY_INVALID          = 1002;
    public const CODE_SERIALIZATION_FAILED = 1003;
    public const CODE_CAPACITY_EXCEEDED    = 1004;
    public const CODE_NETWORK_ERROR        = 1005;
    public const CODE_SOURCE_UNAVAILABLE   = 1006;
    public const CODE_LOCK_TIMEOUT         = 1007;
    public const CODE_VALIDATION_FAILED    = 1008;

    private int $failureCode;

    public function __construct(
        string      $message,
        int         $code = 0,
        ?Throwable $previous = null
    )
    {
        $this->failureCode = $code;
        parent::__construct($message, 0, $previous);
    }

    public static function storeUnavailable(string $storeName, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('System store "%s" is unavailable', $storeName),
            code    : self::CODE_STORE_UNAVAILABLE,
            previous: $previous
        );
    }

    public static function invalidKey(string $key, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('Invalid cache key: "%s"', $key),
            code    : self::CODE_KEY_INVALID,
            previous: $previous
        );
    }

    public static function serializationFailed(string $reason, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('System serialization failed: %s', $reason),
            code    : self::CODE_SERIALIZATION_FAILED,
            previous: $previous
        );
    }

    public static function capacityExceeded(int $maxSize, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('System capacity exceeded maximum of %d entries', $maxSize),
            code    : self::CODE_CAPACITY_EXCEEDED,
            previous: $previous
        );
    }

    public static function networkError(string $message, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('System network error: %s', $message),
            code    : self::CODE_NETWORK_ERROR,
            previous: $previous
        );
    }

    public static function sourceUnavailable(string $sourceName, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('System source "%s" is unavailable', $sourceName),
            code    : self::CODE_SOURCE_UNAVAILABLE,
            previous: $previous
        );
    }

    public static function lockTimeout(string $key, int $timeoutSeconds, ?Throwable $previous = null) : self
    {
        return new self(
            message : sprintf('Lock timeout for key "%s" after %d seconds', $key, $timeoutSeconds),
            code    : self::CODE_LOCK_TIMEOUT,
            previous: $previous
        );
    }

    public function failureCode() : int
    {
        return $this->failureCode;
    }
}