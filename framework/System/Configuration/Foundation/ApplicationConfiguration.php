<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\Foundation;

/**
 * ApplicationConfiguration — Typed, immutable application configuration.
 *
 * Loaded once at boot. Stays warm as immutable state.
 */
final readonly class ApplicationConfiguration
{
    public function __construct(
        public string $name = 'AvaX',
        public string $environment = 'production',
        public bool $debug = false,
        public string $timezone = 'UTC',
        public string $version = '4.0.0',
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            name: (string) ($raw['name'] ?? 'AvaX'),
            environment: (string) ($raw['environment'] ?? 'production'),
            debug: (bool) ($raw['debug'] ?? false),
            timezone: (string) ($raw['timezone'] ?? 'UTC'),
            version: (string) ($raw['version'] ?? '4.0.0'),
        );
    }

    public function isProduction(): bool
    {
        return $this->environment === 'production';
    }

    public function isDevelopment(): bool
    {
        return $this->environment === 'development';
    }
}
