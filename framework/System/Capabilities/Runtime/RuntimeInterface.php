<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerLifecycle;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;
use Avax\Framework\System\Capabilities\StateReset\StateResetRegistry;
use Avax\Framework\System\Foundation\Environment\EnvironmentName;
use Avax\Framework\System\Foundation\Paths\ProjectPath;
use Avax\Framework\System\Foundation\Time\Clock;
use Closure;

interface RuntimeInterface
{
    public function state() : RuntimeState;

    public function context() : RuntimeContext;

    public function requestScopes() : RequestScopeStore;

    public function components() : ComponentRegistry;

    public function stateResetRegistry() : StateResetRegistry;

    public function projectPath() : ProjectPath;

    public function environment() : EnvironmentName;

    public function clock() : Clock;

    public function runtimeName() : string;

    public function httpHandler() : ?Closure;

    /**
     * @return array<string, Closure>
     */
    public function consoleCommands() : array;

    public function runWorker(WorkerRuntimeInterface $workerRuntime) : WorkerLifecycle;
}
