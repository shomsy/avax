<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

/**
 * Immutable build-time options for assembling a container runtime.
 */
final readonly class CreateContainerConfig
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public string $cacheDir = '',
        public string $cacheVersion = 'container-v1',
        public bool $debug = false,
        public array $settings = [],
        public bool $strict = false
    ) {}

    /**
     * @param array<string, mixed> $settings
     */
    public static function create(
        string $cacheDir = '',
        string $cacheVersion = 'container-v1',
        bool $debug = false,
        array $settings = [],
        bool $strict = false
    ) : self
    {
        return new self(
            cacheDir    : $cacheDir,
            cacheVersion: $cacheVersion,
            debug       : $debug,
            settings    : $settings,
            strict      : $strict
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function withSettings(array $settings) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $settings,
            strict      : $this->strict
        );
    }

    public function withDebug(bool $debug) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $debug,
            settings    : $this->settings,
            strict      : $this->strict
        );
    }

    public function withStrict(bool $strict) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $strict
        );
    }

    public function withCacheDir(string $cacheDir) : self
    {
        return new self(
            cacheDir    : $cacheDir,
            cacheVersion: $this->cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict
        );
    }

    public function withCacheVersion(string $cacheVersion) : self
    {
        return new self(
            cacheDir    : $this->cacheDir,
            cacheVersion: $cacheVersion,
            debug       : $this->debug,
            settings    : $this->settings,
            strict      : $this->strict
        );
    }
}
