<?php

declare(strict_types=1);

namespace Avax\Components\Framework\System\Flows\HandleWorkerRequest;

use Avax\Components\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Components\Framework\System\Capabilities\Runtime\RuntimeContext;
use Throwable;

final class HandleWorkerRequest
{
    public function __construct(
        private readonly RuntimeContext $context,
        private readonly RequestScope $requestScope,
    ) {
    }

    public function handle(object $request): object
    {
        $this->openWorkerRequestScope();

        try {
            $response = $this->runWorkerRequest($request);

            $this->closeWorkerRequestScope();

            return $response;
        } catch (Throwable $e) {
            $this->closeWorkerRequestScope();

            throw $e;
        }
    }

    private function openWorkerRequestScope(): void
    {
        $this->requestScope->open();
    }

    private function runWorkerRequest(object $request): object
    {
        return $this->context->getRuntime()->handleRequest($request);
    }

    private function closeWorkerRequestScope(): void
    {
        $this->requestScope->close();
    }
}