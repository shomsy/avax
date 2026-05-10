<?php

declare(strict_types=1);

namespace Avax\Framework\System\Foundation\Environment;

/**
 * Canonical environment names as a backed enum.
 *
 * Replaces bare string literals like 'production', 'local', 'testing', etc.
 * with type-safe enum cases.
 */
enum EnvironmentName: string
{
    case Production = 'production';
    case Local = 'local';
    case Testing = 'testing';
    case Staging = 'staging';
    case Development = 'development';

    /**
     * Create an EnvironmentName from a string value.
     */
    public static function fromString(string $name): self
    {
        return match (strtolower($name)) {
            'production', 'prod' => self::Production,
            'local' => self::Local,
            'testing', 'test' => self::Testing,
            'staging', 'stage' => self::Staging,
            'development', 'dev' => self::Development,
            default => self::Local,
        };
    }

    /**
     * Check if this is a production environment.
     */
    public function isProduction(): bool
    {
        return $this === self::Production;
    }

    /**
     * Check if this is a local/development environment.
     */
    public function isLocal(): bool
    {
        return $this === self::Local || $this === self::Development;
    }

    /**
     * Check if this is a testing environment.
     */
    public function isTesting(): bool
    {
        return $this === self::Testing;
    }
}
