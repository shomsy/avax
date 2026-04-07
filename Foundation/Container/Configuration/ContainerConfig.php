<?php

declare(strict_types=1);

namespace Avax\Container\Configuration;

/**
 * Immutable build-time options for assembling a container runtime.
 */
final readonly class ContainerConfig
{
    /**
     * @param array<string, mixed> $settings
     */
    public function __construct(
        public string $cacheDir = '',
        public bool $debug = false,
        public array $settings = []
    ) {}

    /**
     * @param array<string, mixed> $settings
     */
    public static function create(string $cacheDir = '', bool $debug = false, array $settings = []) : self
    {
        return new self(
            cacheDir: $cacheDir,
            debug   : $debug,
            settings: $settings
        );
    }

    /**
     * @param array<string, mixed> $settings
     */
    public function withSettings(array $settings) : self
    {
        return new self(
            cacheDir: $this->cacheDir,
            debug   : $this->debug,
            settings: $settings
        );
    }

    public function withDebug(bool $debug) : self
    {
        return new self(
            cacheDir: $this->cacheDir,
            debug   : $debug,
            settings: $this->settings
        );
    }

    public function withCacheDir(string $cacheDir) : self
    {
        return new self(
            cacheDir: $cacheDir,
            debug   : $this->debug,
            settings: $this->settings
        );
    }

    public function cacheDirectory() : string
    {
        return $this->cacheDir !== '' ? $this->cacheDir : sys_get_temp_dir();
    }
}
