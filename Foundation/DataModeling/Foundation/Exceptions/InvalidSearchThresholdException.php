<?php

declare(strict_types=1);

namespace Avax\DataModeling\Foundation\Exceptions;

/**
 * Thrown when search threshold is out of valid range.
 */
class InvalidSearchThresholdException extends DataModelingException
{
    public static function thresholdOutOfRange(int $threshold) : self
    {
        return new self(message: "Threshold must be between 0 and 100, got '{$threshold}'.");
    }
}