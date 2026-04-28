<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Collections\Internal\Support;

use Avax\Components\DataStack\Data\System\Foundation\Exceptions\DataException;

/**
 * Validated search threshold value object.
 */
final readonly class Threshold
{
    public const MIN     = 0;
    public const MAX     = 100;
    public const DEFAULT = 70;

    public function __construct(public int $value)
    {
        if ($value < self::MIN || $value > self::MAX) {
            throw new DataException(sprintf('Threshold %d is out of range [%d, %d].', $value, self::MIN, self::MAX));
        }
    }

    public static function default() : self { return new self(self::DEFAULT); }

    public function isDefault() : bool { return $this->value === self::DEFAULT; }
}
