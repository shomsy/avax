<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Clients\Guzzle;

use Avax\HTTP\HttpClient\Config\Middleware\RetryMiddleware;
use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Final class HttpClient
 *
 * Acts as a Guzzle-based HTTP client with support for synchronous and asynchronous requests.
 * This client includes retry and error-handling middleware.
 */
final readonly class HttpClient implements ClientInterface
{
    private ClientInterface $guzzleClient;

    /**
     * @param RetryMiddleware     $retryMiddleware
     * @param LoggerInterface     $logger
     * @param string|UriInterface $baseUri
     */
    public function __construct(
        private RetryMiddleware     $retryMiddleware,
        private LoggerInterface     $logger,
        private string|UriInterface $baseUri,
    )
    {
        // Initialize Guzzle client with base URI and middleware
        $this->guzzleClient = new Client(
            config: [
                'base_uri'        => $this->baseUri,
                'handler'         => $this->getHandlerStack(),
                'timeout'         => 90,  // Maximum duration of request
                'connect_timeout' => 10, // Timeout for connection
                'http_errors'     => false,
                'headers'         => [
                    'Accept'       => 'application/json',
                    'Content-Type' => 'application/json',
                ],
            ],
        );
    }

    /**
     * @return HandlerStack
     */
    private function getHandlerStack() : HandlerStack
    {
        $handlerStack = HandlerStack::create();
        $handlerStack->push(middleware: $this->retryMiddleware->createRetryMiddleware());

        return $handlerStack;
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return ResponseInterface
     * @throws Exception
     */
    public function send(RequestInterface $request, array $options = []) : ResponseInterface
    {
        try {
            return $this->performRequest(
                method : $request->getMethod(),
                uri    : $request->getUri(),
                options: $options,
            );
        } catch (Throwable $throwable) {
            $this->logger->error(
                message: "Request failed",
                context: [
                    'uri'   => (string) $request->getUri(),
                    'error' => $throwable->getMessage(),
                ],
            );
            throw new Exception(message: "Failed to send request", code: $throwable->getCode(), previous: $throwable);
        }
    }

    /**
     * @param string              $method
     * @param string|UriInterface $uri
     * @param array|null          $options
     * @param bool                $async
     *
     * @return ResponseInterface|PromiseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Exception
     */
    private function performRequest(
        string              $method,
        string|UriInterface $uri,
        array|null          $options = null,
        bool                $async = false
    ) : ResponseInterface|PromiseInterface
    {
        $options ??= [];
        try {
            $response = $async
                ? $this->guzzleClient->requestAsync(method: $method, uri: $uri, options: $options)
                : $this->guzzleClient->request(method: $method, uri: $uri, options: $options);

            if ($response->getStatusCode() === 504) {
                throw new RequestException(
                    message : "⏳ 504 Gateway Timeout - Server did not respond in time.",
                    request : new Request(method: $method, uri: $uri),
                    response: $response
                );
            }

            return $response;
        } catch (RequestException|ConnectException|Exception $e) {
            $this->logger->error(
                message: '⏳ HTTP error detected!',
                context: [
                    'method'    => $method,
                    'url'       => (string) $uri,
                    'exception' => $e->getMessage(),
                ],
            );

            if (str_contains(haystack: $e->getMessage(), needle: 'timed out')) {
                $this->logger->warning(
                    message: '⏳ HTTP Request stopped because of timeout!',
                    context: [
                        'method' => $method,
                        'url'    => (string) $uri,
                        'error'  => $e->getMessage(),
                    ],
                );
                throw new Exception(
                    message : "⏳ Request timeout (server did not respond in time)",
                    code    : 408,
                    previous: $e
                );
            }
            throw $e;
        }
    }

    /**
     * @param string $method
     * @param        $uri
     * @param array  $options
     *
     * @return PromiseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function requestAsync(string $method, $uri, array $options = []) : PromiseInterface
    {
        return $this->performRequest(
            method : $method,
            uri    : $uri,
            options: $options,
            async  : true,
        );
    }

    /**
     * Implements Guzzle's request method.
     *
     * @param string              $method  HTTP method.
     * @param string|UriInterface $uri     Request URI.
     * @param array               $options Additional request options.
     *
     * @return ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function request(string $method, $uri, array $options = []) : ResponseInterface
    {
        return $this->performRequest(
            method : $method,
            uri    : $uri,
            options: $options,
            async  : false,
        );
    }

    /**
     * @param RequestInterface $request
     * @param array            $options
     *
     * @return PromiseInterface
     * @throws Exception
     */
    public function sendAsync(RequestInterface $request, array $options = []) : PromiseInterface
    {
        try {
            // Delegate the asynchronous request to the underlying Guzzle client
            return $this->guzzleClient->sendAsync(request: $request, options: $options);
        } catch (Throwable $throwable) {
            $this->logger->error(
                message: "Asynchronous request failed",
                context: [
                    'uri'   => (string) $request->getUri(),
                    'error' => $throwable->getMessage(),
                ],
            );
            throw new Exception(
                message : "Failed to send async request",
                code    : $throwable->getCode(),
                previous: $throwable
            );
        }
    }

    /**
     * Implements Guzzle's getConfig method.
     *
     * @param string|null $option Configuration option to retrieve.
     *
     * @return mixed
     */
    public function getConfig(string|null $option = null) : mixed
    {
        return $this->guzzleClient->getConfig(option: $option);
    }
}