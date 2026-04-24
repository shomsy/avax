<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Exceptions;

/**
 * Raised when a threshold falls outside its allowed range.
 */
class InvalidThresholdException extends DataFoundationException
{
    public static function thresholdOutOfRange(int $threshold) : self
    {
        return new self(message: "Threshold must be between 0 and 100, got '{$threshold}'.");
    }
}
