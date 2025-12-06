<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Actions;

use Avax\HTTP\Session\Core\SessionContext;
use Avax\HTTP\Session\Features\Crypto\Actions\DecryptValue;
use Avax\HTTP\Session\Features\TTL\Actions\CheckExpiration;
use Avax\HTTP\Session\Storage\SessionStore;

/**
 * RetrieveValue Action
 *
 * Single Responsibility: Retrieve a value from the session with contextual processing.
 *
 * This action handles retrieving session data while:
 * - Checking TTL expiration
 * - Decrypting encrypted values
 * - Resolving namespaced keys
 * - Providing default fallback values
 *
 * Enterprise Rules:
 * - Security: Automatically decrypts encrypted data.
 * - TTL Enforcement: Returns null for expired values.
 * - Fail-safe: Returns default value on decryption failure.
 *
 * Usage:
 *   $action = new RetrieveValue($store, $context, $decryptor, $ttlChecker);
 *   $value = $action->execute('user_id', default: null);
 *
 * @package Avax\HTTP\Session\Actions
 */
final readonly class RetrieveValue
{
    /**
     * RetrieveValue Constructor.
     *
     * @param SessionStore    $store      The session storage backend.
     * @param SessionContext  $context    The contextual configuration.
     * @param DecryptValue    $decryptor  Action for decrypting values.
     * @param CheckExpiration $ttlChecker Action for validating TTL.
     */
    public function __construct(
        private SessionStore $store,
        private SessionContext $context,
        private DecryptValue $decryptor,
        private CheckExpiration $ttlChecker
    ) {}

    /**
     * Execute the action: Retrieve value with context.
     *
     * This method orchestrates the retrieval process:
     * 1. Resolves the namespaced key
     * 2. Checks TTL expiration
     * 3. Retrieves the raw value
     * 4. Decrypts if necessary
     * 5. Returns value or default
     *
     * @param string $key     The session key identifier.
     * @param mixed  $default The fallback value if key doesn't exist or is expired.
     *
     * @return mixed The retrieved value or default.
     */
    public function execute(string $key, mixed $default = null): mixed
    {
        // Resolve the full key with namespace prefix.
        $resolvedKey = $this->resolveKey($key);

        // Check if value has expired (TTL validation).
        if ($this->ttlChecker->execute($resolvedKey)) {
            // Value has expired, return default.
            logger()?->debug(
                message: 'Session value expired',
                context: [
                    'key' => $resolvedKey,
                    'action' => 'RetrieveValue',
                ]
            );

            return $default;
        }

        // Retrieve the raw value from storage.
        $value = $this->store->get(
            key: $resolvedKey,
            default: $default
        );

        // If value is null or default, return immediately.
        if ($value === null || $value === $default) {
            return $default;
        }

        // Check if value needs decryption.
        $metaKey = "{$resolvedKey}::__meta";
        $meta = $this->store->get(key: $metaKey, default: []);

        // Decrypt if metadata indicates encryption.
        if (isset($meta['encrypted']) && $meta['encrypted'] === true) {
            try {
                // Delegate decryption to specialized action.
                $value = $this->decryptor->execute($value);
            } catch (\RuntimeException $e) {
                // Decryption failed (tampering or corruption).
                // Log security warning and return default.
                logger()?->warning(
                    message: 'Session value decryption failed',
                    context: [
                        'key' => $resolvedKey,
                        'error' => $e->getMessage(),
                        'action' => 'RetrieveValue',
                    ]
                );

                return $default;
            }
        }

        // Return the processed value.
        return $value;
    }

    /**
     * Resolve the full key with namespace prefix.
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
