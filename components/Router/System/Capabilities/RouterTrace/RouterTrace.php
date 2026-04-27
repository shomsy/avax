<?php

declare(strict_types=1);

namespace Avax\Components\Router\System\Capabilities\RouterTrace;

/**
 * Provides tracing and debugging capabilities for the Router resolution process.
 */
final class RouterTrace
{
    private array $events = [];

    public function log(string $event, array $context = []) : void
    {
        $this->events[] = [
            'event'     => $event,
            'context'   => $context,
            'timestamp' => microtime(as_float: true),
        ];
    }

    public function getEvents() : array
    {
        return $this->events;
    }

    public function clear() : void
    {
        $this->events = [];
    }
}
