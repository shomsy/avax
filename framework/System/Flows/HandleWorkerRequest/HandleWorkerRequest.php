<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleWorkerRequest;

use Avax\Framework\System\Capabilities\RequestScope\RequestScope;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Throwable;

final readonly class HandleWorkerRequest
{
    public function __construct(
        private RuntimeInterface $runtime,
        private RequestScope $requestScope,
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
        // Assuming RuntimeInterface will eventually have a generic handleRequest or similar.
        // For now, this is a placeholder for the architectural intent.
        // In a real worker, this might delegate to a specific handler registry.
        return $request;
    }

    private function closeWorkerRequestScope(): void
    {
        $this->requestScope->close();
    }
}
