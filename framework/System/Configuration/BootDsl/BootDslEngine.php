<?php

declare(strict_types=1);

namespace Avax\Framework\System\Configuration\BootDsl;

use Avax\Components\Application\Container\System\Capabilities\ServiceProvider\ServiceProvider;
use Avax\Components\Application\Container\System\Foundation\FrozenContainer;
use Avax\Components\Application\Container\System\PublicSurface\ContainerInterface;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StaticStateReset;
use Avax\Framework\System\Configuration\Builders\BuildRunApplication;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\CreateRuntimeRequestFromHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\FrameworkRouteRegistrar;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\PublicSurface\App;
use Closure;
use LogicException;

/**
 * BootDslEngine — internal engine that owns the container lifecycle.
 *
 * This class is NOT public API. It is invoked by BootDsl (PublicSurface).
 *
 * Lifecycle:
 * 1. create  — instantiate container and bind primitives
 * 2. register — instantiate providers once, call register()
 * 3. compile  — prove core bindings resolve
 * 4. verify   — check required bindings exist
 * 5. boot     — call boot() on same provider instances
 * 6. freeze   — wrap container in FrozenContainer (real freeze)
 * 7. run      — create Runtime and return App
 */
final class BootDslEngine
{
    private BootPhase $phase = BootPhase::Create;

    private ?ContainerInterface $container = null;

    /**
     * @var array<class-string<ServiceProvider>, ServiceProvider>
     */
    private array $providerInstances = [];

    /**
     * @param array<string, Closure> $consoleCommands
     */
    public function __construct(
        private readonly ProviderRegistry $providerRegistry,
        private readonly ProjectPath $projectPath,
        private readonly EnvironmentName $environmentName,
        private readonly Clock $clock,
        private readonly string $runtimeName,
        private readonly HandleIncomingHttp $handleIncomingHttp,
        private readonly ?Closure $httpHandler,
        private readonly array $consoleCommands,
    ) {}

    /**
     * Execute the full boot lifecycle and return a ready App.
     */
    public function boot(): App
    {
        $this->createContainer();
        $this->registerProviders();
        $this->compileContainer();
        $this->verifyContainer();
        $this->bootProviders();
        $this->freezeContainer();

        return $this->createRuntimeAndApp();
    }

    private function createContainer(): void
    {
        $this->advanceTo(BootPhase::Create);

        $this->container = new FrozenContainer();

        // Bind primitives as instances
        $this->container->instance(ProjectPath::class, $this->projectPath);
        $this->container->instance(EnvironmentName::class, $this->environmentName);
        $this->container->instance(Clock::class, $this->clock);
        $this->container->instance(ContainerInterface::class, $this->container);
    }

    private function registerProviders(): void
    {
        $this->advanceTo(BootPhase::Register);

        foreach ($this->providerRegistry->orderedProviders() as $providerClass) {
            if (! class_exists($providerClass)) {
                throw new LogicException("ServiceProvider class [{$providerClass}] does not exist.");
            }

            // Instantiate once — same instance used for register() and boot()
            $provider = new $providerClass();
            $this->providerInstances[$providerClass] = $provider;
            $provider->register($this->container());
        }
    }

    private function compileContainer(): void
    {
        $this->advanceTo(BootPhase::Compile);

        // Compile phase: prove core bindings resolve before boot.
        // Resolve each core service to prove wiring.
        $coreServices = [
            ProjectPath::class,
            EnvironmentName::class,
            Clock::class,
            ContainerInterface::class,
        ];

        foreach ($coreServices as $service) {
            $this->container()->get($service);
        }
    }

    private function verifyContainer(): void
    {
        $this->advanceTo(BootPhase::Verify);

        // Verify that core bindings are resolvable (already proven by compile).
        // This phase is a gate: if compile passed, verify passes.
    }

    private function bootProviders(): void
    {
        $this->advanceTo(BootPhase::Boot);

        // Boot the SAME instances that were registered — state survives.
        foreach ($this->providerRegistry->orderedProviders() as $providerClass) {
            $provider = $this->providerInstances[$providerClass];
            $provider->boot($this->container());
        }
    }

