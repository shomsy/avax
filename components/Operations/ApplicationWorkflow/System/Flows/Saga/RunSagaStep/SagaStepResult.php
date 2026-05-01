<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\RunSagaStep;

use Avax\Components\Operations\ApplicationWorkflow\System\Flows\Saga\DefineSaga\SagaStepDefinition;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class SagaStepResult
{
    public array $output;

    public int $attempt;

    public float $durationMs;

    private function __construct(
        public string             $stepName,
        public bool               $success,
        ?array                    $output = null,
        public ?string            $error = null,
        ?int                      $attempt = null,
        ?float                    $durationMs = null,
        public ?DateTimeImmutable $completedAt = null,
    ) {
        $output           ??= [];
        $attempt          ??= 1;
        $durationMs ??= 0.0;
        $this->output     = $output;
        $this->attempt    = $attempt;
        $this->durationMs = $durationMs;
    }

    public static function success(
        string $stepName,
        ?array $output = null,
        ?int   $attempt = null,
        float $durationMs = 0.0,
    ): self {
        $output ??= [];
        $attempt ??= 1;

        return new self(
            stepName   : $stepName,
            success    : true,
            output     : $output,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable(),
        );
    }

    public static function failure(
        string $stepName,
        string $error,
        ?int $attempt = null,
        float $durationMs = 0.0,
    ): self {
        $attempt ??= 1;

        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable(),
        );
    }

    public function toArray(): array
    {
        return [
            'step_name'   => $this->stepName,
            'success'     => $this->success,
            'output'      => $this->output,
            'error'       => $this->error,
            'attempt'     => $this->attempt,
            'duration_ms' => $this->durationMs,
            'completed_at' => $this->completedAt?->format(format: DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class SagaStepExecutionPolicy
{
    public int $maxRetries;

    public int $retryDelayMs;

    public int $timeoutSeconds;

    public bool $continueOnFailure;

    private function __construct(
        ?int        $maxRetries = null,
        ?int        $retryDelayMs = null,
        ?int        $timeoutSeconds = null,
        ?bool       $continueOnFailure = null,
        public bool $isolationPerStep = true,
    ) {
        $maxRetries             ??= 0;
        $retryDelayMs           ??= 1000;
        $timeoutSeconds         ??= 30;
        $continueOnFailure ??= false;
        $this->maxRetries       = $maxRetries;
        $this->retryDelayMs     = $retryDelayMs;
        $this->timeoutSeconds   = $timeoutSeconds;
        $this->continueOnFailure = $continueOnFailure;
    }

    public static function default(): self
    {
        return new self(
            maxRetries    : 0,
            retryDelayMs  : 1000,
            timeoutSeconds: 30,
        );
    }

    public static function fromStep(SagaStepDefinition $sagaStepDefinition) : self
    {
        return new self(
            maxRetries    : $sagaStepDefinition->maxRetries,
            retryDelayMs  : $sagaStepDefinition->retryDelayMs,
            timeoutSeconds: $sagaStepDefinition->timeoutSeconds,
        );
    }

    public function withRetries(int $retries): self
    {
        return new self(
            maxRetries       : $retries,
            retryDelayMs     : $this->retryDelayMs,
            timeoutSeconds   : $this->timeoutSeconds,
            continueOnFailure: $this->continueOnFailure,
            isolationPerStep : $this->isolationPerStep,
        );
    }

    public function canRetry(int $currentAttempt): bool
    {
        return $currentAttempt < $this->maxRetries;
    }

    public function toArray(): array
    {
        return [
            'max_retries'        => $this->maxRetries,
            'retry_delay_ms'     => $this->retryDelayMs,
            'timeout_seconds'    => $this->timeoutSeconds,
            'continue_on_failure' => $this->continueOnFailure,
            'isolation_per_step' => $this->isolationPerStep,
        ];
    }
}
