<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Runtime\PhpFpm;

use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\PublicSurface\Http\HttpKernelInterface;

final readonly class PhpFpmRuntime
{
    public function __construct(
        private HttpKernelInterface $httpKernel,
        private PhpFpmRequestReader $phpFpmRequestReader = new PhpFpmRequestReader(),
        private PhpFpmResponseSender $phpFpmResponseSender = new PhpFpmResponseSender(),
    ) {
    }

    public function handle(RuntimeRequest $runtimeRequest): RuntimeResponse
    {
        return $this->httpKernel->handle(request: $runtimeRequest);
    }

    /**
     * @param  array<string, string>  $server
     * @param  array<string, mixed>  $query
     * @param  array<string, mixed>  $parsedBody
     */
    public function handleGlobals(
        array $server,
        array $query = [],
        array $parsedBody = [],
        ?string $body = null,
    ): RuntimeResponse {
        return $this->handle(
            runtimeRequest: $this->phpFpmRequestReader->read(
                server    : $server,
                query     : $query,
                parsedBody: $parsedBody,
                body      : $body,
            ),
        );
    }

    public function send(RuntimeResponse $runtimeResponse): void
    {
        $this->phpFpmResponseSender->send(runtimeResponse: $runtimeResponse);
    }
}
