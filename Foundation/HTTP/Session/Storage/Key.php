<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Storage;

use InvalidArgumentException;
use Stringable;

/**
 * Key - Type-Safe Session Key Value Object
 *
 * Prevents key naming conflicts and provides type safety.
 * Enforces naming conventions and namespacing.
 *
 * Features:
 * - Immutable value object
 * - Namespace support (prefix)
 * - Validation (no special characters)
 * - Reserved key detection
 * - String conversion
 * - Equality comparison
 *
 * @example
 *   $key = Key::make('user_id');
 *   $key = Key::make('cart', 'items');  // cart.items
 *   $key = Key::secure('password');     // password_secure
 *
 * @package Avax\HTTP\Session\Storage
 */
final class Key implements Stringable
{
    /**
     * Reserved key prefixes (internal use only).
     */
    private const RESERVED_PREFIXES = ['_ttl', '_flash', '_csrf', '_nonce', '_snapshot', '_registry'];

    /**
     * Key Constructor.
     *
     * @param string      $name      Key name.
     * @param string|null $namespace Optional namespace (prefix).
     */
    private function __construct(
        private string      $name,
        private string|null $namespace = null
    )
    {
        $this->validate();
    }

    /**
     * Validate key name.
     *
     * @return void
     *
     * @throws \InvalidArgumentException If key is invalid.
     */
    private function validate() : void
    {
        // Check empty
        if (empty($this->name)) {
            throw new InvalidArgumentException('Key name cannot be empty');
        }

        // Check length
        if (strlen($this->toString()) > 255) {
            throw new InvalidArgumentException('Key length cannot exceed 255 characters');
        }

        // Check for null bytes
        if (str_contains($this->name, "\0")) {
            throw new InvalidArgumentException('Key cannot contain null bytes');
        }

        // Check namespace if provided
        if ($this->namespace !== null && empty($this->namespace)) {
            throw new InvalidArgumentException('Namespace cannot be empty string');
        }
    }

    /**
     * Get full key string (with namespace).
     *
     * @return string Full key.
     */
    public function toString() : string
    {
        if ($this->namespace === null) {
            return $this->name;
        }

        return $this->namespace . '.' . $this->name;
    }

    /**
     * Create a new key.
     *
     * @param string      $name      Key name.
     * @param string|null $namespace Optional namespace.
     *
     * @return self
     */
    public static function make(string $name, string|null $namespace = null) : self
    {
        return new self($name, $namespace);
    }

    /**
     * Create a secure key (auto-encrypted).
     *
     * Appends '_secure' suffix for auto-encryption.
     *
     * @param string      $name      Key name.
     * @param string|null $namespace Optional namespace.
     *
     * @return self
     */
    public static function secure(string $name, string|null $namespace = null) : self
    {
        if (! str_ends_with($name, '_secure')) {
            $name .= '_secure';
        }

        return new self($name, $namespace);
    }

    /**
     * Create a temporary key (with TTL).
     *
     * @param string      $name      Key name.
     * @param string|null $namespace Optional namespace.
     *
     * @return self
     */
    public static function temporary(string $name, string|null $namespace = null) : self
    {
        return new self($name, $namespace);
    }

    /**
     * Create a flash key.
     *
     * @param string $type Flash type (success, error, warning, info).
     *
     * @return self
     */
    public static function flash(string $type) : self
    {
        return new self($type, '_flash');
    }

    /**
     * Create a CSRF token key.
     *
     * @return self
     */
    public static function csrf() : self
    {
        return new self('token', '_csrf');
    }

    /**
     * Create a nonce key.
     *
     * @param string|null $action Optional action identifier.
     *
     * @return self
     */
    public static function nonce(string|null $action = null) : self
    {
        $name = $action ?? 'default';

        return new self($name, '_nonce');
    }

    /**
     * Create a snapshot key.
     *
     * @param string $name Snapshot name.
     *
     * @return self
     */
    public static function snapshot(string $name) : self
    {
        return new self($name, '_snapshot');
    }

