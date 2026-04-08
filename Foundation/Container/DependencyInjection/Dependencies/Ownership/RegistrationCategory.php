<?php

declare(strict_types=1);

namespace Avax\Container\DependencyInjection\Dependencies\Ownership;

/**
 * Canonical registration category vocabulary.
 */
final class RegistrationCategory
{
    public const string FLOW = 'flow';

    public const string CAPABILITY = 'capability';

    public const string CONFIGURATION = 'configuration';

    public const string FOUNDATION = 'foundation';

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

    public static function normalize(string $category) : string
    {
        $normalized = strtolower(trim($category));

        return in_array($normalized, self::all(), true)
            ? $normalized
            : self::CONFIGURATION;
    }
}
