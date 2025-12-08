<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use Avax\HTTP\Session\Contracts\Storage\Store;

/**
 * NullStore - No-Op Session Storage
 *
 * Dummy storage that discards all data.
 * Useful for dry-run mode or testing without side effects.
 * 
 * Use Cases:
 * - Performance testing (no I/O overhead)
 * - Dry-run scenarios
 * - Feature flags (disabled state)
 * 
 * @package Avax\HTTP\Session
 */
final class NullStore extends AbstractStore
{
    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value): void
    {
        // No-op
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key): bool
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key): void
    {
        // No-op
    }

    /**
     * {@inheritdoc}
     */
    public function all(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function flush(): void
    {
        // No-op
    }
}
