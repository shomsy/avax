<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use PDO;
use PDOStatement;
use Throwable;

final readonly class ExecuteRawDataQuery
{
    private const DEFAULT_TIMEOUT    = 30;
    private const DEFAULT_FETCH_MODE = PDO::FETCH_ASSOC;

    public function __construct(private UseDatabaseRuntime $useDatabaseRuntime) {}

    public function execute(PersistentDataRequest $request) : PersistentDataResult
    {
        $startTime = microtime(true);
        $pdo       = null;

        try {
            $pdo = $this->useDatabaseRuntime->connection($request->connectionName);

            if ($request->timeout !== null) {
                $pdo->setAttribute(PDO::ATTR_TIMEOUT, $request->timeout);
            }

            if ($request->useTransaction && $request->transactionIsolation !== null) {
                $pdo->exec(sprintf('SET TRANSACTION ISOLATION LEVEL %s', $this->isolationLevelToSql($request->transactionIsolation)));
                $pdo->beginTransaction();
            }

            $sql       = $this->prepareSql($request);
            $statement = $pdo->prepare($sql);
            $statement = $this->configureStatement($statement, $request);

            $bound = $statement->execute($this->normalizeBindings($request->bindings));

            if (! $bound) {
                $error = $statement->errorInfo();
                throw new PersistentDataFailure(
                    sprintf('SQL execution failed: %s', $error[2] ?? 'Unknown error'),
                    $error[0] ?? 'HY000',
                    $error[1] ?? 0
                );
            }

            $rows        = [];
            $columnNames = [];
            $rowCount    = 0;

            if ($request->isSelect() || $request->operation === PersistentDataOperation::CALL) {
                $rows     = $statement->fetchAll();
                $rowCount = count($rows);
                if (! empty($rows)) {
                    $columnNames = array_keys($rows[0]);
                }
            }

            $affectedRows = $statement->rowCount();
            $lastInsertId = $pdo->lastInsertId();

            $warnings = $this->fetchWarnings($statement);

            if ($request->useTransaction && $pdo->inTransaction()) {
                if ($request->operation === PersistentDataOperation::INSERT ||
                    $request->operation === PersistentDataOperation::UPDATE ||
                    $request->operation === PersistentDataOperation::DELETE) {
                    $pdo->commit();
                } else {
                    $pdo->rollBack();
                }
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            return PersistentDataResult::success(
                operation      : $request->operation,
                rows           : $rows,
                rowCount       : $rowCount,
                affectedRows   : $affectedRows,
                executionTimeMs: $executionTime,
                lastInsertId   : $lastInsertId,
                generatedSql   : $sql,
                warnings       : $warnings
            );

        } catch (Throwable $e) {
            if ($pdo !== null && $pdo->inTransaction()) {
                $pdo->rollBack();
            }

            $executionTime = (microtime(true) - $startTime) * 1000;

            return PersistentDataResult::failure(
                $request->operation,
                new PersistentDataFailure(
                             $e->getMessage(),
                             (string) ($e->getCode() ?? 'HY000'),
                             $e->getLine(),
                             $e,
                    request: $request
                ),
                $executionTime
            );
        }
    }

    private function prepareSql(PersistentDataRequest $request) : string
    {
        $sql = $request->sql;

        if ($request->hasNamedBindings()) {
            return $sql;
        }

        if ($request->hasPositionalBindings()) {
            return $sql;
        }

        return $sql;
    }

    private function normalizeBindings(array $bindings) : array
    {
        $normalized = [];

        foreach ($bindings as $key => $value) {
            if (is_bool($value)) {
                $normalized[$key] = $value ? 1 : 0;
            } elseif ($value instanceof \DateTimeInterface) {
                $normalized[$key] = $value->format('Y-m-d H:i:s');
            } elseif (is_array($value)) {
                $normalized[$key] = json_encode($value);
            } else {
                $normalized[$key] = $value;
            }
        }

        return $normalized;
    }

    private function configureStatement(PDOStatement $statement, PersistentDataRequest $request) : PDOStatement
    {
        if ($request->fetchMode !== null) {
            $statement->setFetchMode($request->fetchMode, $request->fetchCtorArg1, $request->fetchCtorArg2);
        } else {
            $statement->setFetchMode(self::DEFAULT_FETCH_MODE);
        }

        return $statement;
    }

    private function isolationLevelToSql(int $level) : string
    {
        return match ($level) {
            PDO::TRANSACTION_READ_UNCOMMITTED => 'READ UNCOMMITTED',
            PDO::TRANSACTION_READ_COMMITTED   => 'READ COMMITTED',
            PDO::TRANSACTION_REPEATABLE_READ  => 'REPEATABLE READ',
            PDO::TRANSACTION_SERIALIZABLE     => 'SERIALIZABLE',
            default                           => 'REPEATABLE READ',
        };
    }

    private function fetchWarnings(PDOStatement $statement) : array
    {
        $warnings = [];

        if (method_exists($statement, 'getWarnings')) {
            foreach ($statement->getWarnings() as $warning) {
                $warnings[] = [
                    'level'   => $warning->Level ?? 'Warning',
                    'message' => $warning->Message ?? (string) $warning,
                    'code'    => $warning->Code ?? 0,
                ];
            }
        }

        return $warnings;
    }
}