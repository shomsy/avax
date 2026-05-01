<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Version;

final class AvaxVersion
{
    public const int MAJOR = 1;

    public const int MINOR = 0;

    public const int PATCH = 0;

    public const string VERSION = '1.0.0';

    public const string NAME = 'Avax Runtime-Agnostic Framework';

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
