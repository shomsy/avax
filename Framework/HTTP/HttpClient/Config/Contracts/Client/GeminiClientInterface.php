<?php

declare(strict_types=1);

namespace Gemini\HTTP\HttpClient\Config\Contracts\Client;

use Gemini\HTTP\Enums\HttpMethod;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Interface GeminiClientInterface
 *
 * This interface defines the contract for interacting with HTTP clients.
 * The methods defined are for sending synchronous and asynchronous HTTP requests,
 * as well as aggregating data from multiple endpoints.
 */
interface GeminiClientInterface
{
    /**
     * Send a POST request with the specified body and optional headers.
     *
     * @param string $url     The endpoint URL.
     * @param array  $body    Data to send in the request body.
     * @param array  $headers Optional headers for the request.
     *
     * @return ResponseInterface The formatted response from the endpoint.
     */
    public function sendPostRequest(string $url, array $body, array $headers = []) : ResponseInterface;

    /**
     * Generic method for sending HTTP requests.
     *
     * @param string     $method  HTTP method (GET, POST, etc.).
     * @param string     $url     The URL endpoint.
     * @param array|null $headers Optional headers for the request.
     * @param array      $options Additional options for the request.
     *
     * @return ResponseInterface The raw response from the server.
     */
    public function sendRequest(
        string     $method,
        string     $url,
        array|null $headers = null,
        array      $options = [],
    ) : ResponseInterface;

    /**
     * Process the Guzzle response into a structured format using ResponseFactory.
     *
     * @param mixed  $response The raw Guzzle response.
     * @param string $endpoint The endpoint URL.
     *
     * @return ResponseInterface A standardized response.
     */
    public function getResponse(mixed $response, string $endpoint) : ResponseInterface;

    /**
     * Aggregates data asynchronously from multiple URLs.
     *
     * @param array $urls The list of URLs to request data from.
     *
     * @return PromiseInterface A promise that resolves with aggregated results.
     */
    public function aggregateDataAsynchronously(array $urls) : PromiseInterface;

    /**
     * Creates asynchronous requests for multiple endpoints.
     *
     * @param array      $urls   An array of URLs.
     * @param HttpMethod $method The HTTP method to use (GET, POST, etc.).
     *
     * @return array An array of promises for the requests.
     */
    public function createAsyncRequests(array $urls) : array;

    /**
     * Settles multiple promises and processes their results.
     *
     * @param array $promises The promises to settle.
     *
     * @return PromiseInterface A promise that resolves with the processed results.
     */
    public function settlePromises(array $promises) : PromiseInterface;

    /**
     * Processes the results of multiple promises.
     *
     * @param array $results The array of settled promises.
     *
     * @return array The processed results.
     */
    public function processPromisesResults(array $results) : array;

    /**
     * Processes a single promise result.
     *
     * @param string $endpoint The endpoint URL.
     * @param array  $result   The result of the request.
     *
     * @return ResponseInterface A structured response based on the result.
     */
    public function processSinglePromiseResult(string $endpoint, array $result) : ResponseInterface;

    /**
     * Handles failures in request promises.
     *
     * @param string $endpoint The URL of the failed request.
     * @param mixed  $reason   The reason for the failure.
     *
     * @return ResponseInterface A structured response containing the error information.
     */
    public function handleFailure(string $endpoint, mixed $reason) : ResponseInterface;
}
