<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\System\Capabilities\ResponseBuilding\ResponseFactory;
use Avax\Components\Operations\Observability\System\Capabilities\MetricsCollector\MetricsCollector;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use Closure;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Random\RandomException;
use Stringable;
use Throwable;

final readonly class HandleIncomingHttp
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private MetricsCollector|null $metricsCollector = null,
    ) {
    }

    /**
     * @throws RandomException
     */
    public function handle(RuntimeInterface $runtime, RuntimeRequest $runtimeRequest): RuntimeResponse
    {
        $openHttpRequestScope = new OpenHttpRequestScope(
            requestScopes : $runtime->requestScopes(),
            runtimeContext   : $runtime->context(),
        );
        $closeHttpRequestScope = new CloseHttpRequestScope(requestScopes: $runtime->requestScopes());

        $openHttpRequestScope->open(request: $runtimeRequest);

        try {
            return $this->handleInCurrentScope(runtime: $runtime, runtimeRequest: $runtimeRequest);
        } finally {
            $closeHttpRequestScope->close();
        }
    }

    public function handleInCurrentScope(RuntimeInterface $runtime, RuntimeRequest $runtimeRequest): RuntimeResponse
    {
        $httpHandler = $runtime->httpHandler();

        if (! $httpHandler instanceof Closure) {
            throw new FrameworkMisconfigured(message: 'No HTTP handler is configured for the framework runtime.');
        }

        $this->metricsCollector?->incrementCounter(name: 'request.count');

        $startTime = microtime(true);

        try {
            $response = $this->normalizeResponse(
                value: $httpHandler($runtimeRequest, $runtime),
            );

            $this->recordLatency(startTime: $startTime);

            $runtime->context()->finishRequest(
                runtimeResult: RuntimeResult::fromResponse(runtimeResponse: $response),
            );

            return $response;
        } catch (Throwable) {
            $this->metricsCollector?->incrementCounter(name: 'request.error');
            $this->recordLatency(startTime: $startTime);

            $response = RuntimeResponse::fromPsrResponse(
                response: $this->responseFactory->createErrorResponse(
                    message   : 'Internal Server Error',
                    statusCode: 500,
                ),
            );

            $runtime->context()->finishRequest(
                runtimeResult: RuntimeResult::fromResponse(runtimeResponse: $response),
            );

            return $response;
        }
    }

    private function recordLatency(float $startTime) : void
    {
        $latencyMs = (microtime(true) - $startTime) * 1000;
        $this->metricsCollector?->observeHistogram(name: 'request.latency', value: $latencyMs);
    }

    private function normalizeResponse(mixed $value): RuntimeResponse
    {
        if ($value instanceof RuntimeResponse) {
            return $value;
        }

        if ($value instanceof ResponseInterface) {
            return RuntimeResponse::fromPsrResponse(response: $value);
        }

        if (is_array(value: $value) || $value instanceof JsonSerializable || (is_object(value: $value) && ! $value instanceof Stringable)) {
            return RuntimeResponse::fromPsrResponse(
                response: $this->responseFactory->json(data: $value),
            );
        }

        $normalizedBody = match (true) {
            $value instanceof Stringable => (string) $value,
            is_scalar(value: $value) => (string) $value,
            $value === null => '',
            default => '',
        };

        return RuntimeResponse::fromPsrResponse(
            response: $this->responseFactory->create(body: $normalizedBody),
        );
    }
}
