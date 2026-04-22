<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\QueryBuilder\Execution;

use Avax\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Database\System\Capabilities\Telemetry\Events\QueryExecuted;
use Avax\Database\System\Capabilities\QueryBuilder\DTO\ExecutionResult;
use Avax\Database\System\Capabilities\QueryBuilder\Exceptions\QueryException;
use Avax\Database\System\Capabilities\Telemetry\Support\ExecutionScope;
use PDO;
use Random\RandomException;
use SensitiveParameter;
use Throwable;

/**
 * PDO-backed executor for queries and mutations with telemetry and binding redaction support.
 */
final readonly class PDOExecutor implements ExecutorInterface
{
    private string             $connectionName;
    private EventBus|null      $eventBus;
    private DatabaseConnection $connection;

    public function __construct(
        DatabaseConnection $connection,
        EventBus|null      $eventBus = null,
        string             $connectionName = 'default'
    )
    {
        $this->connection     = $connection;
        $this->eventBus       = $eventBus;
        $this->connectionName = $connectionName;
    }

    /**
     * Execute a "Read" query (SELECT) and get the rows back.
     */
    public function query(
        string                      $sql,
        #[SensitiveParameter] array $bindings = [],
        ExecutionScope|null         $scope = null
    ) : array
    {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $statement->execute(params: $bindings);
            $results = $statement->fetchAll();

            $this->dispatch(
                sql           : $sql,
                bindings      : $bindings,
                start         : $start,
                scope         : $scope,
                redactBindings: $this->shouldRedactBindings()
            );

            return $results;
        } catch (Throwable $e) {
            throw new QueryException(
                message    : 'Query execution failed: ' . $e->getMessage(),
                sql        : $sql,
                rawBindings: $bindings,
                previous   : $e
            );
        }
    }

    private function getPdo() : PDO
    {
        return $this->connection->getConnection();
    }

    /**
     * Execute a "Change" query (INSERT/UPDATE/DELETE/DDL).
     */
    public function execute(
        string                      $sql,
        #[SensitiveParameter] array $bindings = [],
        ExecutionScope|null         $scope = null
    ) : ExecutionResult
    {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $success   = $statement->execute(params: $bindings);

            $this->dispatch(
                sql           : $sql,
                bindings      : $bindings,
                start         : $start,
                scope         : $scope,
                redactBindings: $this->shouldRedactBindings()
            );

            return new ExecutionResult(
                success     : $success,
                affectedRows: $statement->rowCount(),
                lastInsertId: $this->resolveLastInsertId(sql: $sql)
            );
        } catch (Throwable $e) {
            throw new QueryException(
                message    : 'Execution failed: ' . $e->getMessage(),
                sql        : $sql,
                rawBindings: $bindings,
                previous   : $e
            );
        }
    }

    /**
     * @throws RandomException
     */
    private function dispatch(
        string                      $sql,
        #[SensitiveParameter] array $bindings,
        float                       $start,
        ExecutionScope|null         $scope = null,
        bool                        $redactBindings = true
    ) : void
    {
        if ($this->eventBus === null) {
            return;
        }

        $correlationId = $scope?->correlationId ?? ('ctx_' . bin2hex(string: random_bytes(length: 4)));

        $this->eventBus->dispatch(event: new QueryExecuted(
                                             sql           : $sql,
                                             bindings      : $bindings,
                                             timeMs        : (microtime(as_float: true) - $start) * 1000,
                                             connectionName: $this->connectionName,
                                             correlationId : $correlationId,
                                             redactBindings: $redactBindings
                                         ));
    }

    private function shouldRedactBindings() : bool
    {
        $flag = getenv(name: 'DB_LOG_BINDINGS') ?: 'redacted';

        return strtolower(string: $flag) !== 'raw';
    }

    public function getDriverName() : string
    {
        return $this->getPdo()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);
    }

    private function resolveLastInsertId(string $sql) : int|string|null
    {
        if (preg_match(pattern: '/^\s*insert\b/i', subject: $sql) !== 1) {
            return null;
        }

        try {
            $lastInsertId = $this->getPdo()->lastInsertId();

            return $lastInsertId === false || $lastInsertId === '' ? null : $lastInsertId;
        } catch (Throwable) {
            return null;
        }
    }
}
