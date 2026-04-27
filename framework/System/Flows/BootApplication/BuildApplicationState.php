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
    public function build(ApplicationBuilder $builder): Runtime
    {
        $components         = new ComponentRegistry();
        $requestScopes      = new RequestScopeStore();
        $runtimeContext     = new RuntimeContext();
        $stateResetRegistry = new StateResetRegistry();
        $runtimeState       = new RuntimeState(runtimeName: $builder->runtimeName());

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopes);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        foreach ($builder->componentProviders() as $provider) {
            $components->registerProvider(provider: $provider);
        }

        $runtimeState->markBooted(bootedAt: $builder->clock()->now());

        $runtime = new Runtime(
            state             : $runtimeState,
            context           : $runtimeContext,
            requestScopes     : $requestScopes,
            stateResetRegistry: $stateResetRegistry,
            components        : $components,
            projectPath       : $builder->projectPath(),
            environment       : $builder->environment(),
            clock             : $builder->clock(),
            runtimeName       : $builder->runtimeName(),
            httpHandler       : $builder->httpHandler(),
            consoleCommands   : $builder->consoleCommands(),
        );

        $components->boot(runtime: $runtime);

        return $runtime;
    }
}
