<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Ownership;

/**
 * Canonical slice visibility vocabulary.
 */
final class RegistrationVisibility
{
    public const string PRIVATE = 'private';

    public const string SHARED = 'shared';

    public const string PUBLIC = 'public';

    public const string INTERNAL = 'internal';

    public static function normalize(string $visibility) : string
    {
        $normalized = strtolower(trim($visibility));

        return in_array($normalized, self::all(), true)
            ? $normalized
            : self::PUBLIC;
    }

    /**
     * @return list<string>
     */
    public static function all() : array
    {
        return [
            self::PRIVATE,
            self::SHARED,
            self::PUBLIC,
            self::INTERNAL,
        ];
    }
}
