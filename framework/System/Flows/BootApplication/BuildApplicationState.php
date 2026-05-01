<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\BootApplication;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\Runtime;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;

final readonly class BuildApplicationState
{
    public function build(ApplicationBuilder $applicationBuilder) : Runtime
    {
        $componentRegistry = new ComponentRegistry();
        $requestScopeStore = new RequestScopeStore();
        $runtimeContext     = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $runtimeState      = new RuntimeState(runtimeName: $applicationBuilder->runtimeName());

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        foreach ($applicationBuilder->componentProviders() as $componentProvider) {
            $componentRegistry->registerProvider(provider: $componentProvider);
        }

        $runtimeState->markBooted(bootedAt: $applicationBuilder->clock()->now());

        $runtime = new Runtime(
            stateResetRegistry: $stateResetRegistry,
            projectPath       : $applicationBuilder->projectPath(),
            clock             : $applicationBuilder->clock(),
            runtimeName       : $applicationBuilder->runtimeName(),
            httpHandler       : $applicationBuilder->httpHandler(),
            consoleCommands   : $applicationBuilder->consoleCommands(),
            state             : $runtimeState,
            context           : $runtimeContext,
            requestScopes     : $requestScopeStore,
            components        : $componentRegistry,
            environment       : $applicationBuilder->environment(),
        );

        $componentRegistry->boot(runtime: $runtime);

        return $runtime;
    }
}
