<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Traits;

use Throwable;

trait HandlesHttpErrorsTrait
{
    /**
     * Logs an error and returns a formatted failure response.
     *
     * @param string $endpoint The endpoint that failed.
     * @param mixed  $reason   The reason for the failure.
     *
     * @return array A structured response with error information.
     */
    public function handleFailure(string $endpoint, mixed $reason) : array
    {
        $errorMessage = $reason instanceof Throwable ? $reason->getMessage() : 'Unknown error';
        $this->dataLogger->error(
            message: 'Error fetching data for ' . $endpoint,
            context: ['error' => $errorMessage],
        );

        return [
            'endpoint' => $endpoint,
            'reason'   => $errorMessage,
        ];
    }
}
