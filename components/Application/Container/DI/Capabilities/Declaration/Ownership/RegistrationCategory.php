<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Capabilities\Declaration\Ownership;

/**
 * Canonical registration category vocabulary.
 */
final class RegistrationCategory
{
    public const string FLOW = 'flow';

    public const string CAPABILITY = 'capability';

    public const string CONFIGURATION = 'configuration';

    public const string FOUNDATION = 'foundation';

    public static function normalize(string $category) : string
    {
        $normalized = strtolower(string: trim(string: $category));

        return in_array(needle: $normalized, haystack: self::all(), strict: true)
            ? $normalized
            : self::CONFIGURATION;
    }

    /**
     * @return list<string>
     */
    public static function all() : array
    {
        return [
            self::FLOW,
            self::CAPABILITY,
            self::CONFIGURATION,
            self::FOUNDATION,
        ];
    }
}
