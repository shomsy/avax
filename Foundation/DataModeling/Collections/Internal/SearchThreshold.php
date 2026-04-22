<?php

declare(strict_types=1);

namespace Avax\DataModeling\Collections\Internal;

use InvalidArgumentException;

/**
 * Validated search threshold value object.
 */
final readonly class SearchThreshold
{
    public const MIN     = 0;
    public const MAX     = 100;
    public const DEFAULT = 70;

    public function __construct(
        public int $value,
    )
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new InvalidArgumentException(
                message: "Threshold must be between " . self::MIN . " and " . self::MAX . ", got '{$value}'."
            );
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