<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Ownership;

/**
 * Stores the active logical slice view inside one resolution context payload.
 */
final class SliceContext
{
    public const KEY = '__container_slice_view';

    public const ROOT = 'root';

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    public static function with(array $context, string $slice) : array
    {
        $normalized = self::normalize(slice: $slice);
        if ($normalized === '') {
            unset($context[self::KEY]);

            return $context;
        }

        $context[self::KEY] = $normalized;

        return $context;
    }

    /**
     * @param array<string, mixed> $context
     */
    public static function from(array $context) : string
    {
        $slice = $context[self::KEY] ?? '';

        return is_string($slice) ? self::normalize(slice: $slice) : '';
    }

    public static function normalize(string $slice) : string
    {
        return trim($slice);
    }

    public static function isRoot(string $slice) : bool
    {
        $normalized = self::normalize(slice: $slice);

        return $normalized === self::ROOT || $normalized === 'root.composition';
    }

    public static function category(string $slice) : string
    {
        $normalized = self::normalize(slice: $slice);

        if (str_starts_with($normalized, 'flow.')) {
            return RegistrationCategory::FLOW;
        }

        if (str_starts_with($normalized, 'capability.')) {
            return RegistrationCategory::CAPABILITY;
        }

        if (str_starts_with($normalized, 'configuration.')) {
            return RegistrationCategory::CONFIGURATION;
        }

        if (str_starts_with($normalized, 'foundation.')) {
            return RegistrationCategory::FOUNDATION;
        }

        return '';
    }

    public static function defaultVisibility(string $slice) : string
    {
        return match (self::category(slice: $slice)) {
            RegistrationCategory::FLOW => RegistrationVisibility::PRIVATE,
            RegistrationCategory::CAPABILITY,
            RegistrationCategory::CONFIGURATION,
            RegistrationCategory::FOUNDATION => RegistrationVisibility::INTERNAL,
            default => RegistrationVisibility::PUBLIC,
        };
    }
}
