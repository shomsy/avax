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
    public function __construct(
        private ComponentRegistry|null  $componentRegistry = null,
        private RequestScopeStore|null  $requestScopeStore = null,
        private RuntimeContext|null     $runtimeContext = null,
        private StateResetRegistry|null $stateResetRegistry = null,
    ) {}

    public function build(ApplicationBuilder $builder): Runtime
    {
        $componentRegistry  = $this->componentRegistry ?? new ComponentRegistry();
        $requestScopeStore  = $this->requestScopeStore ?? new RequestScopeStore();
        $runtimeContext     = $this->runtimeContext ?? new RuntimeContext();
        $stateResetRegistry = $this->stateResetRegistry ?? new StateResetRegistry();
        $runtimeState = new RuntimeState(runtimeName: $builder->runtimeName());

        $stateResetRegistry->register(name: 'request-scopes', state: $requestScopeStore);
        $stateResetRegistry->register(name: 'runtime-context', state: $runtimeContext);

        foreach ($builder->componentProviders() as $componentProvider) {
            $componentRegistry->registerProvider(provider: $componentProvider);
        }

        $runtimeState->markBooted(bootedAt: $builder->clock()->now());

        $runtime = new Runtime(
            runtimeState     : $runtimeState,
            runtimeContext   : $runtimeContext,
            requestScopeStore: $requestScopeStore,
            stateResetRegistry: $stateResetRegistry,
            componentRegistry: $componentRegistry,
            projectPath      : $builder->projectPath(),
            environmentName  : $builder->environment(),
            clock            : $builder->clock(),
            runtimeName      : $builder->runtimeName(),
            httpHandler      : $builder->httpHandler(),
            consoleCommands  : $builder->consoleCommands(),
        );

        $componentRegistry->boot(runtime: $runtime);

        return $runtime;
    }
}
