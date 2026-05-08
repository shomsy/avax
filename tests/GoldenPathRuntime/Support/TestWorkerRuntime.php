<?php

declare(strict_types=1);

namespace Avax\Tests\GoldenPathRuntime\Support;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerResponse;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;

/**
 * Test implementation of WorkerRuntimeInterface.
 *
 * Feeds pre-built WorkerRequest objects to the WorkerLoop and
 * collects WorkerResponse objects for assertion.
 */
final class TestWorkerRuntime implements WorkerRuntimeInterface
{
    /** @var list<WorkerRequest> */
    private array $requests;

    /** @var list<WorkerResponse> */
    private array $responses = [];

    private bool $running = true;

    private int $index = 0;

    private RuntimeContext $context;

    /**
     * @param list<WorkerRequest> $requests
     */
    public function __construct(array $requests)
    {
        $this->requests = $requests;
        $this->context  = new RuntimeContext();
    }

    public function name() : string
    {
        return 'test-worker';
    }

    public function receive() : ?WorkerRequest
    {
        if ($this->index >= count($this->requests) || ! $this->running) {
            return null;
        }

        return $this->requests[$this->index++];
    }

    public function send(WorkerResponse $workerResponse) : void
    {
        $this->responses[] = $workerResponse;
    }

    public function run() : void {}

    public function handleRequest(object $request) : object
    {
        return $request;
    }

    public function stop() : void
    {
        $this->running = false;
    }

    public function getContext() : RuntimeContext
    {
        return $this->context;
    }

    /**
     * @return list<WorkerResponse>
     */
    public function responses() : array
    {
        return $this->responses;
    }
}
