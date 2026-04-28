<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface;

use Avax\Framework\System\Capabilities\ComponentRegistry\ComponentRegistry;
use Avax\Framework\System\Capabilities\RequestScope\RequestScopeStore;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeState;
use Avax\Framework\System\Capabilities\StateReset\StateResetReport;
use Avax\Framework\System\Configuration\BuildApplication\ApplicationBuilder;
use Avax\Framework\System\Flows\BootApplication\BootApplication;
use Avax\Framework\System\Flows\BootApplication\BuildApplicationState;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Flows\ResetApplicationState\ResetApplicationState;
use Avax\Framework\System\Flows\RunConsoleCommand\RunConsoleCommand;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernel;
use Avax\Framework\System\PublicSurface\Console\ConsoleKernelInterface;
use Avax\Framework\System\PublicSurface\Http\HttpKernel;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernel;
use Avax\Framework\System\PublicSurface\Runtime\RuntimeKernelInterface;

final readonly class Avax implements AvaxInterface
{
    public function __construct(
        private RuntimeInterface $runtime,
        private HttpKernelInterface $httpKernel,
        private ConsoleKernelInterface $consoleKernel,
        private RuntimeKernelInterface $runtimeKernel,
        private ResetApplicationState $resetApplicationState,
    ) {
    }

    public static function boot(ApplicationBuilder $builder): self
    {
        $runtime = (new BootApplication(
            buildApplicationState: new BuildApplicationState(),
        ))->boot(builder: $builder);

        return new self(
            runtime              : $runtime,
            httpKernel           : new HttpKernel(runtime: $runtime, handleIncomingHttp: new HandleIncomingHttp()),
            consoleKernel        : new ConsoleKernel(runtime: $runtime, runConsoleCommand: new RunConsoleCommand()),
            runtimeKernel        : new RuntimeKernel(runtime: $runtime),
            resetApplicationState: new ResetApplicationState(stateResetRegistry: $runtime->stateResetRegistry()),
        );
    }

    public function state(): RuntimeState
    {
        return $this->runtime->state();
    }

    public function context(): RuntimeContext
    {
        return $this->runtime->context();
    }

    public function requestScopes(): RequestScopeStore
    {
        return $this->runtime->requestScopes();
    }

    public function components(): ComponentRegistry
    {
        return $this->runtime->components();
    }

    public function http(): HttpKernelInterface
    {
        return $this->httpKernel;
    }

    public function console(): ConsoleKernelInterface
    {
        return $this->consoleKernel;
    }

    public function runtime(): RuntimeKernelInterface
    {
        return $this->runtimeKernel;
    }

    public function resetState(): StateResetReport
    {
        return $this->resetApplicationState->reset();
    }
}
