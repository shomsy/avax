<?php

declare(strict_types=1);

namespace Avax\ApplicationWorkflow\Saga\RunSagaStep;

use Avax\ApplicationWorkflow\Saga\StartSaga\SagaInstance;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use Avax\ApplicationWorkflow\Saga\DefineSaga\SagaDefinition;

final readonly class SagaStepResult
{
    public string              $stepName;
    public bool                $success;
    public array               $output;
    public ?string             $error;
    public int                 $attempt;
    public float               $durationMs;
    public ?\DateTimeImmutable $completedAt;

    private function __construct(
        string              $stepName,
        bool                $success,
        array               $output = [],
        ?string             $error = null,
        int                 $attempt = 1,
        float               $durationMs = 0.0,
        ?\DateTimeImmutable $completedAt = null
    )
    {
        $this->stepName    = $stepName;
        $this->success     = $success;
        $this->output      = $output;
        $this->error       = $error;
        $this->attempt     = $attempt;
        $this->durationMs  = $durationMs;
        $this->completedAt = $completedAt;
    }

    public static function success(
        string $stepName,
        array  $output = [],
        int    $attempt = 1,
        float  $durationMs = 0.0
    ) : self
    {
        return new self(
            stepName   : $stepName,
            success    : true,
            output     : $output,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new \DateTimeImmutable()
        );
    }

    public static function failure(
        string $stepName,
        string $error,
        int    $attempt = 1,
        float  $durationMs = 0.0
    ) : self
    {
        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new \DateTimeImmutable()
        );
    }

    public function toArray() : array
    {
        return [
            'step_name'    => $this->stepName,
            'success'      => $this->success,
            'output'       => $this->output,
            'error'        => $this->error,
            'attempt'      => $this->attempt,
            'duration_ms'  => $this->durationMs,
            'completed_at' => $this->completedAt?->format(\DateTimeInterface::ISO8601),
        ];
    }
}

final readonly class SagaStepExecutionPolicy
{
    public int  $maxRetries;
    public int  $retryDelayMs;
    public int  $timeoutSeconds;
    public bool $continueOnFailure;
    public bool $isolationPerStep;

    private function __construct(
        int  $maxRetries = 0,
        int  $retryDelayMs = 1000,
        int  $timeoutSeconds = 30,
        bool $continueOnFailure = false,
        bool $isolationPerStep = true
    )
    {
        $this->maxRetries        = $maxRetries;
        $this->retryDelayMs      = $retryDelayMs;
        $this->timeoutSeconds    = $timeoutSeconds;
        $this->continueOnFailure = $continueOnFailure;
        $this->isolationPerStep  = $isolationPerStep;
    }

    public static function default() : self
    {
        return new self(
            maxRetries    : 0,
            retryDelayMs  : 1000,
            timeoutSeconds: 30
        );
    }

    public static function fromStep(SagaStepDefinition $step) : self
    {
        return new self(
            maxRetries    : $step->maxRetries,
            retryDelayMs  : $step->retryDelayMs,
            timeoutSeconds: $step->timeoutSeconds
        );
    }

    public function withRetries(int $retries) : self
    {
        return new self(
            maxRetries       : $retries,
            retryDelayMs     : $this->retryDelayMs,
            timeoutSeconds   : $this->timeoutSeconds,
            continueOnFailure: $this->continueOnFailure,
            isolationPerStep : $this->isolationPerStep
        );
    }

    public function canRetry(int $currentAttempt) : bool
    {
        return $currentAttempt < $this->maxRetries;
    }

    public function toArray() : array
    {
        return [
            'max_retries'         => $this->maxRetries,
            'retry_delay_ms'      => $this->retryDelayMs,
            'timeout_seconds'     => $this->timeoutSeconds,
            'continue_on_failure' => $this->continueOnFailure,
            'isolation_per_step'  => $this->isolationPerStep,
        ];
    }
}