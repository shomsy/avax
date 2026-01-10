<?php

declare(strict_types=1);
namespace Avax\Container\Core;

/**
 * Configuration class for Container setup.
 *
 * Provides a centralized way to configure container components and settings
 * before constructing the ContainerKernel. Acts as a configuration builder
 * for customizing container behavior, ensuring immutable configuration that prevents runtime drift.
 *
 * @see docs/Core/ContainerConfig.md#quick-summary
 */
readonly class ContainerConfig
{
    /**
     * Initialize config with settings array.
     *
     * @param array $settings Configuration settings
     */
    public function __construct(public array $settings = []) {}

    /**
     * Create a new config instance with updated settings.
     *
     * @param array $settings Settings to merge
     * @return self New config instance
     * @see docs/Core/ContainerConfig.html
     */
    public function withSettings(array $settings) : self
    {
        return new self(settings: array_merge($this->settings, $settings));
    }

    /**
     * Get a setting value with optional default.
     *
     * @param string $key Setting key
     * @param mixed $default Default value if key not found
     * @return mixed Setting value or default
     * @see docs/Core/ContainerConfig.html
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->settings[$key] ?? $default;
    }

    /**
     * Check if a setting exists.
     *
     * @param string $key Setting key to check
     * @return bool True if setting exists
     * @see docs/Core/ContainerConfig.html
     */
    public function has(string $key) : bool
    {
        return array_key_exists($key, $this->settings);
    }
}