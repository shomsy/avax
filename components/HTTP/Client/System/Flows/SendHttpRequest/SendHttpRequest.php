<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Flows\SendHttpRequest;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Capabilities\Transports\HttpTransportInterface;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpTimeout;
use Throwable;

/**
 * SendHttpRequest - Main flow orchestrator for outbound HTTP requests.
 *
 * Coordinates the full request lifecycle:
 * 1. Build the PSR-7 request from OutboundRequest
 * 2. Send via the transport layer
 * 3. Decode the response into ClientResponse
 * 4. Handle failures with retry logic
 */
final readonly class SendHttpRequest
{
    /**
     * @param BuildOutboundRequest   $buildRequest   Request builder
     * @param DecodeHttpResponse     $decodeResponse Response decoder
     * @param HandleHttpFailure      $handleFailure  Failure handler with retry logic
     * @param HttpTransportInterface $transport      HTTP transport backend
     */
    public function __construct(
        public BuildOutboundRequest $buildRequest,
        public DecodeHttpResponse $decodeResponse,
        public HandleHttpFailure $handleFailure,
        public HttpTransportInterface $transport,
    ) {}

    /**
     * Execute an outbound HTTP request.
     *
     * @param OutboundRequest $outboundRequest The outbound request to execute
     *
     * @return ClientResponse The HTTP response
     *
     * @throws HttpRequestFailed if the request fails and retries are exhausted
     * @throws HttpTimeout if the request times out
     */
    public function execute(OutboundRequest $outboundRequest) : ClientResponse
    {
        try {
            return $this->transport->send($outboundRequest);
        } catch (Throwable $throwable) {
            return $this->handleFailure->handle(
                retryCallback: fn (OutboundRequest $outboundRequest) : ClientResponse => $this->transport->send($outboundRequest),
                request      : $outboundRequest,
                exception    : $throwable,
                options      : $outboundRequest->options,
            );
        }
    }
}
