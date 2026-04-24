<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Internal\Support;

use Avax\DataFoundation\Exceptions\InvalidThresholdException;

/**
 * Validated search threshold value object.
 */
final readonly class Threshold
{
    public const MIN     = 0;
    public const MAX     = 100;
    public const DEFAULT = 70;

    public function __construct(
        public int $value,
    )
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw InvalidThresholdException::thresholdOutOfRange(threshold: $value);
        }
    }

    public static function default() : self
    {
        return new self(value: self::DEFAULT);
    }

    public function isDefault() : bool
    {
        return $this->value === self::DEFAULT;
    }
}
