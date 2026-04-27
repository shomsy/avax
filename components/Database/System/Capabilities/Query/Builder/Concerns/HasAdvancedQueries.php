<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Query\Builder\Concerns;

use components\Database\System\Capabilities\Query\State\QueryState;

/**
 * Adds support for CTEs and Window Functions.
 */
trait HasAdvancedQueries
{
    /**
     * Add a recursive Common Table Expression (CTE) to the query.
     */
    public function withRecursive(string $name, mixed $query) : static
    {
        return $this->with($name, $query, true);
    }

    /**
     * Add a Common Table Expression (CTE) to the query.
     */
    public function with(string $name, mixed $query, bool $recursive = false) : static
    {
        return clone(object: $this, withProperties: [
            "state" => $this->state->withCte($name, $query, $recursive)
        ]);
    }

    /**
     * Add a window function definition to the query.
     */
    public function over(string $name, mixed $window) : static
    {
        return clone(object: $this, withProperties: [
            "state" => $this->state->withWindow($name, $window)
        ]);
    }
}
