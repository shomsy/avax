<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\Foundation;

/**
 * RuntimeConfiguration — Typed, immutable runtime configuration.
 *
 * Loaded once at boot. Stays warm as immutable state.
 */
final readonly class RuntimeConfiguration
{
    public function __construct(
        public string $defaultRuntime = 'built-in',
        public string $host = '0.0.0.0',
        public int $port = 8000,
        public int $memoryGuardSoftLimit = 128 * 1024 * 1024,
        public int $memoryGuardHardLimit = 256 * 1024 * 1024,
        public int $maxRequests = 0,
        public bool $warmSafetyEnabled = true,
        public string $serveMode = 'dev',
    ) {
    }

    /**
     * @param array<string, mixed> $raw
     */
    public static function fromArray(array $raw): self
    {
        return new self(
            defaultRuntime: (string) ($raw['default_runtime'] ?? 'built-in'),
            host: (string) ($raw['host'] ?? '0.0.0.0'),
            port: (int) ($raw['port'] ?? 8000),
            memoryGuardSoftLimit: (int) ($raw['memory_guard_soft_limit'] ?? (128 * 1024 * 1024)),
            memoryGuardHardLimit: (int) ($raw['memory_guard_hard_limit'] ?? (256 * 1024 * 1024)),
            maxRequests: (int) ($raw['max_requests'] ?? 0),
            warmSafetyEnabled: (bool) ($raw['warm_safety_enabled'] ?? true),
            serveMode: (string) ($raw['serve_mode'] ?? 'dev'),
        );
    }
}
