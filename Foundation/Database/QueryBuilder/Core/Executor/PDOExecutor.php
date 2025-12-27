<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\Core\Executor;

use Avax\Database\Connection\Contracts\DatabaseConnection;
use Avax\Database\QueryBuilder\DTO\ExecutionResult;
use Avax\Database\QueryBuilder\Exceptions\QueryException;
use Avax\Database\Support\ExecutionScope;
use Avax\Database\Events\EventBus;
use Avax\Database\Events\QueryExecuted;
use PDO;
use SensitiveParameter;
use Throwable;

/**
 * PDO-backed executor for queries and mutations with telemetry and binding redaction support.
 */
final readonly class PDOExecutor implements ExecutorInterface
{
    /**
     * @param DatabaseConnection $connection
     * @param EventBus|null      $eventBus
     * @param string             $connectionName
     */
    public function __construct(
        private DatabaseConnection $connection,
        private EventBus|null      $eventBus = null,
        private string             $connectionName = 'default'
    ) {}

    /**
     * Execute a "Read" query (SELECT) and get the rows back.
     */
    public function query(
        string $sql,
        #[SensitiveParameter] array $bindings = [],
        ExecutionScope|null $scope = null
    ): array {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $statement->execute(params: $bindings);
            $results = $statement->fetchAll();

            $this->dispatch(
                sql: $sql,
                bindings: $bindings,
                start: $start,
                scope: $scope,
                redactBindings: $this->shouldRedactBindings()
            );

            return $results;
        } catch (Throwable $e) {
            throw new QueryException(
                message: "Query execution failed: " . $e->getMessage(),
                sql: $sql,
                rawBindings: $bindings,
                previous: $e
            );
        }
    }

    private function getPdo(): PDO
    {
        return $this->connection->getConnection();
    }

    /**
     * Execute a "Change" query (INSERT/UPDATE/DELETE/DDL).
     */
    public function execute(
        string $sql,
        #[SensitiveParameter] array $bindings = [],
        ExecutionScope|null $scope = null
    ): ExecutionResult {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $success = $statement->execute(params: $bindings);

            $this->dispatch(
                sql: $sql,
                bindings: $bindings,
                start: $start,
                scope: $scope,
                redactBindings: $this->shouldRedactBindings()
            );

            return new ExecutionResult(
                success: $success,
                affectedRows: $statement->rowCount()
            );
        } catch (Throwable $e) {
            throw new QueryException(
                message: "Execution failed: " . $e->getMessage(),
                sql: $sql,
                rawBindings: $bindings,
                previous: $e
            );
        }
    }

    private function dispatch(
        string                      $sql,
        #[SensitiveParameter] array $bindings,
        float                       $start,
        ExecutionScope|null         $scope = null,
        bool                        $redactBindings = true
    ): void {
        if ($this->eventBus === null) {
            return;
        }

        $correlationId = $scope?->correlationId ?? ('ctx_' . bin2hex(string: random_bytes(length: 4)));

        $this->eventBus->dispatch(new QueryExecuted(
            sql: $sql,
            bindings: $bindings,
            timeMs: (microtime(as_float: true) - $start) * 1000,
            connectionName: $this->connectionName,
            correlationId: $correlationId,
            redactBindings: $redactBindings
        ));
    }

    private function shouldRedactBindings(): bool
    {
        $flag = getenv('DB_LOG_BINDINGS') ?: 'redacted';

        return strtolower(string: $flag) !== 'raw';
    }

    public function getDriverName(): string
    {
        return $this->getPdo()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);
    }
}
