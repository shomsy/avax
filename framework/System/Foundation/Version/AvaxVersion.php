<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Version;

final class AvaxVersion
{
    public const MAJOR = 1;

    public const MINOR = 0;

    public const PATCH = 0;

    public const VERSION = '1.0.0';

    public const NAME = 'Avax Runtime-Agnostic Framework';

    public static function version(): string
    {
        return self::VERSION;
    }

    public static function name(): string
    {
        return self::NAME;
    }

    public static function isCompatible(string $constraint): bool
    {
        return version_compare(self::VERSION, $constraint, '>=');
    }
}
