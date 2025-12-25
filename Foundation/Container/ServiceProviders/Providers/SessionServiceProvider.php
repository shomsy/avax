<?php

declare(strict_types=1);

namespace Avax\Container\ServiceProviders\Providers;

use Avax\Container\ServiceProviders\ServiceProvider;
use Avax\Filesystem\Storage\LocalFileStorage;
use Avax\HTTP\Session\Audit\Audit;
use Avax\HTTP\Session\Audit\AuditManager;
use Avax\HTTP\Session\Core\Config;
use Avax\HTTP\Session\Core\CoreManager;
use Avax\HTTP\Session\Core\Lifecycle\SessionEngine;
use Avax\HTTP\Session\Core\Storage\FileStore;
use Avax\HTTP\Session\Events\Events;
use Avax\HTTP\Session\Events\EventsManager;
use Avax\HTTP\Session\Recovery\Recovery;
use Avax\HTTP\Session\Recovery\RecoveryManager;
use Avax\HTTP\Session\Session;
use Avax\HTTP\Session\Shared\Contracts\Security\SessionIdProviderInterface;
use Avax\HTTP\Session\Shared\Contracts\SessionContract;
use Avax\HTTP\Session\Shared\Contracts\SessionInterface;
use Avax\HTTP\Session\Shared\Contracts\Storage\StoreInterface;
use Avax\HTTP\Session\Shared\Security\CookieManager;
use Avax\HTTP\Session\Shared\Security\EncrypterFactory;
use Avax\HTTP\Session\Shared\Security\NativeSessionIdProvider;
use Avax\HTTP\Session\Shared\Security\Policies\PolicyGroupBuilder;
use Avax\HTTP\Session\Shared\Security\Policies\PolicyInterface;
use Avax\HTTP\Session\Shared\Security\SessionRegistry;
use Avax\HTTP\Session\Shared\Security\SessionSignature;
use Random\RandomException;


/**
 * SessionServiceProvider
 * ======================================================================
 * Absolute enterprise-grade dependency bootstrap for the entire Session
 * subsystem. Ensures deterministic, immutable and fully auditable wiring:
 *
 * 1. Storage         (FileStore / RedisStore)
 * 2. Security        (Signature, Policies, Cookie Rules)
 * 3. Recovery        (Snapshots & Transactions)
 * 4. Registry        (Multi-device session tracking)
 * 5. Features        (Audit, Events)
 * 6. Engine          (SessionEngine)
 * 7. Managers        (Core/Audit/Events/Recovery)
 * 8. Facade          (Session)
 *
 * Philosophy:
 * - Pure DI (no runtime registration or mutation)
 * - Immutable policies and security config
 * - Predictable lifecycle & consistent injection
 * - Zero side-effects in constructors
 * - Enterprise observability and security isolation
 */
final class SessionServiceProvider extends ServiceProvider
{
    /**
     * @throws RandomException
     */
    #[\Override]
    public function register() : void
    {
        // -----------------------------------------------------------------
        // CONFIGURATION
        // -----------------------------------------------------------------
        $storagePath  = $_ENV['SESSION_STORAGE_PATH'] ?? base_path(path: 'storage/sessions');
        $auditLogPath = $_ENV['SESSION_AUDIT_LOG_PATH'] ?? base_path(path: 'storage/logs/session_audit.log');
        $ttl          = (int) ($_ENV['SESSION_TTL'] ?? 3600);
        $secure       = filter_var(value: $_ENV['SESSION_SECURE'] ?? true, filter: FILTER_VALIDATE_BOOL);

        // Core signature key (separate from encryption key)
        $signatureKey = $_ENV['SESSION_SIGNATURE_KEY'] ?? bin2hex(string: random_bytes(length: 32));

        // -----------------------------------------------------------------
        // STORAGE
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: LocalFileStorage::class,
            concrete: static fn() => new LocalFileStorage()
        );

        $this->dependencyInjector->singleton(
            abstract: StoreInterface::class,
            concrete: fn() => new FileStore(
                storage  : $this->dependencyInjector->get(id: LocalFileStorage::class),
                directory: $storagePath
            )
        );

