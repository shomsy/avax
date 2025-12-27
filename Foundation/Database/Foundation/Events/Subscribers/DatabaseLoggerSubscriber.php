<?php

declare(strict_types=1);

namespace Avax\Database\Events\Subscribers;

use Avax\Database\Config\Config;
use Avax\Database\Events\ConnectionAcquired;
use Avax\Database\Events\ConnectionFailed;
use Avax\Database\Events\ConnectionOpened;
use Avax\Database\Events\EventSubscriberInterface;
use Avax\Database\Events\QueryExecuted;
use Psr\Log\LoggerInterface;

/**
 * Infrastructure observer for logging database activity and lifecycle events.
 *
 * -- intent:
 * Centralizes technical telemetry across the database component for unified 
 * monitoring, security auditing, and performance profiling. It translates 
 * internal signals into standardized PSR-3 log entries.
 *
 * -- invariants:
 * - All log entries must include a correlation ID for request tracing.
 * - Sensitivity policies (redaction) must be enforced based on configuration or event flags.
 * - Critical failures (ConnectionFailed) must be recorded at ERROR level.
 *
 * -- boundaries:
 * - Does NOT perform specialized log aggregation (delegated to the PSR-3 implementation).
 * - Does NOT make performance optimizations based on the signals it observes.
 */
final readonly class DatabaseLoggerSubscriber implements EventSubscriberInterface
{
    /**
     * @param LoggerInterface $logger The PSR-3 logging implementation for outputting telemetry.
     * @param Config|null     $config Optional configuration registry for dynamic control over logging levels and redaction.
     */
    public function __construct(
        private LoggerInterface $logger,
        private Config|null $config = null
    ) {}

    /**
     * Map database signal types to their corresponding handler logic.
     *
     * -- intent:
     * Declare the collection of events this observer is designed to monitor
     * and process.
     *
     * @return array<string, string> Collection mapping Event class names to handler method names.
     */
    public function getSubscribedEvents(): array
    {
        return [
            ConnectionOpened::class   => 'onConnectionOpened',
            ConnectionFailed::class   => 'onConnectionFailed',
            QueryExecuted::class      => 'onQueryExecuted',
            ConnectionAcquired::class => 'onConnectionAcquired',
        ];
    }

    /**
     * Record a log entry when a connection is retrieved from the resource pool.
     *
     * -- intent:
     * Track connection checkout frequency and recycling health (cache hits)
     * for pool optimization monitoring.
     *
     * @param ConnectionAcquired $event The signal payload containing acquisition details.
     * @return void
     */
    public function onConnectionAcquired(ConnectionAcquired $event): void
    {
        $this->logger->info(message: "Database connection acquired", context: [
            'correlation_id' => $event->correlationId,
            'connection'     => $event->connectionName,
            'recycled'       => $event->isRecycled,
            'timestamp'      => $event->timestamp,
        ]);
    }

    /**
     * Record a log entry when a fresh physical connection is established.
     *
     * -- intent:
     * Monitor the creation of new driver instances to detect potential
     * connection "churn" or pool exhaustion.
     *
     * @param ConnectionOpened $event The signal payload containing the new connection data.
     * @return void
     */
    public function onConnectionOpened(ConnectionOpened $event): void
    {
        $this->logger->info(message: "Database connection opened", context: [
            'correlation_id' => $event->correlationId,
            'connection'     => $event->connectionName,
            'timestamp'      => $event->timestamp,
        ]);
    }

    /**
     * Record a critical log entry when a driver negotiation failure occurs.
     *
     * -- intent:
     * Provide immediate visibility and technical context for connection
     * failures to assist in alerting and post-mortem analysis.
     *
     * @param ConnectionFailed $event The signal payload containing the failure exception.
     * @return void
     */
    public function onConnectionFailed(ConnectionFailed $event): void
    {
        $this->logger->error(message: "Database connection failed", context: [
            'correlation_id' => $event->correlationId,
            'connection'     => $event->connectionName,
            'exception'      => $event->exception->getMessage(),
            'timestamp'      => $event->timestamp,
        ]);
    }

    /**
     * Record a detailed log entry upon successful query completion.
     *
     * -- intent:
     * Document query performance and execution signatures while strictly
     * enforcing security redaction policies to prevent data leakage into logs.
     *
     * @param QueryExecuted $event The signal payload containing SQL, bindings, and timing data.
     * @return void
     */
    public function onQueryExecuted(QueryExecuted $event): void
    {
        $shouldRedact = $this->config?->get(key: 'logging.redact', default: true) ?? true;
        $includeRaw   = $this->config?->get(key: 'logging.include_raw_bindings', default: false) ?? false;

        $this->logger->debug(message: "Query executed", context: [
            'correlation_id' => $event->correlationId,
            'connection'     => $event->connectionName,
            'sql'            => $event->sql,
            'bindings'       => $shouldRedact || $event->bindingsRedacted ? $event->redactedBindings : $event->bindings,
            'raw_bindings'   => $includeRaw && ! $shouldRedact ? $event->rawBindings : '[REDACTED]',
            'time_ms'        => $event->timeMs,
            'timestamp'      => $event->timestamp,
        ]);
    }
}
