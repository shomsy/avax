<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\HTTP\Session\Config\SessionConfig;
use Avax\HTTP\Session\Contracts\SessionContract;
use Avax\HTTP\Session\Contracts\SessionInterface;
use Avax\HTTP\Session\Contracts\Storage\Store;
use Avax\HTTP\Session\Data\FileStore;
use Avax\HTTP\Session\Data\Recovery;
use Avax\HTTP\Session\Features\{Audit, Events};
use Avax\HTTP\Session\Lifecycle\SessionProvider;
use Avax\HTTP\Session\Security\CookieManager;
use Avax\HTTP\Session\Security\EncrypterFactory;
use Avax\HTTP\Session\Security\Policies\{PolicyGroupBuilder, PolicyInterface};
use Avax\HTTP\Session\Security\SessionRegistry;
use Avax\HTTP\Session\Security\SessionSignature;
use Avax\HTTP\Session\Session;

/**
 * 🧩 SessionServiceProvider
 * ------------------------------------------------------------
 * Central registration for all Session Component dependencies.
 *
 * Integrates secure storage, encryption, recovery, registry,
 * auditing, events, and security policies into a single DI service.
 *
 * 💡 DI-friendly — this provider wires all dependencies, but
 * does not rely on lifecycle hooks like afterResolving().
 */
final class SessionServiceProvider extends ServiceProvider
{
    /**
     * Register all session-related dependencies.
     *
     * @return void
     */
    public function register() : void
    {
        // -----------------------------------------------------
        // ⚙️ Configuration (dynamic from ENV or fallback)
        // -----------------------------------------------------
        $storagePath  = $_ENV['SESSION_STORAGE_PATH'] ?? base_path(path: 'storage/sessions');
        $auditLogPath = $_ENV['SESSION_AUDIT_LOG_PATH'] ?? base_path(path: 'storage/logs/session_audit.log');
        $ttl          = (int) ($_ENV['SESSION_TTL'] ?? 3600);
        $secure       = filter_var(value: $_ENV['SESSION_SECURE'] ?? true, filter: FILTER_VALIDATE_BOOL);

        // -----------------------------------------------------
        // 🧱 Storage & Filesystem
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: LocalFileStorage::class,
            concrete: static fn() => new LocalFileStorage()
        );

        $this->dependencyInjector->singleton(
            abstract: Store::class,
            concrete: fn() => new FileStore(
                storage  : $this->dependencyInjector->get(id: LocalFileStorage::class),
                directory: $storagePath
            )
        );

        // -----------------------------------------------------
        // 🔐 Security Layer
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: CookieManager::class,
            concrete: static fn() => CookieManager::strict()
        );

        $this->dependencyInjector->singleton(
            abstract: EncrypterFactory::class,
            concrete: static fn() => new EncrypterFactory()
        );

        $this->dependencyInjector->singleton(
            abstract: SessionSignature::class,
            concrete: static fn() => new SessionSignature(
                secretKey: $_ENV['SESSION_SIGNATURE_KEY'] ?? 'default-signature-key'
            )
        );

        // -----------------------------------------------------
        // ⚙️ Configuration & Policies
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: SessionConfig::class,
            concrete: static fn() => new SessionConfig(
                ttl   : $ttl,
                secure: $secure
            )
        );

        $this->dependencyInjector->singleton(
            abstract: PolicyInterface::class,
            concrete: static fn() => PolicyGroupBuilder::create()
                ->requireAll(name: 'default_policy')
                ->maxLifetime(seconds: 7200)
                ->maxIdle(seconds: 900)
                ->secureOnly()
                ->endGroup()
                ->build()
        );

        // -----------------------------------------------------
        // 🧠 Recovery & Registry
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Recovery::class,
            concrete: fn() => new Recovery(
                store: $this->dependencyInjector->get(id: Store::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: SessionRegistry::class,
            concrete: fn() => new SessionRegistry(
                store: $this->dependencyInjector->get(id: Store::class)
            )
        );

        // -----------------------------------------------------
        // 🪶 Observability (Audit + Events)
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Audit::class,
            concrete: static fn() => new Audit(
                logPath: $auditLogPath
            )
        );

        $this->dependencyInjector->singleton(
            abstract: Events::class,
            concrete: static fn() => new Events()
        );

        // -----------------------------------------------------
        // 🧩 Core Session Provider
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: SessionProvider::class,
            concrete: fn() => $this->createSessionProvider()
        );

        // -----------------------------------------------------
        // 🪶 Contracts / Aliases
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: SessionInterface::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionProvider::class)
        );

        $this->dependencyInjector->singleton(
            abstract: SessionContract::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionProvider::class)
        );

        // -----------------------------------------------------
        // 🎯 Register High-Level Session API (Facade Layer)
        // -----------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Session::class,
            concrete: fn() => new Session(
                provider: $this->dependencyInjector->get(id: SessionProvider::class)
            )
        );
    }

    /**
     * Factory method for creating a fully wired SessionProvider instance.
     *
     * @return SessionProvider
     */
    private function createSessionProvider() : SessionProvider
    {
        $provider = new SessionProvider(
            store    : $this->dependencyInjector->get(id: Store::class),
            config   : $this->dependencyInjector->get(id: SessionConfig::class),
            encrypter: $this->dependencyInjector->get(id: EncrypterFactory::class)->create(),
            recovery : $this->dependencyInjector->get(id: Recovery::class),
            signature: $this->dependencyInjector->get(id: SessionSignature::class),
            policies : $this->dependencyInjector->get(id: PolicyInterface::class),
            registry : $this->dependencyInjector->get(id: SessionRegistry::class)
        );

        // Manually attach optional features (Audit + Events)
        $provider->registerFeature(feature: $this->dependencyInjector->get(id: Audit::class));
        $provider->registerFeature(feature: $this->dependencyInjector->get(id: Events::class));

        return $provider;
    }

    /**
     * Boot method (no-op for this provider).
     *
     * @return void
     */
    public function boot() : void {}
}
