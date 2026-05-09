<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\CreateApplication;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\SystemClock;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\PublicSurface\App;

/**
 * CreateApplication — Zero-configuration application factory flow.
 *
 * Creates a tiny runnable App API over existing AvaX internals.
 * Wires Runtime, RequestScope, and state reset together
 * without requiring the user to manually configure ApplicationBuilder.
 *
 * The Container is not initialized globally here — route dispatch
 * uses RouteFacadeContainer (a minimal PSR-11 container) for
 * controller resolution. Full container integration comes in later V4 stages.
 */
final readonly class CreateApplication
{
    /**
     * Create a new App with default configuration.
     *
     * @param string $environment Environment name (e.g. 'production', 'local', 'testing')
     */
    public function make(string $environment = 'production'): App
    {
        // Build runtime state
        $projectPath = new ProjectPath(value: getcwd() ?: __DIR__ . '/../../../..');
        $envName = new EnvironmentName(value: $environment);
        $clock = new SystemClock();

        $componentRegistry = new ComponentRegistry();
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $runtimeState = new RuntimeState(runtimeName: 'avax');

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        $runtimeState->markBooted(bootedAt: $clock->now());

        // Build runtime (no httpHandler — App manages routes directly)
        $runtime = new Runtime(
            runtimeState: $runtimeState,
            runtimeContext: $runtimeContext,
            requestScopeStore: $requestScopeStore,
            stateResetRegistry: $stateResetRegistry,
            componentRegistry: $componentRegistry,
            projectPath: $projectPath,
            environmentName: $envName,
            clock: $clock,
            runtimeName: 'avax',
            httpHandler: null,
            consoleCommands: [],
        );

        $componentRegistry->boot(runtime: $runtime);

        return new App(
            runtime: $runtime,
            resetApplicationState: new ResetApplicationState(stateResetRegistry: $stateResetRegistry),
        );
    }
}
