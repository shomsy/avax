<?php

declare(strict_types=1);

/**
 * Database shortcuts for global access.
 */

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Connections;
use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\EntityLifecycleDsl;
use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\QueryLifecycleDsl;
use Avax\Components\DataStack\Database\System\Capabilities\Lifecycle\TransactionLifecycleDsl;

if (! function_exists('connection')) {
    /**
     * Retrieves a PDO database connection.
     *
     *
     * @throws RuntimeException
     */
    function connection(string|null $connectionName = null) : PDO
    {
        /** @var Connections $connections */
        $connections = app(Connections::class);

        if (! $connections instanceof Connections) {
            throw new RuntimeException('Database connection service is not registered in DI container.');
        }

        return $connections->pdo($connectionName);
    }
}

if (! function_exists('onEntity')) {
    /**
     * Register entity lifecycle listeners for a specific entity class.
     *
     * Usage:
     *   onEntity(User::class)
     *       ->creating(ValidateUser::class)
     *       ->created(EmitUserRegistered::class);
     *
     * This is a declaration API only. No execution happens during registration.
     * Listeners compile into the CompiledDatabaseLifecycleRegistry at boot time.
     */
    function onEntity(string $entityClass): EntityLifecycleDsl
    {
        return new EntityLifecycleDsl(entityClass: $entityClass);
    }
}

if (! function_exists('onQuery')) {
    /**
     * Register query lifecycle/telemetry listeners.
     *
     * Usage:
     *   onQuery()
     *       ->executed(RecordQueryTelemetry::class)
     *       ->slow(ReportSlowQuery::class, thresholdMs: 100);
     *
     * Query lifecycle is telemetry/observability first.
     * SQL and bindings are redacted by default.
     * This is a declaration API only.
     */
    function onQuery(): QueryLifecycleDsl
    {
        return new QueryLifecycleDsl();
    }
}

if (! function_exists('onTransaction')) {
    /**
     * Register transaction lifecycle listeners.
     *
     * Usage:
     *   onTransaction()
     *       ->afterCommit(PublishOutboxMessages::class)
     *       ->afterRollback(ClearPendingEvents::class);
     *
     * afterCommit runs only after successful outermost commit.
     * afterRollback runs on rollback.
     * This is a declaration API only.
     */
    function onTransaction(): TransactionLifecycleDsl
    {
        return new TransactionLifecycleDsl();
    }
}
