<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;

final class WorkerLoop
{
    private bool $running = false;

    public function __construct(
        private RuntimeInterface|null       $runtime = null,
        private WorkerRuntimeInterface|null $workerRuntime = null,
    ) {}

    public function start() : void
    {
        $this->running = true;
    }

    public function isRunning() : bool
    {
        return $this->running;
    }

    public function stop() : void
    {
        $this->running = false;
    }

    public function tick() : void
    {
    }

    public function run() : WorkerLifecycle
    {
        $runtime       = $this->runtime;
        $workerRuntime = $this->workerRuntime;

        if ($runtime === null || $workerRuntime === null) {
            throw new FrameworkMisconfigured(
                message: 'Worker loop requires runtime and worker runtime before it can run.',
            );
        }

        $this->start();

        $lifecycle = new WorkerLifecycle(
            runtimeName: $workerRuntime->name(),
            startedAt  : $runtime->clock()->now(),
        );

        try {
            $this->runUntilEmpty(
                runtime      : $runtime,
                workerRuntime: $workerRuntime,
                lifecycle    : $lifecycle,
            );
        } finally {
            $this->stop();
            $workerRuntime->stop();
            $runtime->stateResetRegistry()->resetAll();
            $runtime->state()->markShutdown(shutdownAt: $runtime->clock()->now());
            $lifecycle->stop(stoppedAt: $runtime->clock()->now());
        }

        return $lifecycle;
    }

    private function runUntilEmpty(
        RuntimeInterface       $runtime,
        WorkerRuntimeInterface $workerRuntime,
        WorkerLifecycle        $lifecycle,
    ) : void
    {
        $handleIncomingHttp = new HandleIncomingHttp();

        while ( ($request = $workerRuntime->receive()) !== null ) {
            $response = $handleIncomingHttp->handle(
                runtime: $runtime,
                request: $request->request(),
            );

            $workerRuntime->send(
                response: WorkerResponse::fromRuntimeResponse(
                            requestId: $request->id(),
                            response : $response,
                        ),
            );

            $lifecycle->recordHandledRequest(requestId: $request->id());
            $runtime->stateResetRegistry()->resetAll();
        }
    }
}
