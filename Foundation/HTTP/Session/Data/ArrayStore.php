<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Data;

/**
 * Session Component
 *
 * ArrayStore - In-Memory Session Storage
 *
 * In-memory storage for testing and development.
 * Data is lost after request ends.
 *
 * Perfect for:
 * - Unit tests
 * - Development/debugging
 * - Isolated test scenarios
 *
 * @author  Milos Stankovic
 * @package Shomsy\Components\Foundation\HTTP\Session\Data
 */
final class ArrayStore extends AbstractStore
{
    /**
     * @var array<string, mixed> In-memory storage
     */
    private array $data = [];

    /**
     * {@inheritdoc}
     */
    public function get(string $key, mixed $default = null) : mixed
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * {@inheritdoc}
     */
    public function put(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
    }

    /**
     * {@inheritdoc}
     */
    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $key) : void
    {
        unset($this->data[$key]);
    }

    /**
     * {@inheritdoc}
     */
    public function all() : array
    {
        return $this->data;
    }

    /**
     * {@inheritdoc}
     */
    public function flush() : void
    {
        $this->data = [];
    }
}
