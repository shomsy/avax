<?php

declare(strict_types=1);

namespace Avax\DataLayer\CommitDataChanges;

enum IsolationLevel: string
{
    case READ_UNCOMMITTED = 'READ UNCOMMITTED';
    case READ_COMMITTED   = 'READ COMMITTED';
    case REPEATABLE_READ  = 'REPEATABLE READ';
    case SERIALIZABLE     = 'SERIALIZABLE';
    case SNAPSHOT         = 'SNAPSHOT';

    public function toPdo() : int
    {
        return match ($this) {
            self::READ_UNCOMMITTED => \PDO::TRANSACTION_READ_UNCOMMITTED,
            self::READ_COMMITTED   => \PDO::TRANSACTION_READ_COMMITTED,
            self::REPEATABLE_READ  => \PDO::TRANSACTION_REPEATABLE_READ,
            self::SERIALIZABLE     => \PDO::TRANSACTION_SERIALIZABLE,
            self::SNAPSHOT         => \PDO::TRANSACTION_REPEATABLE_READ,
        };
    }

    public static function fromPdo(int $level) : self
    {
        return match ($level) {
            \PDO::TRANSACTION_READ_UNCOMMITTED => self::READ_UNCOMMITTED,
            \PDO::TRANSACTION_READ_COMMITTED   => self::READ_COMMITTED,
            \PDO::TRANSACTION_REPEATABLE_READ  => self::REPEATABLE_READ,
            \PDO::TRANSACTION_SERIALIZABLE     => self::SERIALIZABLE,
            default                            => self::REPEATABLE_READ,
        };
    }
}

final readonly class DataTransaction
{
    public string         $id;
    public ?string        $connectionName;
    public IsolationLevel $isolationLevel;
    public bool           $isActive;
    public bool           $isCommitted;
    public bool           $isRolledBack;
    public float          $startedAt;
    public ?float         $finishedAt;
    public ?float         $durationMs;
    public array          $affectedRows;
    public array          $sideEffects;
    public ?string        $savepointName;

    private function __construct(
        string         $id,
        ?string        $connectionName,
        IsolationLevel $isolationLevel,
        bool|null  $isActive = null,
        bool|null  $isCommitted = null,
        bool|null  $isRolledBack = null,
        float|null $startedAt = null,
        ?float         $finishedAt = null,
        ?float         $durationMs = null,
        array|null $affectedRows = null,
        array|null $sideEffects = null,
        ?string        $savepointName = null
    )
    {
        $isActive     ??= false;
        $isCommitted  ??= false;
        $isRolledBack ??= false;
        $startedAt    ??= 0.0;
        $affectedRows ??= [];
        $sideEffects  ??= [];
        $this->id             = $id;
        $this->connectionName = $connectionName;
        $this->isolationLevel = $isolationLevel;
        $this->isActive       = $isActive;
        $this->isCommitted    = $isCommitted;
        $this->isRolledBack   = $isRolledBack;
        $this->startedAt      = $startedAt;
        $this->finishedAt     = $finishedAt;
        $this->durationMs     = $durationMs;
        $this->affectedRows   = $affectedRows;
        $this->sideEffects    = $sideEffects;
        $this->savepointName  = $savepointName;
    }

    public static function started(
        string         $id,
        ?string        $connectionName,
        IsolationLevel $isolationLevel,
        ?string        $savepointName = null
    ) : self
    {
        return new self(
            id            : $id,
            connectionName: $connectionName,
            isolationLevel: $isolationLevel,
            isActive      : true,
            startedAt     : microtime(true),
            savepointName : $savepointName
        );
    }

    public static function committed(
        string         $id,
        ?string        $connectionName,
        IsolationLevel $isolationLevel,
        array          $affectedRows,
        float          $startedAt
    ) : self
    {
        $now = microtime(true);

        return new self(
            id            : $id,
            connectionName: $connectionName,
            isolationLevel: $isolationLevel,
            isActive      : false,
            isCommitted   : true,
            startedAt     : $startedAt,
            finishedAt    : $now,
            durationMs    : ($now - $startedAt) * 1000,
            affectedRows  : $affectedRows
        );
    }

    public static function rolledBack(
        string         $id,
        ?string        $connectionName,
        IsolationLevel $isolationLevel,
        array          $affectedRows,
        float          $startedAt
    ) : self
    {
        $now = microtime(true);

        return new self(
            id            : $id,
            connectionName: $connectionName,
            isolationLevel: $isolationLevel,
            isActive      : false,
            isRolledBack  : true,
            startedAt     : $startedAt,
            finishedAt    : $now,
            durationMs    : ($now - $startedAt) * 1000,
            affectedRows  : $affectedRows
        );
    }

    public function withSideEffect(string $key, mixed $value) : self
    {
        return new self(
            id            : $this->id,
            connectionName: $this->connectionName,
            isolationLevel: $this->isolationLevel,
            isActive      : $this->isActive,
            isCommitted   : $this->isCommitted,
            isRolledBack  : $this->isRolledBack,
            startedAt     : $this->startedAt,
            finishedAt    : $this->finishedAt,
            durationMs    : $this->durationMs,
            affectedRows  : $this->affectedRows,
            sideEffects   : array_merge($this->sideEffects, [$key => $value]),
            savepointName : $this->savepointName
        );
    }

    public function addAffectedRows(string $statement, int $rows) : self
    {
        return new self(
            id            : $this->id,
            connectionName: $this->connectionName,
            isolationLevel: $this->isolationLevel,
            isActive      : $this->isActive,
            isCommitted   : $this->isCommitted,
            isRolledBack  : $this->isRolledBack,
            startedAt     : $this->startedAt,
            finishedAt    : $this->finishedAt,
            durationMs    : $this->durationMs,
            affectedRows  : array_merge($this->affectedRows, [$statement => $rows]),
            sideEffects   : $this->sideEffects,
            savepointName : $this->savepointName
        );
    }

    public function isSuccessful() : bool
    {
        return $this->isCommitted && ! $this->isRolledBack;
    }

    public function totalAffectedRows() : int
    {
        return array_sum($this->affectedRows);
    }

    public function toMetadata() : array
    {
        return [
            'id'             => $this->id,
            'connection'     => $this->connectionName,
            'isolation'      => $this->isolationLevel->value,
            'started_at'     => $this->startedAt,
            'finished_at'    => $this->finishedAt,
            'duration_ms'    => $this->durationMs,
            'is_active'      => $this->isActive,
            'is_committed'   => $this->isCommitted,
            'is_rolled_back' => $this->isRolledBack,
            'affected_rows'  => $this->affectedRows,
            'total_affected' => $this->totalAffectedRows(),
            'side_effects'   => $this->sideEffects,
            'savepoint'      => $this->savepointName,
        ];
    }
}