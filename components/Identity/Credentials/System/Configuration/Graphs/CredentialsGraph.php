<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration\Graphs;

use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\CredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\CredentialStore\InMemoryCredentialStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\InMemoryAttemptLimitStorage;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Limit\LimitMfaAttempts;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Stores\MfaStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\Totp;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Totp\TotpInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Verify\MfaChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyChallengeStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\InMemoryPasskeyCredentialStore;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyChallengeStoreInterface;
use Avax\Components\Identity\Credentials\System\Capabilities\Passkey\PasskeyCredentialCeremony\PasskeyCredentialStoreInterface;
use Avax\Components\Identity\Credentials\System\Configuration\CredentialsConfiguration;

/**
 * CredentialsGraph — canonical DI registration for Credentials component infrastructure.
 *
 * Registers:
 * - MFA stores (InMemoryMfaStore, InMemoryMfaChallengeStore)
 * - TOTP implementation
 * - MFA attempt limiting
 * - Passkey stores (credential + challenge)
 * - Credential store
 * - CredentialsConfiguration
 *
 * The Credentials component is a capability provider — its classes are consumed
 * by Auth's IdentityAssembler. This graph ensures all infrastructure singletons
 * are registered before assembly.
 */
final class CredentialsGraph
{
    /**
     * Register all Credentials component dependencies in the container.
     */
    public static function register(ContainerInterface $container) : void
    {
        // === Configuration ===

        $container->singleton(
            CredentialsConfiguration::class,
            static fn () : CredentialsConfiguration => new CredentialsConfiguration(),
        );

        // === Credential Store ===

        $container->singleton(
            InMemoryCredentialStore::class,
            static fn () : InMemoryCredentialStore => new InMemoryCredentialStore(),
        );
        $container->singleton(
            CredentialStoreInterface::class,
            static fn (ContainerInterface $c) : CredentialStoreInterface => $c->get(InMemoryCredentialStore::class),
        );

        // === MFA Infrastructure ===

        $container->singleton(
            InMemoryMfaStore::class,
            static fn () : InMemoryMfaStore => new InMemoryMfaStore(),
        );
        $container->singleton(
            MfaStoreInterface::class,
            static fn (ContainerInterface $c) : MfaStoreInterface => $c->get(InMemoryMfaStore::class),
        );

        $container->singleton(
            InMemoryMfaChallengeStore::class,
            static fn () : InMemoryMfaChallengeStore => new InMemoryMfaChallengeStore(),
        );
        $container->singleton(
            MfaChallengeStoreInterface::class,
            static fn (ContainerInterface $c) : MfaChallengeStoreInterface => $c->get(InMemoryMfaChallengeStore::class),
        );

        $container->singleton(
            Totp::class,
            static fn () : Totp => new Totp(),
        );
        $container->singleton(
            TotpInterface::class,
            static fn (ContainerInterface $c) : TotpInterface => $c->get(Totp::class),
        );

        // MFA attempt limiting
        $container->singleton(
            InMemoryAttemptLimitStorage::class,
            static fn () : InMemoryAttemptLimitStorage => new InMemoryAttemptLimitStorage(),
        );
        $container->singleton(
            LimitMfaAttempts::class,
            static fn (ContainerInterface $c) : LimitMfaAttempts => new LimitMfaAttempts(
                attemptLimitStorage: $c->get(InMemoryAttemptLimitStorage::class),
                clock              : $c->get(\Avax\Components\Identity\Auth\System\Foundation\Clock::class),
            ),
        );

        // === Passkey Infrastructure ===

        $container->singleton(
            InMemoryPasskeyCredentialStore::class,
            static fn () : InMemoryPasskeyCredentialStore => new InMemoryPasskeyCredentialStore(),
        );
        $container->singleton(
            PasskeyCredentialStoreInterface::class,
            static fn (ContainerInterface $c) : PasskeyCredentialStoreInterface => $c->get(InMemoryPasskeyCredentialStore::class),
        );

        $container->singleton(
            InMemoryPasskeyChallengeStore::class,
            static fn () : InMemoryPasskeyChallengeStore => new InMemoryPasskeyChallengeStore(),
        );
        $container->singleton(
            PasskeyChallengeStoreInterface::class,
            static fn (ContainerInterface $c) : PasskeyChallengeStoreInterface => $c->get(InMemoryPasskeyChallengeStore::class),
        );
    }

    /**
     * Boot — reset static state for long-lived worker safety.
     */
    public static function boot() : void
    {
        \Avax\Components\Identity\Credentials\System\PublicSurface\Credentials::reset();
    }
}
