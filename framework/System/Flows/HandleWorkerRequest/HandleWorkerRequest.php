<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleWorkerRequest;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\Runtime\RuntimeContext;
use Throwable;

final readonly class HandleWorkerRequest
{
    public function __construct(
        private RuntimeContext $runtimeContext,
        private RequestScope   $requestScope,
    ) {
    }

    public function handle(object $request): object
    {
        $this->openWorkerRequestScope();

        try {
            $response = $this->runWorkerRequest($request);

            $this->closeWorkerRequestScope();

            return $response;
        } catch (Throwable $throwable) {
            $this->closeWorkerRequestScope();

            throw $throwable;
        }
    }

    private function openWorkerRequestScope(): void
    {
        $this->requestScope->open();
    }

    private function runWorkerRequest(object $request): object
    {
        return $this->runtimeContext->getRuntime()->handleRequest($request);
    }

    private function closeWorkerRequestScope(): void
    {
        $this->requestScope->close();
    }
}
