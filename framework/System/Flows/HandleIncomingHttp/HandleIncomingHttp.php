<?php

declare(strict_types=1);

namespace Avax\Framework\System\Flows\HandleIncomingHttp;

use Avax\Components\HTTP\Response\ResponseFactory;
use Avax\Framework\System\Capabilities\Runtime\RuntimeInterface;
use Avax\Framework\System\Capabilities\Runtime\RuntimeRequest;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResponse;
use Avax\Framework\System\Capabilities\Runtime\RuntimeResult;
use Avax\Framework\System\Foundation\Failure\FrameworkMisconfigured;
use JsonSerializable;
use Psr\Http\Message\ResponseInterface;
use Random\RandomException;
use Stringable;
use Throwable;

final readonly class HandleIncomingHttp
{
    public function __construct(private ResponseFactory $responseFactory = new ResponseFactory) {}

    /**
     * @throws RandomException
     */
    public function handle(RuntimeInterface $runtime, RuntimeRequest $request) : RuntimeResponse
    {
        $openRequestScope = new OpenHttpRequestScope(
            requestScopes : $runtime->requestScopes(),
            runtimeContext: $runtime->context(),
        );
        $closeRequestScope = new CloseHttpRequestScope(requestScopes: $runtime->requestScopes());

        $openRequestScope->open(request: $request);

        try {
            return $this->handleInCurrentScope(runtime: $runtime, request: $request);
        } finally {
            $closeRequestScope->close();
        }
    }

    public function handleInCurrentScope(RuntimeInterface $runtime, RuntimeRequest $request) : RuntimeResponse
    {
        $httpHandler = $runtime->httpHandler();

        if ($httpHandler === null) {
            throw new FrameworkMisconfigured(message: 'No HTTP handler is configured for the framework runtime.');
        }

        try {
            $response = $this->normalizeResponse(
                value: $httpHandler($request, $runtime),
            );

            $runtime->context()->finishRequest(
                result: RuntimeResult::fromResponse(response: $response),
            );

            return $response;
        } catch (Throwable $throwable) {
            $response = RuntimeResponse::fromPsrResponse(
                response: $this->responseFactory->createErrorResponse(
                    statusCode: 500,
                    message   : 'Internal Server Error',
                ),
            );

            $runtime->context()->finishRequest(
                result: RuntimeResult::fromResponse(response: $response),
            );

            return $response;
        }
    }

    private function normalizeResponse(mixed $value) : RuntimeResponse
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
            $value === null          => '',
            default                  => '',
        };

        return RuntimeResponse::fromPsrResponse(
            response: $this->responseFactory->create(body: $normalizedBody),
        );
    }
}
