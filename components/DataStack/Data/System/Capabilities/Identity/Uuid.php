<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Identity;

use InvalidArgumentException;

/**
 * UUID Value Object.
 * Migrated from DataFoundation.
 */
final readonly class Uuid
{
    public function __construct(public string $value)
    {
        if (! preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
            throw new InvalidArgumentException("Invalid UUID: {$value}");
        }
    }

    public static function generate() : self
    {
        return new self(sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
                                mt_rand(0, 0xffff), mt_rand(0, 0xffff),
                                mt_rand(0, 0xffff),
                                mt_rand(0, 0x0fff) | 0x4000,
                                mt_rand(0, 0x3fff) | 0x8000,
                                mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
                        ));
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
