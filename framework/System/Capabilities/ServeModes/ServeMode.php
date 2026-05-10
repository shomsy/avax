<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\ServeModes;

/**
 * Canonical serve modes as a backed enum.
 *
 * Replaces bare string literals like 'built-in', 'reactphp', etc.
 * with type-safe enum cases.
 */
enum ServeMode: string
{
    case BuiltIn = 'built-in';
    case ReactPhp = 'reactphp';
    case FrankenPhp = 'frankenphp';
    case Swoole = 'swoole';
    case Workerman = 'workerman';

    /**
     * Create a ServeMode from a string value.
     */
    public static function fromString(string $mode): self
    {
        return match (strtolower($mode)) {
            'built-in' => self::BuiltIn,
            'reactphp' => self::ReactPhp,
            'frankenphp' => self::FrankenPhp,
            'swoole' => self::Swoole,
            'workerman' => self::Workerman,
            default => self::BuiltIn,
        };
    }

    /**
     * Try to create a ServeMode from a string, returning null if unknown.
     */
    public static function tryFromString(string $mode): ?self
    {
        return match (strtolower($mode)) {
            'built-in' => self::BuiltIn,
            'reactphp' => self::ReactPhp,
            'frankenphp' => self::FrankenPhp,
            'swoole' => self::Swoole,
            'workerman' => self::Workerman,
            default => null,
        };
    }
}
