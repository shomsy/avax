<?php

declare(strict_types=1);

namespace Avax\DataLayer\InspectDataLayer;

final readonly class TraceDataTransaction
{
    public function __construct(
        public string     $transactionId,
        public float      $startedAt,
        public float|null $finishedAt,
        public array      $operations
    ) {}

    public function describeResponsibility() : string
    {
        return 'traces data transaction including start, finish, and operations.';
    }

    public static function start(string $id) : self
    {
        return new self(
            transactionId: $id,
            startedAt    : microtime(true),
            finishedAt   : null,
            operations   : []
        );
    }

    public function finish() : self
    {
        return new self(
            transactionId: $this->transactionId,
            startedAt    : $this->startedAt,
            finishedAt   : microtime(true),
            operations   : $this->operations
        );
    }

    public function addOperation(string $operation) : self
    {
        return new self(
            transactionId: $this->transactionId,
            startedAt    : $this->startedAt,
            finishedAt   : $this->finishedAt,
            operations   : [...$this->operations, $operation]
        );
    }

    public function durationMs() : float
    {
        if ($this->finishedAt === null) {
            return 0;
        }

        return ($this->finishedAt - $this->startedAt) * 1000;
    }

    public function toMetadata() : array
    {
        return [
            'transaction_id' => $this->transactionId,
            'started_at'     => $this->startedAt,
            'finished_at'    => $this->finishedAt,
            'operations'     => $this->operations,
            'duration_ms'    => $this->durationMs(),
        ];
    }
}