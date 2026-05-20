<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\CreateApplication;

use Avax\Components\HTTP\Request\System\Flows\CreateRequestFromGlobals\CreateRequestFromGlobals;
use Avax\Components\HTTP\Response\System\Capabilities\CreateHttpResponse\CreateHttpResponse;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Capabilities\StateReset\StaticStateReset;
use Avax\Framework\System\Configuration\Builders\BuildRunApplication;
use Avax\Framework\System\Configuration\BuildApplication\Builders\ApplicationBuilder;
use Avax\Framework\System\Flows\HandleIncomingHttp\CloseHttpRequestScope;
use Avax\Framework\System\Flows\HandleIncomingHttp\CreateRuntimeRequestFromHttpRequest;
use Avax\Framework\System\Flows\HandleIncomingHttp\FrameworkRouteRegistrar;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\HandleIncomingHttp\OpenHttpRequestScope;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Avax\Framework\System\PublicSurface\App;

/**
 * CreateApplication — Zero-configuration application factory flow.
 *
 * Creates a tiny runnable App API over existing AvaX internals.
 * Wires Runtime, RequestScope, and state reset together
 * without requiring the user to manually configure ApplicationBuilder.
 *
 * This is a composition-root-level Flow: it assembles all dependencies
 * for the simplest "Avax::create()" DX path.
 *
 * The Container is not initialized globally here — route dispatch
 * uses RouteFacadeContainer (a minimal PSR-11 container) for
 * controller resolution. Full container integration comes in later V4 stages.
 */
final readonly class CreateApplication
{
    public function __construct(
        private Clock                   $clock,
        private ProjectPath             $projectPath,
        private ComponentRegistry       $componentRegistry,
        private RequestScopeStore       $requestScopeStore,
        private RuntimeContext          $runtimeContext,
        private StateResetRegistry      $stateResetRegistry,
        private CreateHttpResponse $createHttpResponse,
        private HandleIncomingHttp $handleIncomingHttp,
        private CreateRequestFromGlobals $createRequestFromGlobals,
    ) {}

    /**
     * Create a new App with default configuration.
     *
     * @param string $environment Environment name (e.g. 'production', 'local', 'testing')
     */
    public function make(string $environment = 'production'): App
    {
        $envName = EnvironmentName::fromString($environment);
        $clock = $this->clock;

        $componentRegistry = $this->componentRegistry;
        $requestScopeStore = $this->requestScopeStore;
        $runtimeContext = $this->runtimeContext;
        $stateResetRegistry = $this->stateResetRegistry;
        $runtimeState = new RuntimeState(runtimeName: 'avax');

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);
        $stateResetRegistry->register(name: 'static-state', state: new StaticStateReset());

        $runtimeState->markBooted(bootedAt: $clock->now());

        $createHttpResponse = $this->createHttpResponse;
        $normalizer = new NormalizeControllerResult(createHttpResponse: $createHttpResponse);
        $dispatcher = BuildRunApplication::fromDefaultResolutionPipeline(
            createHttpResponse: $createHttpResponse,
            normalizer        : $normalizer,
        );

        // Build runtime (no httpHandler — App manages routes directly)
        $runtime = new Runtime(
            runtimeState      : $runtimeState,
            runtimeContext    : $runtimeContext,
            requestScopeStore : $requestScopeStore,
            stateResetRegistry: $stateResetRegistry,
            componentRegistry : $componentRegistry,
            projectPath       : $this->projectPath,
            environmentName   : $envName,
            clock             : $clock,
            runtimeName       : 'avax',
            handleIncomingHttp: $this->handleIncomingHttp,
            httpHandler       : null,
            consoleCommands   : [],
        );

        $componentRegistry->boot(runtime: $runtime);

        return new App(
            runtime              : $runtime,
            resetApplicationState: new ResetApplicationState(stateResetRegistry: $stateResetRegistry),
            createHttpResponse   : $createHttpResponse,
            createRequestFromGlobals: $this->createRequestFromGlobals,
            dispatcher           : $dispatcher,
            routeRegistrar       : new FrameworkRouteRegistrar(),
            openRequestScope     : new OpenHttpRequestScope(
                requestScopes: $runtime->requestScopes(),
                runtimeContext: $runtime->context(),
            ),
            closeRequestScope    : new CloseHttpRequestScope(requestScopes: $runtime->requestScopes()),
            createRuntimeRequest : new CreateRuntimeRequestFromHttpRequest(),
        );
    }

    /**
     * Create from an ApplicationBuilder for more control.
     */
    public static function fromBuilder(ApplicationBuilder $builder, Clock $clock, CreateRequestFromGlobals $createRequestFromGlobals): App
    {
        $projectPath = $builder->projectPath();
        $envName = $builder->environment();

        $componentRegistry = new ComponentRegistry();
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $runtimeState = new RuntimeState(runtimeName: $builder->runtimeName());

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);
        $stateResetRegistry->register(name: 'static-state', state: new StaticStateReset());

        $runtimeState->markBooted(bootedAt: $clock->now());

        $createHttpResponse = new CreateHttpResponse();
        $normalizer = new NormalizeControllerResult(createHttpResponse: $createHttpResponse);
        $dispatcher = BuildRunApplication::fromDefaultResolutionPipeline(
            createHttpResponse: $createHttpResponse,
            normalizer        : $normalizer,
        );

        $runtime = new Runtime(
            runtimeState: $runtimeState,
            runtimeContext: $runtimeContext,
            requestScopeStore: $requestScopeStore,
            stateResetRegistry: $stateResetRegistry,
            componentRegistry: $componentRegistry,
            projectPath: $projectPath,
            environmentName: $envName,
            clock: $clock,
            runtimeName: $builder->runtimeName(),
            handleIncomingHttp: $builder->handleIncomingHttp(),
            httpHandler: null,
            consoleCommands: [],
        );

        $componentRegistry->boot(runtime: $runtime);

        return new App(
            runtime: $runtime,
            resetApplicationState: new ResetApplicationState(stateResetRegistry: $stateResetRegistry),
            createHttpResponse: $createHttpResponse,
            createRequestFromGlobals: $createRequestFromGlobals,
            dispatcher: $dispatcher,
            routeRegistrar: new FrameworkRouteRegistrar(),
            openRequestScope: new OpenHttpRequestScope(
                requestScopes: $runtime->requestScopes(),
                runtimeContext: $runtime->context(),
            ),
            closeRequestScope: new CloseHttpRequestScope(requestScopes: $runtime->requestScopes()),
            createRuntimeRequest: new CreateRuntimeRequestFromHttpRequest(),
        );
    }
}
