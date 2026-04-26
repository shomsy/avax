<?php

declare(strict_types=1);

namespace Avax\DataLayer\AccessPersistentData;

use Countable;
use IteratorAggregate;
use Traversable;

final readonly class PersistentDataResult implements Countable, IteratorAggregate
{
    public PersistentDataOperation    $operation;
    public int                        $rowCount;
    public int                        $affectedRows;
    public float                      $executionTimeMs;
    public array                      $rows;
    public array                      $columnNames;
    public string|null                $lastInsertId;
    public string|null                $generatedSql;
    public array                      $warnings;
    public bool                       $success;
    public PersistentDataFailure|null $failure;

    private function __construct(
        PersistentDataOperation    $operation,
        int|null                   $rowCount = null,
        int|null                   $affectedRows = null,
        float|null                 $executionTimeMs = null,
        array|null                 $rows = null,
        array|null                 $columnNames = null,
        string|null                $lastInsertId = null,
        string|null                $generatedSql = null,
        array|null                 $warnings = null,
        bool|null                  $success = null,
        PersistentDataFailure|null $failure = null
    )
    {
        $rowCount              ??= 0;
        $affectedRows          ??= 0;
        $executionTimeMs       ??= 0.0;
        $rows                  ??= [];
        $columnNames           ??= [];
        $warnings              ??= [];
        $success               ??= true;
        $this->operation       = $operation;
        $this->rowCount        = $rowCount;
        $this->affectedRows    = $affectedRows;
        $this->executionTimeMs = $executionTimeMs;
        $this->rows            = $rows;
        $this->columnNames     = $columnNames;
        $this->lastInsertId    = $lastInsertId;
        $this->generatedSql    = $generatedSql;
        $this->warnings        = $warnings;
        $this->success         = $success;
        $this->failure         = $failure;
    }

    public static function success(
        PersistentDataOperation $operation,
        array|null              $rows = null,
        int|null                $rowCount = null,
        int|null                $affectedRows = null,
        float|null              $executionTimeMs = null,
        string|null             $lastInsertId = null,
        string|null             $generatedSql = null,
        array                   $warnings = []
    ) : self
    {
        $rows            ??= [];
        $rowCount        ??= 0;
        $affectedRows    ??= 0;
        $executionTimeMs ??= 0.0;
        $columnNames     = [];
        if (! empty($rows)) {
            $firstRow = reset($rows);
            if (is_array($firstRow)) {
                $columnNames = array_keys($firstRow);
            }
        }

        return new self(
            operation      : $operation,
            rowCount       : $rowCount,
            affectedRows   : $affectedRows,
            executionTimeMs: $executionTimeMs,
            rows           : $rows,
            columnNames    : $columnNames,
            lastInsertId   : $lastInsertId,
            generatedSql   : $generatedSql,
            warnings       : $warnings,
            success        : true,
            failure        : null
        );
    }

    public static function failure(
        PersistentDataOperation $operation,
        PersistentDataFailure   $failure,
        float                   $executionTimeMs = 0.0
    ) : self
    {
        return new self(
            operation      : $operation,
            executionTimeMs: $executionTimeMs,
            success        : false,
            failure        : $failure
        );
    }

    public static function empty(
        PersistentDataOperation $operation,
        float                   $executionTimeMs = 0.0
    ) : self
    {
        return new self(
            operation      : $operation,
            executionTimeMs: $executionTimeMs
        );
    }

    public function first() : array|null
    {
        return $this->rows[0] ?? null;
    }

    public function firstOrFail() : array
    {
        if (empty($this->rows)) {
            throw new PersistentDataFailure(message: 'Expected at least one row but got none.');
        }

        return $this->rows[0];
    }

    public function at(int $index) : array|null
    {
        return $this->rows[$index] ?? null;
    }

    public function atOrFail(int $index) : array
    {
        if (! isset($this->rows[$index])) {
            $msg = sprintf('Expected row at index %d but only %d rows exist.', $index, count($this->rows));
            throw new PersistentDataFailure(message: $msg);
        }

        return $this->rows[$index];
    }

    public function column(string $name) : array
    {
        return array_column($this->rows, $name);
    }

    public function find(string $field, mixed $value) : array|null
    {
        foreach ($this->rows as $row) {
            if (($row[$field] ?? null) === $value) {
                return $row;
            }
        }

        return null;
    }

    public function exists() : bool
    {
        return ! empty($this->rows);
    }

    public function isEmpty() : bool
    {
        return empty($this->rows);
    }

    public function count() : int
    {
        return $this->rowCount;
    }

    public function getIterator() : Traversable
    {
        foreach ($this->rows as $index => $row) {
            yield $index => $row;
        }
    }

    public function toArray() : array
    {
        return $this->rows;
    }

    public function toJson(int $flags = JSON_THROW_ON_ERROR) : string
    {
        return json_encode($this->rows, $flags);
    }
}