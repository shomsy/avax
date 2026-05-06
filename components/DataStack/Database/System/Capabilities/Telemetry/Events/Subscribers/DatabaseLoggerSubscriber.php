<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\Subscribers;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventSubscriberInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\QueryExecuted;
use Psr\Log\LoggerInterface;

/**
 * Subscriber that logs query execution events to a Psr\Log\LoggerInterface.
 */
final readonly class DatabaseLoggerSubscriber implements EventSubscriberInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            QueryExecuted::class => 'handleQueryExecuted',
        ];
    }

    /**
     * Handle the QueryExecuted event.
     *
     * @param  QueryExecuted  $queryExecuted  The event containing query execution details.
     */
    public function handleQueryExecuted(QueryExecuted $queryExecuted): void
    {
        $this->logger->info(message: 'Query executed', context: [
            'sql' => $queryExecuted->sql,
            'bindings' => $queryExecuted->bindings,
            'duration_ms' => $queryExecuted->timeMs,
            'connection' => $queryExecuted->connectionName,
            'trace_id' => $queryExecuted->correlationId,
        ]);
    }
}
