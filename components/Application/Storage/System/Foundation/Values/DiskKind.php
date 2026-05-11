<?php

declare(strict_types=1);

namespace Avax\Components\Application\Storage\System\Foundation\Values;

use InvalidArgumentException;

/**
 * Canonical disk kinds as a backed enum.
 *
 * Replaces bare string literals like 'local', 's3', etc.
 * with type-safe enum cases.
 */
enum DiskKind: string
{
    case Local = 'local';
    case S3 = 's3';
    case Public_ = 'public';

    /**
     * Create a DiskKind from a string value.
     *
     * @throws InvalidArgumentException if the disk name is not a registered canonical disk.
     */
    public static function fromString(string $name): self
    {
        return match ($name) {
            'local' => self::Local,
            's3' => self::S3,
            'public' => self::Public_,
            default => throw new InvalidArgumentException("Unknown disk name: {$name}"),
        };
    }

    /**
     * Try to create a DiskKind from a string, returning null if unknown.
     */
    public static function tryFromString(string $name) : self|null
    {
        return match ($name) {
            'local' => self::Local,
            's3' => self::S3,
            'public' => self::Public_,
            default => null,
        };
    }
}
