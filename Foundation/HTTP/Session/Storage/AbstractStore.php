<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use Avax\HTTP\Session\Contracts\Storage\Store;

/**
 * AbstractStore - Base Store Implementation
 *
 * Provides common functionality for all Store implementations.
 * Reduces code duplication across NativeStore, ArrayStore, NullStore.
 *
 * Default Implementations:
 * - has(): Checks if get() returns non-null
 * - pull(): Gets and deletes in one operation
 * - increment()/decrement(): Numeric operations
 * - clear(): Alias for flush()
 *
 * @package Avax\HTTP\Session\Storage
 */
abstract class AbstractStore implements Store
{
    /**
     * {@inheritdoc}
     */
    public function has(string $key) : bool
    {
        return $this->get($key) !== null;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function get(string $key, mixed $default = null) : mixed;

    /**
     * Get and remove a value in one operation.
     *
     * @param string     $key     The key.
     * @param mixed|null $default Default value.
     *
     * @return mixed The value or default.
     */
    public function pull(string $key, mixed $default = null) : mixed
    {
        $value = $this->get($key, $default);

        if ($value !== $default) {
            $this->delete($key);
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function delete(string $key) : void;

    /**
     * Decrement a numeric value.
     *
     * @param string $key   The key.
     * @param int    $value Amount to decrement (default: 1).
     *
     * @return int New value.
     */
    public function decrement(string $key, int $value = 1) : int
    {
        return $this->increment($key, -$value);
    }

    /**
     * Increment a numeric value.
     *
     * @param string $key   The key.
     * @param int    $value Amount to increment (default: 1).
     *
     * @return int New value.
     */
    public function increment(string $key, int $value = 1) : int
    {
        $current = (int) $this->get($key, 0);
        $new     = $current + $value;
        $this->put($key, $new);

        return $new;
    }

    /**
     * {@inheritdoc}
     */
    abstract public function put(string $key, mixed $value) : void;

    /**
     * Check if store is empty.
     *
     * @return bool True if no data stored.
     */
    public function isEmpty() : bool
    {
        return empty($this->all());
    }

    /**
     * {@inheritdoc}
     */
    abstract public function all() : array;

    /**
     * Get number of stored items.
     *
     * @return int Item count.
     */
    public function count() : int
    {
        return count($this->all());
    }

    /**
     * Alias for flush().
     *
     * @return void
     */
    public function clear() : void
    {
        $this->flush();
    }

    /**
     * {@inheritdoc}
     */
    abstract public function flush() : void;

    /**
     * Store multiple key-value pairs.
     *
     * @param array<string, mixed> $values Key-value pairs.
     *
     * @return void
     */
    public function putMany(array $values) : void
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }
    }

    /**
     * Delete multiple keys.
     *
     * @param array<string> $keys Keys to delete.
     *
     * @return void
     */
    public function deleteMany(array $keys) : void
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }
    }
}
