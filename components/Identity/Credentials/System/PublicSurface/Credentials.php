<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\PublicSurface;

use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\InMemoryCredentialStore;

/**
 * Credentials — manages user credential data.
 *
 * @deprecated Inject CredentialStoreInterface directly instead of using this static facade.
 *             Use CredentialsGraph for assembly. This class remains for backward compatibility
 *             but the static store state is no longer owned directly by this class.
 */
final class Credentials
{
    private static ?CredentialStoreInterface $store = null;

    /**
     * Replace the backing store (for DI integration).
     */
    public static function setStore(CredentialStoreInterface $store) : void
    {
        self::$store = $store;
    }

    /**
     * @param array<string, mixed> $credentials
     */
    public static function store(string $userId, array $credentials) : void
    {
        self::resolveStore()->store($userId, $credentials);
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function read(string $userId) : array|null
    {
        return self::resolveStore()->read($userId);
    }

    public static function forget(string $userId) : void
    {
        self::resolveStore()->forget($userId);
    }

    private static function resolveStore() : CredentialStoreInterface
    {
        if (self::$store === null) {
            self::$store = new InMemoryCredentialStore();
        }

        return self::$store;
    }

    /**
     * Reset static state for long-lived worker safety.
     * MUST be called between requests in persistent runtimes.
     */
    public static function reset() : void
    {
        self::$store = null;
    }
}
