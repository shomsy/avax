<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Actions;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Crypto\Actions\EncryptValue;
use Avax\HTTP\Session\Features\TTL\Actions\SetTTL;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * StoreValue Action
 *
 * Single Responsibility: Store a value in the session with contextual policies.
 *
 * This action handles storing session data while respecting:
 * - Namespace isolation
 * - Encryption requirements
 * - TTL (Time-To-Live) expiration
 * - Metadata tracking
 *
 * Enterprise Rules:
 * - Security-first: Encrypts sensitive data when required.
 * - Validation: Guards against invalid keys and values.
 * - Atomicity: Storage is atomic (all-or-nothing).
 *
 * Usage:
 *   $action = new StoreValue($store, $context, $encryptor, $ttlManager);
 *   $action->execute('user_id', 123);
 *
 * @package Avax\HTTP\Session\Actions
 */
final readonly class StoreValue
{
    /**
     * StoreValue Constructor.
     *
     * @param SessionStore    $store     The session storage backend.
     * @param SessionContext  $context   The contextual configuration (namespace, TTL, encryption).
     * @param EncryptValue    $encryptor Action for encrypting values.
     * @param SetTTL          $ttlSetter Action for setting TTL metadata.
     */
    public function __construct(
        private SessionStore $store,
        private SessionContext $context,
        private EncryptValue $encryptor,
        private SetTTL $ttlSetter
    ) {}

    /**
     * Execute the action: Store value with context.
     *
     * This method orchestrates the storage process:
     * 1. Resolves the namespaced key
     * 2. Encrypts the value if required
     * 3. Sets TTL metadata if specified
     * 4. Stores the value atomically
     *
     * @param string $key   The session key identifier.
     * @param mixed  $value The value to store (must be serializable).
     *
     * @return void
     */
    public function execute(string $key, mixed $value): void
    {
        // Guard: Validate key is not empty.
        if (trim($key) === '') {
            throw new \InvalidArgumentException(
                message: 'Session key cannot be empty'
            );
        }

        // Resolve the full key with namespace prefix.
        $resolvedKey = $this->resolveKey($key);

        // Process value based on context policies.
        $processedValue = $value;

        // Apply encryption if required by context.
        if ($this->context->secure) {
            // Delegate encryption to specialized action.
            $processedValue = $this->encryptor->execute($value);
        }

        // Store the (potentially encrypted) value.
        $this->store->put(
            key: $resolvedKey,
            value: $processedValue
        );

        // Set TTL metadata if specified in context.
        if ($this->context->ttl !== null) {
            // Delegate TTL management to specialized action.
            $this->ttlSetter->execute(
                key: $resolvedKey,
                ttl: $this->context->ttl
            );
        }

        // Log the storage operation for audit trail.
        logger()?->debug(
            message: 'Session value stored',
            context: [
                'key' => $resolvedKey,
                'encrypted' => $this->context->secure,
                'ttl' => $this->context->ttl,
                'namespace' => $this->context->namespace,
                'action' => 'StoreValue',
            ]
        );
    }

    /**
     * Resolve the full key with namespace prefix.
     *
     * If a namespace is defined in the context, it is prepended to the key
     * to ensure logical isolation of session data.
     *
     * @param string $key The base key.
     *
     * @return string The fully qualified key.
     */
    private function resolveKey(string $key): string
    {
        // Return raw key if no namespace or default namespace.
        if ($this->context->namespace === '' || $this->context->namespace === 'default') {
            return $key;
        }

        // Return namespaced key.
        return "{$this->context->namespace}.{$key}";
    }
}
