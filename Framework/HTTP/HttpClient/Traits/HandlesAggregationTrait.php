<?php

declare(strict_types=1);

namespace Gemini\HTTP\HttpClient\Traits;

use GuzzleHttp\Promise\PromiseInterface;

trait HandlesAggregationTrait
{
    /**
     * Asynchronously aggregates data from multiple endpoints.
     *
     * @param array $urls The list of URLs to request data from.
     *
     * @return PromiseInterface A promise that resolves with the aggregated results.
     * @throws \Exception
     */
    public function aggregateDataAsynchronously(array $urls) : PromiseInterface
    {
        $this->dataLogger->info(message: 'Starting data aggregation for multiple endpoints.');

        $promises = $this->createAsyncRequests(urls: $urls);

        return $this->settlePromises(promises: $promises);
    }
}
