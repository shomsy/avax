<?php

declare(strict_types=1);

namespace Avax\Database\Connection\Exceptions;

use Avax\Database\Exceptions\DatabaseException;
use Override;

/**
 * Triggered when the database connection pool reaches its maximum configured capacity.
 *
 * -- intent: signal resource exhaustion in high-concurrency environments.
 */
final class PoolLimitReachedException extends DatabaseException
{
    /**
     * Constructor capturing the pool name and configured limit.
     *
     * -- intent: provide clear evidence of resource saturation for scaling decisions.
     *
     * @param string $name  Technical pool identifier
     * @param int    $limit Configured maximum connection count
     */
    #[Override]
    public function __construct(
        private readonly string $name,
        private readonly int    $limit
    )
    {
        parent::__construct(message: "Connection pool [{$name}] reached its limit of {$limit} connections.");
    }

    /**
     * Retrieve the technical name of the saturated pool.
     *
     * -- intent: identify which connection path is blocked.
     *
     * @return string
     */
    public function getPoolName() : string
    {
        return $this->name;
    }

    /**
     * Retrieve the maximum allowed connection count for this pool.
     *
     * -- intent: assist in diagnostic capacity planning.
     *
     * @return int
     */
    public function getLimit() : int
    {
        return $this->limit;
    }
}