        // -----------------------------------------------------------------
        // SECURITY
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: CookieManager::class,
            concrete: static fn() => CookieManager::strict()
        );

        $this->dependencyInjector->singleton(
            abstract: EncrypterFactory::class,
            concrete: static fn() => new EncrypterFactory()
        );

        // Signature key is **standalone**, not tied to encryption
        $this->dependencyInjector->singleton(
            abstract: SessionSignature::class,
            concrete: static fn() => new SessionSignature(secretKey: $signatureKey)
        );

        $this->dependencyInjector->singleton(
            abstract: SessionIdProviderInterface::class,
            concrete: static fn() => new NativeSessionIdProvider()
        );

        // -----------------------------------------------------------------
        // CONFIG & POLICIES (immutable)
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Config::class,
            concrete: static fn() => new Config(ttl: $ttl, secure: $secure)
        );

        $this->dependencyInjector->singleton(
            abstract: PolicyInterface::class,
            concrete: static fn() => PolicyGroupBuilder::create()
                ->requireAll(name: 'default_security_policy')
                ->maxLifetime(seconds: 7200) // 2h
                ->maxIdle(seconds: 900)      // 15 min
                ->secureOnly()
                ->endGroup()
                ->build()
        );

        // -----------------------------------------------------------------
        // RECOVERY & REGISTRY
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Recovery::class,
            concrete: fn() => new Recovery(
                store: $this->dependencyInjector->get(id: StoreInterface::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: SessionRegistry::class,
            concrete: fn() => new SessionRegistry(
                store: $this->dependencyInjector->get(id: StoreInterface::class)
            )
        );

        // -----------------------------------------------------------------
        // FEATURES: AUDIT & EVENTS
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Audit::class,
            concrete: static fn() => new Audit(logPath: $auditLogPath)
        );

        $this->dependencyInjector->singleton(
            abstract: Events::class,
            concrete: static fn() => new Events()
        );

        // -----------------------------------------------------------------
        // ENGINE
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: SessionEngine::class,
            concrete: fn() => $this->createSessionEngine()
        );

        // -----------------------------------------------------------------
        // MANAGERS (consistent: all receive engine)
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: CoreManager::class,
            concrete: fn() => new CoreManager(
                engine: $this->dependencyInjector->get(id: SessionEngine::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: RecoveryManager::class,
            concrete: fn() => new RecoveryManager(
                recovery: $this->dependencyInjector->get(id: Recovery::class),
                audit   : $this->dependencyInjector->get(id: Audit::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: AuditManager::class,
            concrete: fn() => new AuditManager(
                audit: $this->dependencyInjector->get(id: Audit::class)
            )
        );

        $this->dependencyInjector->singleton(
            abstract: EventsManager::class,
            concrete: fn() => new EventsManager(
                events         : $this->dependencyInjector->get(id: Events::class),
                asyncDispatcher: null
            )
        );

        // -----------------------------------------------------------------
        // CONTRACTS → ENGINE
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: SessionInterface::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionEngine::class)
        );

        $this->dependencyInjector->singleton(
            abstract: SessionContract::class,
            concrete: fn() => $this->dependencyInjector->get(id: SessionEngine::class)
        );

        // -----------------------------------------------------------------
        // HIGH-LEVEL FACADE
        // -----------------------------------------------------------------
        $this->dependencyInjector->singleton(
            abstract: Session::class,
            concrete: fn() => new Session(
                core    : $this->dependencyInjector->get(id: CoreManager::class),
                recovery: $this->dependencyInjector->get(id: RecoveryManager::class),
                audit   : $this->dependencyInjector->get(id: AuditManager::class),
                events  : $this->dependencyInjector->get(id: EventsManager::class)
            )
        );
    }


    /**
     * Build a fully-initialized, immutable SessionEngine.
     */
    private function createSessionEngine() : SessionEngine
    {
        return new SessionEngine(
            store     : $this->dependencyInjector->get(id: StoreInterface::class),
            config    : $this->dependencyInjector->get(id: Config::class),
            encrypter : $this->dependencyInjector->get(id: EncrypterFactory::class)->create(),
            recovery  : $this->dependencyInjector->get(id: Recovery::class),
            idProvider: $this->dependencyInjector->get(id: SessionIdProviderInterface::class),
            audit     : $this->dependencyInjector->get(id: Audit::class),
            events    : $this->dependencyInjector->get(id: Events::class),
            signature : $this->dependencyInjector->get(id: SessionSignature::class),
            policies  : $this->dependencyInjector->get(id: PolicyInterface::class),
            registry  : $this->dependencyInjector->get(id: SessionRegistry::class)
        );
    }

    #[\Override]
    public function boot() : void {}
}
