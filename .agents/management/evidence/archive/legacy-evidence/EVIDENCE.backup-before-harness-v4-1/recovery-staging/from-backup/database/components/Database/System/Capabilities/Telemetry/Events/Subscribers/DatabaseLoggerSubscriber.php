<?php

declare(strict_types=1);

namespace components\Database\System\Capabilities\Telemetry\Events\Subscribers;

use components\Database\System\Capabilities\Telemetry\Config\Config;
use components\Database\System\Capabilities\Telemetry\Events\EventSubscriberInterface;
use components\Database\System\Capabilities\Telemetry\Events\QueryExecuted;
use Psr\Log\LoggerInterface;

/**
 * Subscriber that logs query execution events to a Psr\Log\LoggerInterface.
 */
final readonly class DatabaseLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger, private Config $config) {}

    public function getSubscribedEvents() : array
    {
        return [
            QueryExecuted::class => 'handleQueryExecuted',
        ];
    }

    /**
     * Handle the QueryExecuted event.
     *
     * @param QueryExecuted $event The event containing query execution details.
     */
    public function handleQueryExecuted(QueryExecuted $event) : void
    {
        $this->logger->info(message: 'Query executed', context: [
            'sql'         => $event->sql,
            'bindings'    => $event->bindings,
            'duration_ms' => $event->timeMs,
            'connection'  => $event->connectionName,
            'trace_id'    => $event->correlationId,
        ]);
    }
}
