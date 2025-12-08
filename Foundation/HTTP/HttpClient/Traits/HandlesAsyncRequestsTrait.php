<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Traits;

use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\Utils;

trait HandlesAsyncRequestsTrait
{
    /**
     * Generates asynchronous requests for multiple endpoints.
     *
     * @param array $urls The list of URLs to fetch data from.
     *
     * @return array An array of promises for the asynchronous requests.
     * @throws \Exception
     * @throws \Exception
     */
    public function createAsyncRequests(array $urls) : array
    {
        $promises = [];
        foreach ($urls as $endpoint => $url) {
            $promises[$endpoint] = $this->httpClient->requestAsync(method: 'GET', uri: $url);
        }

        return $promises;
    }

    /**
     * Settles the promises for multiple requests and processes their results.
     *
     * @param array $promises The list of promises to settle.
     *
     * @return PromiseInterface A promise that resolves with processed results.
     */
    public function settlePromises(array $promises) : PromiseInterface
    {
        return Utils::settle(promises: $promises)->then(
            onFulfilled: fn(array $results) => $this->processPromisesResults(results: $results),
        );
    }

    /**
     * Processes the results of each settled promise.
     *
     * @param array $results The array of results from settled promises.
     *
     * @return array The array of processed responses.
     */
    private function processPromisesResults(array $results) : array
    {
        $aggregatedResponses = [];
        foreach ($results as $endpoint => $result) {
            $aggregatedResponses[$endpoint] = $this->processSinglePromiseResult($endpoint, $result);
        }

        return $aggregatedResponses;
    }
}