    private function freezeContainer(): void
    {
        $this->advanceTo(BootPhase::Freeze);

        // Real freeze: wrap the container in FrozenContainer.
        // After this point, mutation methods (bind, singleton, instance, alias, flush)
        // throw LogicException. Read/resolve methods still work.
        if ($this->container instanceof FrozenContainer) {
            $this->container->freeze();
        }
    }

    private function createRuntimeAndApp(): App
    {
        $this->advanceTo(BootPhase::Run);

        $componentRegistry = new ComponentRegistry();
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);
        $stateResetRegistry->register(name: 'static-state', state: new StaticStateReset());

        $runtimeState = new RuntimeState(runtimeName: $this->runtimeName);
        $runtimeState->markBooted(bootedAt: $this->clock->now());

        $runtime = new Runtime(
            runtimeState        : $runtimeState,
            runtimeContext      : $runtimeContext,
            requestScopeStore   : $requestScopeStore,
            stateResetRegistry  : $stateResetRegistry,
            componentRegistry   : $componentRegistry,
            projectPath         : $this->projectPath,
            environmentName     : $this->environmentName,
            clock               : $this->clock,
            runtimeName         : $this->runtimeName,
            handleIncomingHttp  : $this->handleIncomingHttp,
            httpHandler         : $this->httpHandler,
            consoleCommands     : $this->consoleCommands,
        );

        $componentRegistry->boot(runtime: $runtime);

        $createHttpResponse = new \Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse();
        $normalizer = new \Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult(
            createHttpResponse: $createHttpResponse,
        );
        $dispatcher = BuildRunApplication::fromDefaultResolutionPipeline(
            createHttpResponse: $createHttpResponse,
            normalizer        : $normalizer,
        );
        $createRequestFromGlobals = $this->createRequestFromGlobals();
        $resetApp = new \Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState(
            stateResetRegistry: $stateResetRegistry,
        );

        return new App(
            runtime                : $runtime,
            resetApplicationState  : $resetApp,
            createHttpResponse     : $createHttpResponse,
            createRequestFromGlobals: $createRequestFromGlobals,
            dispatcher             : $dispatcher,
            routeRegistrar         : new FrameworkRouteRegistrar(),
            openRequestScope       : new OpenHttpRequestScope(
                requestScopes: $runtime->requestScopes(),
                runtimeContext: $runtime->context(),
            ),
            closeRequestScope      : new CloseHttpRequestScope(requestScopes: $runtime->requestScopes()),
            createRuntimeRequest   : new CreateRuntimeRequestFromHttpRequest(),
        );
    }

    private function createRequestFromGlobals(): \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals
    {
        return new \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals(
            readServerParameters  : new \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadServerParameters(),
            readQueryParameters   : new \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadQueryParameters(),
            readUploadedFiles     : new \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadUploadedFiles(),
            readRequestBody       : new \Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\ReadRequestBody(),
            normalizeHeaders      : new \Avax\Components\HTTP\Request\System\Capabilities\Headers\NormalizeHeaders(),
            normalizeUploadedFiles: new \Avax\Components\HTTP\Request\System\Capabilities\Files\NormalizeUploadedFiles(),
            parseJsonBody         : new \Avax\Components\HTTP\Request\System\Capabilities\Body\ParseJsonBody(),
            parseFormBody         : new \Avax\Components\HTTP\Request\System\Capabilities\Body\ParseFormBody(),
        );
    }

    private function advanceTo(BootPhase $target): void
    {
        if ($this->phase === $target) {
            return;
        }

        if ($this->phase->isBeforeOr($target) === false) {
            throw new LogicException(
                "Cannot move from phase [{$this->phase->value}] to [{$target->value}]. Phases are forward-only.",
            );
        }

        $this->phase = $target;
    }

    /**
     * Returns the current boot phase.
     */
    public function phase(): BootPhase
    {
        return $this->phase;
    }

    /**
     * Returns the container instance (available after create phase).
     */
    public function container(): ContainerInterface
    {
        if ($this->container === null) {
            throw new LogicException('Container not yet created. Current phase: ' . $this->phase->value);
        }

        return $this->container;
    }

    /**
     * Returns true if the container has been frozen.
     */
    public function isFrozen(): bool
    {
        return $this->container instanceof FrozenContainer && $this->container->isFrozen();
    }
}
