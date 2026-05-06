<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\CompensateSaga;

enum CompensationStatus: string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case SKIPPED = 'skipped';
}

final readonly class CompensationStepResult
{
    public function __construct(
        public string $stepName,
        public CompensationStatus $status,
        public ?string $error,
        public float $startedAt,
        public ?float $finishedAt,
    ) {
    }

    public static function success(string $stepName, float $startedAt): self
    {
        return new self(
            stepName  : $stepName,
            error     : null,
            status    : CompensationStatus::SUCCESS,
            startedAt : $startedAt,
            finishedAt: microtime(true),
        );
    }

    public static function failure(string $stepName, string $error, float $startedAt): self
    {
        return new self(
            stepName  : $stepName,
            error     : $error,
            status    : CompensationStatus::FAILED,
            startedAt : $startedAt,
            finishedAt: microtime(true),
        );
    }

    public function describeResponsibility(): string
    {
        return 'records compensation step result including status, error, and timing.';
    }

    public function isSuccessful(): bool
    {
        return $this->status === CompensationStatus::SUCCESS;
    }

    public function toMetadata(): array
    {
        return [
            'step_name' => $this->stepName,
            'status' => $this->status->value,
            'error' => $this->error,
            'duration_ms' => $this->durationMs(),
        ];
    }

    public function durationMs(): float
    {
        if ($this->finishedAt === null) {
            return 0;
        }

        return ($this->finishedAt - $this->startedAt) * 1000;
    }
}
