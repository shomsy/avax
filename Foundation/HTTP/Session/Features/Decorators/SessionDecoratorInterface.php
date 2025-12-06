<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features\Decorators;

use Avax\HTTP\Session\Core\SessionContext;

/**
 * SessionDecoratorInterface
 *
 * Contract for session decorators.
 *
 * @package Avax\HTTP\Session\Features\Decorators
 */
interface SessionDecoratorInterface
{
    /**
     * Set a value.
     *
     * @param string $key   The key.
     * @param mixed  $value The value.
     *
     * @return void
     */
    public function set(string $key, mixed $value): void;

    /**
     * Get a value.
     *
     * @param string $key     The key.
     * @param mixed  $default Default value.
     *
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Delete a value.
     *
     * @param string $key The key.
     *
     * @return void
     */
    public function delete(string $key): void;

    /**
     * Flush all data.
     *
     * @return void
     */
    public function flush(): void;
}
