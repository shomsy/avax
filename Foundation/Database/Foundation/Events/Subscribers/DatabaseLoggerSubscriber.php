<?php

declare(strict_types=1);

namespace Avax\Database\Events\Subscribers;

use Avax\Database\Events\ConnectionFailed;
use Avax\Database\Events\ConnectionOpened;
use Avax\Database\Events\EventSubscriberInterface;
use Avax\Database\Events\QueryExecuted;
use Psr\Log\LoggerInterface;

/**
 * Event subscriber for logging database operations.
 *
 * -- intent: provide centralized telemetry and debugging for all database activities.
 */
final readonly class DatabaseLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @param LoggerInterface $logger PSR-3 logger instance (injected via DI)
     */
    public function __construct(private LoggerInterface $logger) {}

    /**
     * Register event handlers for database telemetry.
     *
     * @return array<string, string>
     */
    public function getSubscribedEvents() : array
    {
        return [
            ConnectionOpened::class => 'onConnectionOpened',
            ConnectionFailed::class => 'onConnectionFailed',
            QueryExecuted::class    => 'onQueryExecuted',
        ];
    }

    /**
     * Log successful connection establishment.
     */
    public function onConnectionOpened(ConnectionOpened $event) : void
    {
        $this->logger->info(message: "Database connection opened", context: [
            'connection' => $event->connectionName,
            'timestamp'  => $event->timestamp,
        ]);
    }

    /**
     * Log connection failures with full exception details.
     */
    public function onConnectionFailed(ConnectionFailed $event) : void
    {
        $this->logger->error(message: "Database connection failed", context: [
            'connection' => $event->connectionName,
            'exception'  => $event->exception,
            'timestamp'  => $event->timestamp,
        ]);
    }

    /**
     * Log executed queries with performance metrics.
     */
    public function onQueryExecuted(QueryExecuted $event) : void
    {
        $this->logger->debug(message: "Query executed", context: [
            'connection' => $event->connectionName,
            'sql'        => $event->sql,
            'bindings'   => $event->bindings,
            'time_ms'    => $event->timeMs,
            'timestamp'  => $event->timestamp,
        ]);
    }
}
