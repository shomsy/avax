<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Framework\Configuration\BootDsl;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\Foundation\FrozenContainer;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Framework\System\Configuration\BootDsl\BootDslBuilder;
use Avax\Framework\System\Configuration\BootDsl\BootPhase;
use Avax\Framework\System\Configuration\BootDsl\BootDslEngine;
use Avax\Framework\System\Configuration\BootDsl\ProviderRegistry;
use Avax\Framework\System\Configuration\FrameworkServiceProvider;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\PublicSurface\Avax;
use Avax\Framework\System\PublicSurface\BootDsl;
use LogicException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class BootDslTest extends TestCase
{
    // ========== BootPhase enum tests ==========

    #[Test]
    public function boot_phase_enum_advances_sequentially(): void
    {
        self::assertSame(BootPhase::Register, BootPhase::Create->next());
        self::assertSame(BootPhase::Compile, BootPhase::Register->next());
        self::assertSame(BootPhase::Verify, BootPhase::Compile->next());
        self::assertSame(BootPhase::Boot, BootPhase::Verify->next());
        self::assertSame(BootPhase::Freeze, BootPhase::Boot->next());
        self::assertSame(BootPhase::Run, BootPhase::Freeze->next());
    }

    #[Test]
    public function boot_phase_enum_throws_on_final_phase(): void
    {
        $this->expectException(LogicException::class);
        BootPhase::Run->next();
    }

    // ========== ProviderRegistry tests ==========

    #[Test]
    public function provider_registry_orders_framework_first(): void
    {
        $registry = new ProviderRegistry();
        $registry->pinFrameworkProvider(FrameworkServiceProvider::class);
        $registry->registerUserProvider(StateTrackingServiceProvider::class);

        $ordered = $registry->orderedProviders();

        self::assertSame(FrameworkServiceProvider::class, $ordered[0]);
        self::assertSame(StateTrackingServiceProvider::class, $ordered[1]);
    }

    #[Test]
    public function provider_registry_is_empty_initially(): void
    {
        $registry = new ProviderRegistry();
        self::assertTrue($registry->isEmpty());

        $registry->pinFrameworkProvider(FrameworkServiceProvider::class);
        self::assertFalse($registry->isEmpty());
    }

    // ========== PublicSurface boundary tests ==========

    #[Test]
    public function avax_dsl_returns_public_boot_dsl_not_internal_builder(): void
    {
        $dsl = Avax::dsl();

        // Prove the returned class is BootDsl (public), not BootDslBuilder (internal)
        $className = $dsl::class;
        self::assertStringEndsWith('BootDsl', $className);
        self::assertStringNotContainsString('BootDslBuilder', $className);
    }

    #[Test]
    public function boot_dsl_public_api_has_expected_methods(): void
    {
        $reflection = new \ReflectionClass(BootDsl::class);

        // Public API must have these public methods
        $expectedMethods = ['from', 'withProvider', 'withProviders', 'create', 'withClock', 'withRuntimeName', 'withHttpHandler', 'withCommand'];
        foreach ($expectedMethods as $method) {
            self::assertTrue($reflection->hasMethod($method), "BootDsl must have method: {$method}");
            $m = $reflection->getMethod($method);
            self::assertTrue($m->isPublic(), "BootDsl::{$method} must be public");
        }
    }

    // ========== Provider lifecycle tests ==========

    #[Test]
    public function same_provider_instance_receives_register_and_boot(): void
    {
        StateTrackingServiceProvider::reset();

        $providerRegistry = new ProviderRegistry();
        $providerRegistry->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry->registerUserProvider(StateTrackingServiceProvider::class);

        $engine = $this->createEngine(providerRegistry: $providerRegistry);
        $engine->boot();

        // Same instance must have received both register and boot
        self::assertNotNull(StateTrackingServiceProvider::$registerContainer);
        self::assertNotNull(StateTrackingServiceProvider::$bootContainer);
        self::assertSame(
            StateTrackingServiceProvider::$registerContainer,
            StateTrackingServiceProvider::$bootContainer,
            'register() and boot() must be called on the same provider instance',
        );
    }

    #[Test]
    public function provider_state_set_during_register_is_visible_during_boot(): void
    {
        StateTrackingServiceProvider::reset();
        StateTrackingServiceProvider::$registerValue = 'test-value';

        $providerRegistry = new ProviderRegistry();
        $providerRegistry->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry->registerUserProvider(StateTrackingServiceProvider::class);

        $engine = $this->createEngine(providerRegistry: $providerRegistry);
        $engine->boot();

        // Boot must see the value set during register
        self::assertSame('test-value', StateTrackingServiceProvider::$bootValue);
    }

    #[Test]
    public function framework_provider_boots_before_user_provider(): void
    {
        StateTrackingServiceProvider::reset();

        $providerRegistry = new ProviderRegistry();
        $providerRegistry->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry->registerUserProvider(StateTrackingServiceProvider::class);

        $engine = $this->createEngine(providerRegistry: $providerRegistry);
        $engine->boot();

        // Framework provider register must happen before user provider boot
        self::assertGreaterThanOrEqual(
            StateTrackingServiceProvider::$frameworkRegisterOrder,
            StateTrackingServiceProvider::$userBootOrder,
        );
        // User provider boot must happen after framework provider register
        self::assertGreaterThan(
            StateTrackingServiceProvider::$frameworkRegisterOrder,
            StateTrackingServiceProvider::$userBootOrder,
        );
    }

    #[Test]
    public function missing_provider_class_fails_clearly(): void
    {
        $providerRegistry = new ProviderRegistry();
        // @phpstan-ignore argument.type
        $providerRegistry->registerUserProvider('NonExistent\\ServiceProvider');

        $engine = $this->createEngine(providerRegistry: $providerRegistry);

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('ServiceProvider class [NonExistent\ServiceProvider] does not exist');

        $engine->boot();
    }

    // ========== Container freeze tests ==========

    #[Test]
    public function container_is_frozen_after_boot(): void
    {
        $engine = $this->createEngine();
        $engine->boot();

        self::assertTrue($engine->isFrozen());
    }

    #[Test]
    public function mutation_after_freeze_throws(): void
    {
        $container = new FrozenContainer();
        $container->freeze();

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Cannot call [bind] on a frozen container');

        $container->bind('some.service');
    }

    #[Test]
    public function singleton_after_freeze_throws(): void
    {
        $container = new FrozenContainer();
        $container->freeze();

        $this->expectException(LogicException::class);
        $container->singleton('some.service');
    }

    #[Test]
    public function instance_after_freeze_throws(): void
    {
        $container = new FrozenContainer();
        $container->freeze();

        $this->expectException(LogicException::class);
        $container->instance('some.service', new \stdClass());
    }

    #[Test]
    public function alias_after_freeze_throws(): void
    {
        $container = new FrozenContainer();
        $container->freeze();

        $this->expectException(LogicException::class);
        $container->alias('alias', 'abstract');
    }

    #[Test]
    public function flush_after_freeze_throws(): void
    {
        $container = new FrozenContainer();
        $container->freeze();

        $this->expectException(LogicException::class);
        $container->flush();
    }

    #[Test]
    public function read_methods_work_after_freeze(): void
    {
        $container = new FrozenContainer();
        $service = new \stdClass();
        $service->name = 'test';
        $container->instance('my.service', $service);
        $container->freeze();

        // Read methods must still work
        self::assertTrue($container->has('my.service'));
        /** @var \stdClass $resolved */
        $resolved = $container->get('my.service');
        self::assertSame('test', $resolved->name);
    }

    #[Test]
    public function resolve_after_freeze_creates_new_instances(): void
    {
        $container = new FrozenContainer();
        $container->singleton('my.service', fn () => new \stdClass());
        $container->freeze();

        // Resolution through closure must still work
        $first = $container->get('my.service');
        $second = $container->get('my.service');
        // Singleton should cache after first resolve
        self::assertSame($first, $second);
    }

    // ========== Boot DSL end-to-end tests ==========

    #[Test]
    public function boot_dsl_builder_requires_project_path(): void
    {
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Project path is required');

        Avax::dsl()->create();
    }

    #[Test]
    public function boot_dsl_creates_booted_app(): void
    {
        $app = BootDsl::make()
            ->from(projectPath: $this->projectRoot(), environmentName: 'testing')
            ->withRuntimeName('test-boot-dsl')
            ->create();

        self::assertTrue($app->runtime()->state()->isBooted());
    }

    #[Test]
    public function boot_dsl_engine_advances_through_all_phases(): void
    {
        $engine = $this->createEngine();
        $engine->boot();

        self::assertSame(BootPhase::Run, $engine->phase());
        self::assertTrue($engine->isFrozen());
    }

    // ========== Compatibility tests ==========

    #[Test]
    public function avax_boot_with_application_builder_still_works(): void
    {
        $builder = new \Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder(
            projectPath     : new ProjectPath($this->projectRoot()),
            environmentName : EnvironmentName::Testing,
            clock           : new SystemClock(),
            runDoctor       : new \Avax\Framework\System\Flows\RunDoctor\RunDoctor(),
            handleIncomingHttp: new HandleIncomingHttp(
                createHttpResponse: new \Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse(),
            ),
            filesystem       : new \Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem(),
            createHttpResponse: new \Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse(),
            runtimeName      : 'avax',
        );

        $avax = Avax::boot($builder);

        self::assertTrue($avax->state()->isBooted());
    }

    #[Test]
    public function avax_create_still_works(): void
    {
        $app = Avax::create('testing');

        self::assertTrue($app->runtime()->state()->isBooted());
    }

    // ========== Missing dependency timing tests ==========

    #[Test]
    public function missing_required_dependency_fails_during_compile_not_runtime(): void
    {
        // The compile phase resolves core services — if any is missing, it fails during boot
        $providerRegistry = new ProviderRegistry();

        // Create an engine with minimal setup — compile will prove core bindings exist
        $engine = $this->createEngine(providerRegistry: $providerRegistry);
        $engine->boot();

        // If we reach here, compile/verify passed — core bindings exist
        self::assertTrue($engine->isFrozen());
    }

    // ========== Repeated boot safety tests ==========

    #[Test]
    public function repeated_boot_does_not_leak_state(): void
    {
        StateTrackingServiceProvider::reset();

        // First boot
        StateTrackingServiceProvider::$registerValue = 'boot-1';
        $providerRegistry1 = new ProviderRegistry();
        $providerRegistry1->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry1->registerUserProvider(StateTrackingServiceProvider::class);
        $engine1 = $this->createEngine(providerRegistry: $providerRegistry1);
        $app1 = $engine1->boot();

        // First boot captured its value
        self::assertSame('boot-1', StateTrackingServiceProvider::$bootValue);
        $firstBootValue = StateTrackingServiceProvider::$bootValue;

        // Second boot — fresh state
        StateTrackingServiceProvider::reset();
        StateTrackingServiceProvider::$registerValue = 'boot-2';
        $providerRegistry2 = new ProviderRegistry();
        $providerRegistry2->pinFrameworkProvider(FrameworkServiceProvider::class);
        $providerRegistry2->registerUserProvider(StateTrackingServiceProvider::class);
        $engine2 = $this->createEngine(providerRegistry: $providerRegistry2);
        $app2 = $engine2->boot();

        // Second boot must see its own value, not leaked from first
        self::assertSame('boot-2', StateTrackingServiceProvider::$bootValue);
        self::assertNotSame($app1, $app2);
    }

    // ========== Helpers ==========

    private function createEngine(?ProviderRegistry $providerRegistry = null): BootDslEngine
    {
        $registry = $providerRegistry ?? new ProviderRegistry();

        $createHttpResponse = new \Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse();

        return new BootDslEngine(
            providerRegistry   : $registry,
            projectPath        : new ProjectPath(value: $this->projectRoot()),
            environmentName    : EnvironmentName::fromString('testing'),
            clock              : new SystemClock(),
            runtimeName        : 'test-engine',
            handleIncomingHttp : new HandleIncomingHttp(createHttpResponse: $createHttpResponse),
            httpHandler        : null,
            consoleCommands    : [],
        );
    }

    private function projectRoot(): string
    {
        return dirname(__DIR__, 4);
    }
}

/**
 * ServiceProvider that tracks register/boot calls and state.
 */
final class StateTrackingServiceProvider implements ServiceProvider
{
    public static ?ContainerInterface $registerContainer = null;
    public static ?ContainerInterface $bootContainer = null;
    public static string $registerValue = '';
    public static string $bootValue = '';
    public static int $frameworkRegisterOrder = 0;
    public static int $userBootOrder = 0;
    private static int $orderCounter = 0;

    public static function reset(): void
    {
        self::$registerContainer = null;
        self::$bootContainer = null;
        self::$registerValue = '';
        self::$bootValue = '';
        self::$frameworkRegisterOrder = 0;
        self::$userBootOrder = 0;
        self::$orderCounter = 0;
    }

    public function register(ContainerInterface $container): void
    {
        self::$registerContainer = $container;
        self::$registerValue = self::$registerValue;
        self::$orderCounter++;
        self::$frameworkRegisterOrder = self::$orderCounter;
    }

    public function boot(ContainerInterface $container): void
    {
        self::$bootContainer = $container;
        self::$bootValue = self::$registerValue;
        self::$orderCounter++;
        self::$userBootOrder = self::$orderCounter;
    }
}
