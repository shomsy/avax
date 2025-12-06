<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Middleware\Policies;

use Avax\HTTP\HttpClient\Config\Middleware\Policies\Concrats\RetryPolicyInterface;

/**
 * Class that aggregates and provides different retry policies.
 */
class RetryPolicies
{
    public function getServerErrorPolicy() : RetryPolicyInterface
    {
        return new ServerErrorRetryPolicy();
    }

    public function getNetworkFailurePolicy() : RetryPolicyInterface
    {
        return new NetworkFailureRetryPolicy();
    }
}
