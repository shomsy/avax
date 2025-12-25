<?php

declare(strict_types=1);

namespace Avax\Database\Events;

/**
 * Event signaled when a new database connection is successfully opened.
 */
final readonly class ConnectionOpened extends Event
{
    public function __construct(public string $connectionName)
    {
        parent::__construct();
    }
}
