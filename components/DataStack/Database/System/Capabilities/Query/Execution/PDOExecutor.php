<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\Execution;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\QueryException;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\EventBus;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Events\QueryExecuted;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use Override;
use PDO;
use Random\RandomException;
use SensitiveParameter;
use Throwable;

/**
 * PDO-backed executor for queries and mutations with telemetry and binding redaction support.
 */
final readonly class PDOExecutor implements ExecutorInterface
{
    public function __construct(private DatabaseConnection $databaseConnection, private ?EventBus $eventBus = null, private string $connectionName = 'default') {}

    /**
     * Execute a "Read" query (SELECT) and get the rows back.
     */
    #[Override]
    public function query(
        string $sql,
        #[SensitiveParameter]
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): array {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $statement->execute(params: $bindings);
            $results = $statement->fetchAll();

            $this->dispatch(
                sql           : $sql,
                bindings      : $bindings,
                start         : $start,
                redactBindings: $this->shouldRedactBindings(),
                executionScope: $executionScope,
            );

            return $results;
        } catch (Throwable $throwable) {
            throw new QueryException(
                message    : 'Query execution failed: ' . $throwable->getMessage(),
                sql        : $sql,
                rawBindings: $bindings,
                throwable  : $throwable,
            );
        }
    }

    private function getPdo(): PDO
    {
        return $this->databaseConnection->getConnection();
    }

    /**
     * Execute a "Change" query (INSERT/UPDATE/DELETE/DDL).
     */
    #[Override]
    public function execute(
        string $sql,
        #[SensitiveParameter]
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): ExecutionResult {
        $start = microtime(as_float: true);

        try {
            $statement = $this->getPdo()->prepare(query: $sql);
            $success = $statement->execute(params: $bindings);

            $this->dispatch(
                sql           : $sql,
                bindings      : $bindings,
                start         : $start,
                redactBindings: $this->shouldRedactBindings(),
                executionScope: $executionScope,
            );

            return new ExecutionResult(
                success     : $success,
                affectedRows: $statement->rowCount(),
                lastInsertId: $this->resolveLastInsertId(sql: $sql),
            );
        } catch (Throwable $throwable) {
            throw new QueryException(
                message    : 'Execution failed: ' . $throwable->getMessage(),
                sql        : $sql,
                rawBindings: $bindings,
                throwable  : $throwable,
            );
        }
    }

    /**
     * @throws RandomException
     */
    private function dispatch(
        string $sql,
        #[SensitiveParameter]
        array $bindings,
        float $start,
        ?ExecutionScope $executionScope = null,
        bool $redactBindings = true,
    ): void {
        if (! $this->eventBus instanceof EventBus) {
            return;
        }

        $correlationId = $executionScope?->correlationId ?? ('ctx_' . bin2hex(string: random_bytes(length: 4)));

        $this->eventBus->dispatch(event: new QueryExecuted(
            sql           : $sql,
            timeMs        : (microtime(as_float: true) - $start) * 1000,
            connectionName: $this->connectionName,
            correlationId : $correlationId,
            bindings      : $bindings,
            redactBindings: $redactBindings,
        ));
    }

    private function shouldRedactBindings(): bool
    {
        $flag = function_exists(function: 'config')
            ? config(key: 'database.log_bindings', default: 'redacted')
            : 'redacted';

        return strtolower(string: (string) $flag) !== 'raw';
    }

    private function resolveLastInsertId(string $sql): int|string|null
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

    #[Override]
    public function getDriverName(): string
    {
        return $this->getPdo()->getAttribute(attribute: PDO::ATTR_DRIVER_NAME);
    }
}
