<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\Worker;

use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;

interface WorkerRuntimeInterface
{
    public function name() : string;

    public function receive() : WorkerRequest|null;

    public function send(WorkerResponse $response) : void;

    public function run() : void;

    public function handleRequest(object $request) : object;

    public function stop() : void;

    public function getContext() : RuntimeContext;
}
