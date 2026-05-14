<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\CreateApplication;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\ResponseNormalization\NormalizeControllerResult;
use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
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
        private ResponseFactory         $responseFactory,
        private HandleIncomingHttp      $handleIncomingHttp,
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

        $runtimeState->markBooted(bootedAt: $clock->now());

        $responseFactory = $this->responseFactory;

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
            responseFactory      : $responseFactory,
            normalizer           : new NormalizeControllerResult(responseFactory: $responseFactory),
        );
    }

    /**
     * Create from an ApplicationBuilder for more control.
     */
    public static function fromBuilder(ApplicationBuilder $builder, Clock $clock): App
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

        $runtimeState->markBooted(bootedAt: $clock->now());

        $responseFactory = new ResponseFactory();

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
            responseFactory: $responseFactory,
            normalizer: new NormalizeControllerResult(responseFactory: $responseFactory),
        );
    }
}
