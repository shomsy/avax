<?php

declare(strict_types=1);

namespace Avax\Container\Containers;

enum LifecycleHook: string
{
    /**
     * Called when the container is initialized.
     */
    case INIT = 'init';

    /**
     * Called when the container is shutting down.
     */
    case SHUTDOWN = 'shutdown';

    /**
     * Called when an error occurs within the container.
     */
    case ERROR = 'error';

    /**
     * Get all available lifecycle hook types.
     *
     * @return array<string> List of lifecycle hook values.
     */
    public static function all() : array
    {
        return array_map(callback: static fn(self $hook) : string => $hook->value, array: self::cases());
    }

    /**
     * Check if a given value is a valid lifecycle hook.
     *
     * @param string $value The value to check.
     *
     * @return bool True if the value is a valid lifecycle hook, false otherwise.
     */
    public static function isValid(string $value) : bool
    {
        return in_array(needle: $value, haystack: self::all(), strict: true);
    }
}
