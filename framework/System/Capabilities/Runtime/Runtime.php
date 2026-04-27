<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLoop;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Closure;

final readonly class Runtime implements RuntimeInterface
{
    /**
     * @param array<string, Closure> $consoleCommands
     */
    public function __construct(
        private RuntimeState $state,
        private RuntimeContext $context,
        private RequestScopeStore $requestScopes,
        private StateResetRegistry $stateResetRegistry,
        private ComponentRegistry $components,
        private ProjectPath $projectPath,
        private EnvironmentName $environment,
        private Clock $clock,
        private string $runtimeName,
        private Closure|null $httpHandler = null,
        private array $consoleCommands = [],
    ) {
    }

    public function state(): RuntimeState
    {
        return $this->state;
    }

    public function context(): RuntimeContext
    {
        return $this->context;
    }

    public function requestScopes(): RequestScopeStore
    {
        return $this->requestScopes;
    }

    public function components(): ComponentRegistry
    {
        return $this->components;
    }

    public function stateResetRegistry(): StateResetRegistry
    {
        return $this->stateResetRegistry;
    }

    public function projectPath(): ProjectPath
    {
        return $this->projectPath;
    }

    public function environment(): EnvironmentName
    {
        return $this->environment;
    }

    public function clock(): Clock
    {
        return $this->clock;
    }

    public function runtimeName(): string
    {
        return $this->runtimeName;
    }

    public function httpHandler(): Closure|null
    {
        return $this->httpHandler;
    }

    public function consoleCommands(): array
    {
        return $this->consoleCommands;
    }

    public function runWorker(WorkerRuntimeInterface $workerRuntime): WorkerLifecycle
    {
        $workerLoop = new WorkerLoop(
            runtime      : $this,
            workerRuntime: $workerRuntime,
        );

        return $workerLoop->run();
    }
}
