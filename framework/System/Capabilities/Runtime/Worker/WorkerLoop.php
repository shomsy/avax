<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;

final class WorkerLoop
{
    private bool $running = false;

    public function __construct(
        private readonly RuntimeInterface       $runtime,
        private readonly WorkerRuntimeInterface $workerRuntime,
        private readonly HandleIncomingHttp     $handleIncomingHttp,
    ) {
    }

    public function start(): void
    {
        $this->running = true;
    }

    public function isRunning(): bool
    {
        return $this->running;
    }

    public function stop(): void
    {
        $this->running = false;
    }

    public function run(): WorkerLifecycle
    {
        $runtime = $this->runtime;
        $workerRuntime = $this->workerRuntime;

        $this->start();

        $workerLifecycle = new WorkerLifecycle(
            runtimeName: $workerRuntime->name(),
            startedAt  : $runtime->clock()->now(),
        );

        try {
            $this->runUntilEmpty(
                runtime      : $runtime,
                workerRuntime: $workerRuntime,
                workerLifecycle: $workerLifecycle,
            );
        } finally {
            $this->stop();
            $workerRuntime->stop();
            $runtime->stateResetRegistry()->resetAll();
            $runtime->state()->markShutdown(shutdownAt: $runtime->clock()->now());
            $workerLifecycle->stop(stoppedAt: $runtime->clock()->now());
        }

        return $workerLifecycle;
    }

    private function runUntilEmpty(
        RuntimeInterface $runtime,
        WorkerRuntimeInterface $workerRuntime,
        WorkerLifecycle $workerLifecycle,
    ): void {
        while (($request = $workerRuntime->receive()) instanceof WorkerRequest) {
            $response = $this->handleIncomingHttp->handle(
                runtime: $runtime,
                runtimeRequest: $request->request(),
            );

            $workerRuntime->send(
                workerResponse: WorkerResponse::fromRuntimeResponse(
                    requestId: $request->id(),
                    runtimeResponse: $response,
                ),
            );

            $workerLifecycle->recordHandledRequest(requestId: $request->id());
            $runtime->stateResetRegistry()->resetAll();
        }
    }
}
