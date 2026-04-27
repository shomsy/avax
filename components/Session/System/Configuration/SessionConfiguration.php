<?php

declare(strict_types=1);

namespace Avax\Components\Session\System\Configuration;

final class SessionConfiguration
{
    public function __construct(
        public readonly string $driver = 'file',
        public readonly int $lifetime = 120,
        public readonly string $path = '/',
        public readonly string $domain = '',
        public readonly bool $secure = false,
        public readonly bool $httpOnly = true,
        public readonly string $sameSite = 'lax',
    ) {
    }

    public static function fromArray(array $config): self
    {
        return new self(
            driver: $config['driver'] ?? 'file',
            lifetime: $config['lifetime'] ?? 120,
            path: $config['path'] ?? '/',
            domain: $config['domain'] ?? '',
            secure: $config['secure'] ?? false,
            httpOnly: $config['http_only'] ?? true,
            sameSite: $config['same_site'] ?? 'lax',
        );
    }
}