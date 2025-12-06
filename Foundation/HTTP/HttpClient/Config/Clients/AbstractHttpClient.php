<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Clients;

use Avax\HTTP\HttpClient\Config\Contracts\Client\Async\AsyncOperationInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class AbstractHttpClient
 *
 * An abstract base class that defines the blueprint for HTTP clients.
 * Provides methods for sending synchronous and asynchronous requests, handling responses, and logging.
 * Concrete clients like GuzzleClient will implement the actual HTTP handling logic.
 */
abstract class AbstractHttpClient
{
    /**
     * Constructor to initialize the logger.
     *
     * @param LoggerInterface $logger Injected logger instance for error and info logging.
     */
    public function __construct(protected LoggerInterface $logger) {}

    /**
     * Sends a synchronous HTTP request.
     *
     * @param string $method   The HTTP method (GET, POST, etc.).
     * @param string $endpoint The URL endpoint.
     * @param array  $options  Additional options for the request.
     *
     * @return array A structured response with endpoint and result data.
     */
    abstract public function sendRequest(
        string $method,
        string $endpoint,
        array  $options = []
    ) : ResponseInterface;

    /**
     * Sends an asynchronous HTTP request.
     *
     * @param string $method   The HTTP method (GET, POST, etc.).
     * @param string $endpoint The URL endpoint.
     * @param array  $options  Additional options for the request.
     *
     * @return AsyncOperationInterface A promise-like interface that resolves with structured response data.
     */
    abstract public function sendAsyncRequest(
        string $method,
        string $endpoint,
        array  $options = [],
    ) : AsyncOperationInterface;

    /**
     * Formats the response data for synchronous or asynchronous requests.
     *
     * @param ResponseInterface $response The HTTP response.
     * @param string            $endpoint The URL endpoint from which the response was retrieved.
     *
     * @return array The formatted response data containing the endpoint and parsed result.
     */
    protected function formatResponse(ResponseInterface $response, string $endpoint) : array
    {
        $this->logger->info(
            message: sprintf('Request to %s succeeded', $endpoint),
            context: [
                         'status' => $response->getStatusCode(),
                     ],
        );

        return [
            'endpoint' => $endpoint,
            'status'   => $response->getStatusCode(),
            'data'     => $response->getBody()->getContents(),
        ];
    }

    /**
     * Logs successful async requests.
     *
     * @param string            $endpoint The endpoint URL.
     * @param ResponseInterface $response The response object.
     */
    protected function logSuccess(string $endpoint, ResponseInterface $response) : void
    {
        $this->logger->info(
            message: 'Asynchronous request to ' . $endpoint
                     . ' completed successfully with status ' . $response->getStatusCode(),
        );
    }

    /**
     * Asynchronous error handler for promises.
     *
     * @param string $endpoint The endpoint URL.
     * @param mixed  $reason   The reason for the failure (typically an exception or error message).
     *
     * @return array The formatted failure data.
     */
    protected function handleAsyncFailure(string $endpoint, mixed $reason) : array
    {
        $this->logger->error(
            message: 'Asynchronous request to ' . $endpoint . ' failed.',
            context: [
                         'reason' => $reason instanceof Throwable ? $reason->getMessage() : 'Unknown error',
                     ],
        );

        return $this->handleFailure(endpoint: $endpoint, reason: $reason);
    }

    /**
     * Logs an error and returns a formatted failure response.
     *
     * @param string $endpoint The endpoint URL.
     * @param mixed  $reason   The reason for the failure (could be an exception or another value).
     *
     * @return array The error data, structured with endpoint and reason.
     */
    public function handleFailure(string $endpoint, mixed $reason) : array
    {
        $errorMessage = $reason instanceof Throwable ? $reason->getMessage() : 'Unknown error';
        $this->logger->error(message: sprintf('Request to %s failed', $endpoint), context: ['error' => $errorMessage]);

        return [
            'endpoint' => $endpoint,
            'error'    => $errorMessage,
        ];
    }
}