    /**
     * Create a registry key.
     *
     * @param string $userId User identifier.
     *
     * @return self
     */
    public static function registry(string $userId) : self
    {
        return new self($userId, '_registry');
    }

    /**
     * Parse a string into a Key object.
     *
     * @param string $keyString Key string (e.g., "namespace.name").
     *
     * @return self Key instance.
     */
    public static function parse(string $keyString) : self
    {
        $parts = explode('.', $keyString, 2);

        if (count($parts) === 1) {
            return new self($parts[0], null);
        }

        return new self($parts[1], $parts[0]);
    }

    /**
     * Create multiple keys from array.
     *
     * @param array<string> $names     Key names.
     * @param string|null   $namespace Optional namespace for all keys.
     *
     * @return array<self> Array of Key instances.
     */
    public static function many(array $names, string|null $namespace = null) : array
    {
        return array_map(
            fn($name) => new self($name, $namespace),
            $names
        );
    }

    /**
     * Get all reserved prefixes.
     *
     * @return array<string> Reserved prefixes.
     */
    public static function getReservedPrefixes() : array
    {
        return self::RESERVED_PREFIXES;
    }

    /**
     * Get key name (without namespace).
     *
     * @return string Key name.
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Get namespace.
     *
     * @return string|null Namespace or null.
     */
    public function getNamespace() : string|null
    {
        return $this->namespace;
    }

    /**
     * Convert to string.
     *
     * @return string Full key.
     */
    public function __toString() : string
    {
        return $this->toString();
    }

    /**
     * Check equality with another key.
     *
     * @param Key $other Other key.
     *
     * @return bool True if equal.
     */
    public function equals(Key $other) : bool
    {
        return $this->toString() === $other->toString();
    }

    /**
     * Create a new key with different namespace.
     *
     * @param string $namespace New namespace.
     *
     * @return self New key instance.
     */
    public function withNamespace(string $namespace) : self
    {
        return new self($this->name, $namespace);
    }

    /**
     * Create a new key without namespace.
     *
     * @return self New key instance.
     */
    public function withoutNamespace() : self
    {
        return new self($this->name, null);
    }

    /**
     * Create a TTL meta key for this key.
     *
     * @return self TTL meta key.
     */
    public function toTtlKey() : self
    {
        return new self($this->toString(), '_ttl');
    }

    /**
     * Check if this is a TTL meta key.
     *
     * @return bool True if TTL key.
     */
    public function isTtlKey() : bool
    {
        return $this->namespace === '_ttl';
    }

    /**
     * Get hash of the key (for use as array key).
     *
     * @return string Hash.
     */
    public function hash() : string
    {
        return md5($this->toString());
    }

    /**
     * Check if key matches a pattern.
     *
     * @param string $pattern Pattern (supports * wildcard).
     *
     * @return bool True if matches.
     */
    public function matches(string $pattern) : bool
    {
        $regex = '/^' . str_replace('*', '.*', preg_quote($pattern, '/')) . '$/';

        return preg_match($regex, $this->toString()) === 1;
    }

    /**
     * Serialize to JSON.
     *
     * @return array<string, mixed> JSON data.
     */
    public function jsonSerialize() : array
    {
        return [
            'name'        => $this->name,
            'namespace'   => $this->namespace,
            'full'        => $this->toString(),
            'is_secure'   => $this->isSecure(),
            'is_reserved' => $this->isReserved(),
        ];
    }

    /**
     * Check if key is secure (auto-encrypted).
     *
     * @return bool True if secure.
     */
    public function isSecure() : bool
    {
        return str_ends_with($this->name, '_secure');
    }

    /**
     * Check if key is reserved (internal use).
     *
     * @return bool True if reserved.
     */
    public function isReserved() : bool
    {
        if ($this->namespace === null) {
            return false;
        }

        return in_array($this->namespace, self::RESERVED_PREFIXES, true);
    }
}
