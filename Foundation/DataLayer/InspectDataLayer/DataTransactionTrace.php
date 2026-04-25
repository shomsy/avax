<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class DataTransactionTrace
{
    public function __construct(
        public string     $id,
        public array      $operations,
        public float      $startedAt,
        public float|null $committedAt,
        public float|null $rolledBackAt
    ) {}

    public function describeResponsibility() : string
    {
        return 'records data transaction trace with operations and commit status.';
    }

    public static function create(string $id) : self
    {
        return new self(
            id          : $id,
            operations  : [],
            startedAt   : microtime(true),
            committedAt : null,
            rolledBackAt: null
        );
    }

    public function commit() : self
    {
        return new self(
            id          : $this->id,
            operations  : $this->operations,
            startedAt   : $this->startedAt,
            committedAt : microtime(true),
            rolledBackAt: $this->rolledBackAt
        );
    }

    public function rollback() : self
    {
        return new self(
            id          : $this->id,
            operations  : $this->operations,
            startedAt   : $this->startedAt,
            committedAt : $this->committedAt,
            rolledBackAt: microtime(true)
        );
    }

    public function isCommitted() : bool
    {
        return $this->committedAt !== null;
    }

    public function isRolledBack() : bool
    {
        return $this->rolledBackAt !== null;
    }

    public function toMetadata() : array
    {
        return [
            'id'              => $this->id,
            'operation_count' => count($this->operations),
            'started_at'      => $this->startedAt,
            'committed_at'    => $this->committedAt,
            'rolled_back_at'  => $this->rolledBackAt,
        ];
    }
}