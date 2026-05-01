<?php

declare(strict_types=1);

namespace Avax\Framework\System\PublicSurface\Http;

use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Flows\HandleIncomingHttp\HandleIncomingHttp;

final readonly class HttpKernel implements HttpKernelInterface
{
    public function __construct(
        private RuntimeInterface $runtime,
        private HandleIncomingHttp $handleIncomingHttp,
    ) {
    }

    public function handle(RuntimeRequest $runtimeRequest) : RuntimeResponse
    {
        return $this->handleIncomingHttp->handle(
            runtime: $this->runtime,
            request: $runtimeRequest,
        );
    }
}
