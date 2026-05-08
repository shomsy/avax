<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Values\Identity;

use InvalidArgumentException;
use Override;
use Stringable;

/**
 * UUID Value Object.
 * Migrated from DataFoundation.
 */
final readonly class Uuid implements Stringable
{
    public function __construct(public string $value)
    {
        if (in_array(preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value), [0, false], true)) {
            throw new InvalidArgumentException('Invalid UUID: '.$value);
        }
    }

    public static function generate(): self
    {
        return new self(sprintf(
            '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0x0FFF) | 0x4000,
            mt_rand(0, 0x3FFF) | 0x8000,
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
            mt_rand(0, 0xFFFF),
        ));
    }

    #[Override]
    public function __toString(): string
    {
        return $this->value;
    }
}
