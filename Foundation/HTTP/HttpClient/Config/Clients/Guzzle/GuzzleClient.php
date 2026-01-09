<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Clients\Guzzle;

use Avax\HTTP\HttpClient\Config\Clients\AbstractHttpClient;
use Avax\HTTP\HttpClient\Config\Contracts\Client\Async\AsyncOperationInterface;
use Avax\HTTP\HttpClient\Traits\HandlesAggregationTrait;
use Avax\HTTP\HttpClient\Traits\HandlesAsyncRequestsTrait;
use Avax\HTTP\HttpClient\Traits\HandlesHttpErrorsTrait;
use Avax\HTTP\HttpClient\Traits\HandlesHttpResponseTrait;
use Avax\HTTP\HttpClient\Traits\SendsHttpRequestsTrait;
use Avax\HTTP\Response\ResponseFactory;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class GuzzleClient
 *
 * A concrete HTTP client that uses various traits for sending requests, handling responses, and managing errors.
 */
final class GuzzleClient extends AbstractHttpClient
{
    use HandlesHttpResponseTrait;
    use SendsHttpRequestsTrait;
    use HandlesAsyncRequestsTrait;
    use HandlesAggregationTrait;
    use HandlesHttpErrorsTrait;

    /**
     * Constructor for the class.
     *
     * @param HttpClient      $httpClient      Allows handling of HTTP requests.
     * @param LoggerInterface $dataLogger      Logger to capture and record data-related events.
     * @param ResponseFactory $responseFactory Factory to create response objects.
     */
    public function __construct(
        private readonly HttpClient      $httpClient,
        private readonly LoggerInterface $dataLogger,
        private readonly ResponseFactory $responseFactory,
    )
    {
        parent::__construct(logger: $dataLogger);
    }

    /**
     * Sends an asynchronous HTTP request.
     *
     * @param string $method   The HTTP method (GET, POST, etc.).
     * @param string $endpoint The URL endpoint.
     * @param array  $options  Additional options for the request.
     *
     * @return AsyncOperationInterface A promise-like interface that resolves with structured response data.
     * @throws \Throwable
     * @throws \Throwable
     */
    public function sendAsyncRequest(string $method, string $endpoint, array $options = []) : AsyncOperationInterface
    {
        try {
            $promise = $this->httpClient->requestAsync(method: $method, uri: $endpoint, options: $options);

            return new GuzzleAsyncOperation(promise: $promise, logger: $this->dataLogger);
        } catch (Throwable $throwable) {
            // Log the error for debugging purposes
            $this->logRequestError(method: $method, endpoint: $endpoint, options: $options, throwable: $throwable);

            // Re-throw the exception to ensure it's handled upstream
            throw $throwable;
        }
    }

    /**
     * Logs request errors for debugging and monitoring purposes.
     *
     * @param string    $method    The HTTP method used.
     * @param string    $endpoint  The URL endpoint.
     * @param array     $options   Additional options for the request.
     * @param Throwable $throwable The exception that occurred.
     */
    private function logRequestError(string $method, string $endpoint, array $options, Throwable $throwable) : void
    {
        $this->dataLogger->error(
            message: 'HTTP Request failed',
            context: [
                'method'    => $method,
                'endpoint'  => $endpoint,
                'options'   => $options,
                'exception' => [
                    'message' => $throwable->getMessage(),
                    'code'    => $throwable->getCode(),
                    'trace'   => $throwable->getTraceAsString(),
                ],
            ]
        );
    }
}
