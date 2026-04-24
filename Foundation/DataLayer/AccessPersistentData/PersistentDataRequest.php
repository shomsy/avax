<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use InvalidArgumentException;

enum PersistentDataOperation: string
{
    case SELECT   = 'SELECT';
    case INSERT   = 'INSERT';
    case UPDATE   = 'UPDATE';
    case DELETE   = 'DELETE';
    case CALL     = 'CALL';
    case BEGIN    = 'BEGIN';
    case COMMIT   = 'COMMIT';
    case ROLLBACK = 'ROLLBACK';
}

final readonly class PersistentDataRequest
{
    public string                  $sql;
    public PersistentDataOperation $operation;
    public array                   $bindings;
    public ?string                 $connectionName;
    public ?int                    $fetchMode;
    public ?int                    $fetchCtorArg1;
    public ?string                 $fetchCtorArg2;
    public bool                    $useTransaction;
    public ?int                    $transactionIsolation;
    public ?int                    $timeout;
    public array                   $options;

    private function __construct(
        string                  $sql,
        PersistentDataOperation $operation,
        array                   $bindings = [],
        ?string                 $connectionName = null,
        ?int                    $fetchMode = null,
        ?int                    $fetchCtorArg1 = null,
        ?string                 $fetchCtorArg2 = null,
        bool                    $useTransaction = false,
        ?int                    $transactionIsolation = null,
        ?int                    $timeout = null,
        array                   $options = []
    )
    {
        $this->sql                  = $sql;
        $this->operation            = $operation;
        $this->bindings             = $bindings;
        $this->connectionName       = $connectionName;
        $this->fetchMode            = $fetchMode;
        $this->fetchCtorArg1        = $fetchCtorArg1;
        $this->fetchCtorArg2        = $fetchCtorArg2;
        $this->useTransaction       = $useTransaction;
        $this->transactionIsolation = $transactionIsolation;
        $this->timeout              = $timeout;
        $this->options              = $options;
    }

    public static function create(
        string $sql,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        if (empty(trim($sql))) {
            throw new InvalidArgumentException('SQL cannot be empty.');
        }

        $operation = self::detectOperation($sql);

        return new self(
            sql                 : $sql,
            operation           : $operation,
            bindings            : $bindings,
            connectionName      : $options['connection'] ?? null,
            fetchMode           : $options['fetch_mode'] ?? null,
            fetchCtorArg1       : $options['fetch_arg1'] ?? null,
            fetchCtorArg2       : $options['fetch_arg2'] ?? null,
            useTransaction      : $options['transaction'] ?? false,
            transactionIsolation: $options['isolation'] ?? null,
            timeout             : $options['timeout'] ?? null,
            options             : $options
        );
    }

    public static function select(
        string $sql,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        return self::create($sql, $bindings, array_merge($options, ['operation' => 'SELECT']));
    }

    public static function insert(
        string $table,
        array  $values,
        array  $options = []
    ) : self
    {
        $columns      = array_keys($values);
        $placeholders = array_map(fn ($i) => ":{$i}", array_keys($values));
        $sql          = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            implode(', ', $columns),
            implode(', ', $placeholders)
        );

        return self::create($sql, $values, array_merge($options, ['operation' => 'INSERT']));
    }

    public static function update(
        string $table,
        array  $values,
        string $where,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        $set = implode(', ', array_map(fn ($col) => "{$col} = :{$col}", array_keys($values)));
        $sql = sprintf('UPDATE %s SET %s WHERE %s', $table, $set, $where);

        $allBindings = array_merge($values, $bindings);

        return self::create($sql, $allBindings, array_merge($options, ['operation' => 'UPDATE']));
    }

    public static function delete(
        string $table,
        string $where,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);

        return self::create($sql, $bindings, array_merge($options, ['operation' => 'DELETE']));
    }

    public static function call(
        string $procedure,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        $sql = sprintf('CALL %s(%s)', $procedure, implode(', ', array_keys($bindings)));

        return self::create($sql, $bindings, array_merge($options, ['operation' => 'CALL']));
    }

    public static function raw(
        string $sql,
        array  $bindings = [],
        array  $options = []
    ) : self
    {
        return self::create($sql, $bindings, $options);
    }

    private static function detectOperation(string $sql) : PersistentDataOperation
    {
        $normalized = preg_replace('/\s+/', ' ', trim($sql));

        if (preg_match('/^SELECT\s/i', $normalized)) {
            return PersistentDataOperation::SELECT;
        }
        if (preg_match('/^INSERT\s/i', $normalized)) {
            return PersistentDataOperation::INSERT;
        }
        if (preg_match('/^UPDATE\s/i', $normalized)) {
            return PersistentDataOperation::UPDATE;
        }
        if (preg_match('/^DELETE\s/i', $normalized)) {
            return PersistentDataOperation::DELETE;
        }
        if (preg_match('/^(CALL|BEGIN|COMMIT|ROLLBACK)\s/i', $normalized)) {
            return PersistentDataOperation::from(trim($normalized));
        }

        return PersistentDataOperation::SELECT;
    }

    public function withSql(string $sql) : self
    {
        return new self(
            sql                 : $sql,
            operation           : $this->operation,
            bindings            : $this->bindings,
            connectionName      : $this->connectionName,
            fetchMode           : $this->fetchMode,
            fetchCtorArg1       : $this->fetchCtorArg1,
            fetchCtorArg2       : $this->fetchCtorArg2,
            useTransaction      : $this->useTransaction,
            transactionIsolation: $this->transactionIsolation,
            timeout             : $this->timeout,
            options             : $this->options
        );
    }

    public function withBindings(array $bindings) : self
    {
        return new self(
            sql                 : $this->sql,
            operation           : $this->operation,
            bindings            : $bindings,
            connectionName      : $this->connectionName,
            fetchMode           : $this->fetchMode,
            fetchCtorArg1       : $this->fetchCtorArg1,
            fetchCtorArg2       : $this->fetchCtorArg2,
            useTransaction      : $this->useTransaction,
            transactionIsolation: $this->transactionIsolation,
            timeout             : $this->timeout,
            options             : $this->options
        );
    }

    public function withConnection(?string $name) : self
    {
        return new self(
            sql                 : $this->sql,
            operation           : $this->operation,
            bindings            : $this->bindings,
            connectionName      : $name,
            fetchMode           : $this->fetchMode,
            fetchCtorArg1       : $this->fetchCtorArg1,
            fetchCtorArg2       : $this->fetchCtorArg2,
            useTransaction      : $this->useTransaction,
            transactionIsolation: $this->transactionIsolation,
            timeout             : $this->timeout,
            options             : $this->options
        );
    }

    public function withFetchMode(?int $mode, ?int $arg1 = null, ?string $arg2 = null) : self
    {
        return new self(
            sql                 : $this->sql,
            operation           : $this->operation,
            bindings            : $this->bindings,
            connectionName      : $this->connectionName,
            fetchMode           : $mode,
            fetchCtorArg1       : $arg1,
            fetchCtorArg2       : $arg2,
            useTransaction      : $this->useTransaction,
            transactionIsolation: $this->transactionIsolation,
            timeout             : $this->timeout,
            options             : $this->options
        );
    }

    public function isSelect() : bool
    {
        return $this->operation === PersistentDataOperation::SELECT;
    }

    public function isWrite() : bool
    {
        return in_array($this->operation, [
            PersistentDataOperation::INSERT,
            PersistentDataOperation::UPDATE,
            PersistentDataOperation::DELETE,
        ],              true);
    }

    public function hasNamedBindings() : bool
    {
        return ! empty(preg_grep('/^:/', $this->bindings));
    }

    public function hasPositionalBindings() : bool
    {
        return ! empty(preg_grep('/^\?/', array_keys($this->bindings)));
    }

    public function estimatedRows() : int
    {
        if (! $this->isSelect()) {
            return 0;
        }

        if (preg_match('/LIMIT\s+(\d+)/i', $this->sql, $matches)) {
            return (int) $matches[1];
        }

        return -1;
    }
}