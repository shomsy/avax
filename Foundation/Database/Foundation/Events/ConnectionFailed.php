<?php

declare(strict_types=1);

namespace Avax\Database\Events;

use Override;
use Throwable;

/**
 * Event signaled when a database connection attempt fails.
 */
final readonly class ConnectionFailed extends Event
{
    #[Override]
    public function __construct(
        public string    $connectionName,
        public Throwable $exception
    )
    {
        parent::__construct();
    }
}
