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
        private PhpFpmRequestReader $requestReader = new PhpFpmRequestReader(),
        private PhpFpmResponseSender $responseSender = new PhpFpmResponseSender(),
    ) {
    }

    public function handle(RuntimeRequest $request): RuntimeResponse
    {
        return $this->httpKernel->handle(request: $request);
    }

    /**
     * @param array<string, string> $server
     * @param array<string, mixed>  $query
     * @param array<string, mixed>  $parsedBody
     */
    public function handleGlobals(
        array $server,
        array $query = [],
        array $parsedBody = [],
        string|null $body = null,
    ): RuntimeResponse {
        return $this->handle(
            request: $this->requestReader->read(
                server    : $server,
                query     : $query,
                parsedBody: $parsedBody,
                body      : $body,
            ),
        );
    }

    public function send(RuntimeResponse $response): void
    {
        $this->responseSender->send(response: $response);
    }
}
