<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Features;

use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Contracts\FeatureInterface;

/**
 * Snapshots - Session State Snapshot Manager
 *
 * Provides snapshot and restore functionality for session state.
 * Useful for rollback scenarios, state comparison, or debugging.
 *
 * @example
 *   $snapshots = new Snapshots();
 *   $snapshots->snapshot('before_checkout', $sessionData);
 *   // ... later
 *   $data = $snapshots->restore('before_checkout');
 *
 * @package Avax\HTTP\Session
 */
final class Snapshots implements FeatureInterface
{
    /**
     * @var array<string, string> Snapshot storage (name => serialized data)
     */
    private array $snapshots = [];

    /**
     * @var bool Feature enabled state
     */
    private bool $enabled = true;

    /**
     * Create a snapshot of session state.
     *
     * @param string               $name Snapshot identifier.
     * @param array<string, mixed> $data Session data to snapshot.
     *
     * @return void
     */
    public function snapshot(string $name, array $data): void
    {
        $this->snapshots[$name] = serialize([
            'data' => $data,
            'timestamp' => time(),
            'name' => $name,
        ]);
    }

    /**
     * Restore session state from a snapshot.
     *
     * @param string $name Snapshot identifier.
     *
     * @return array<string, mixed>|null Session data or null if snapshot doesn't exist.
     */
    public function restore(string $name): ?array
    {
        if (!isset($this->snapshots[$name])) {
            return null;
        }

        $snapshot = unserialize($this->snapshots[$name]);

        return $snapshot['data'] ?? null;
    }

    /**
     * Check if a snapshot exists.
     *
     * @param string $name Snapshot identifier.
     *
     * @return bool True if snapshot exists.
     */
    public function has(string $name): bool
    {
        return isset($this->snapshots[$name]);
    }

    /**
     * Delete a snapshot.
     *
     * @param string $name Snapshot identifier.
     *
     * @return void
     */
    public function delete(string $name): void
    {
        unset($this->snapshots[$name]);
    }

    /**
     * Get all snapshot names.
     *
     * @return array<int, string> List of snapshot names.
     */
    public function all(): array
    {
        return array_keys($this->snapshots);
    }

    /**
     * Save all snapshots to a Store.
     *
     * Enables persistence across requests.
     *
     * @param Store  $store The storage backend.
     * @param string $key   Storage key (default: '_snapshots').
     *
     * @return void
     */
    public function saveTo(Store $store, string $key = '_snapshots'): void
    {
        $store->put($key, $this->snapshots);
    }

    /**
     * Load snapshots from a Store.
     *
     * Restores snapshots from persistent storage.
     *
     * @param Store  $store The storage backend.
     * @param string $key   Storage key (default: '_snapshots').
     *
     * @return void
     */
    public function loadFrom(Store $store, string $key = '_snapshots'): void
    {
        $snapshots = $store->get($key, []);

        if (is_array($snapshots)) {
            $this->snapshots = $snapshots;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function boot(): void
    {
        // Snapshots are ready on construction
        $this->enabled = true;
    }

    /**
     * {@inheritdoc}
     */
    public function terminate(): void
    {
        // Clear all snapshots on termination
        $this->snapshots = [];
        $this->enabled = false;
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'snapshots';
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
