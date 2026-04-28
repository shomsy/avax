<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Adapters\Workerman;

use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRequest;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerResponse;
use Avax\Framework\System\Capabilities\Runtime\Worker\WorkerRuntimeInterface;
use Closure;

final readonly class WorkermanRuntime implements WorkerRuntimeInterface
{
    /**
     * @param Closure(): (WorkerRequest|null) $receiver
     * @param Closure(WorkerResponse): void $sender
     */
    public function __construct(
        private Closure $receiver,
        private Closure $sender,
    ) {
    }

    public function name(): string
    {
        return 'workerman';
    }

    public function receive(): WorkerRequest|null
    {
        return ($this->receiver)();
    }

    public function send(WorkerResponse $response): void
    {
        ($this->sender)($response);
    }
}
