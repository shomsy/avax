<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Adapters\RoadRunner;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerResponse;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;
use Closure;
use RuntimeException;

final readonly class RoadRunnerRuntime implements WorkerRuntimeInterface
{
    /**
     * @param Closure(): (WorkerRequest|null) $receiver
     * @param Closure(WorkerResponse) : void $sender
     */
    public function __construct(
        private Closure $receiver,
        private Closure $sender,
    ) {}

    public function name() : string
    {
        return 'roadrunner';
    }

    public function receive() : WorkerRequest|null
    {
        return ($this->receiver)();
    }

    public function send(WorkerResponse $response) : void
    {
        ($this->sender)($response);
    }

    public function run() : void {}

    public function handleRequest(object $request) : object
    {
        return $request;
    }

    public function stop() : void {}

    public function getContext() : RuntimeContext
    {
        throw new RuntimeException('Not implemented');
    }
}
