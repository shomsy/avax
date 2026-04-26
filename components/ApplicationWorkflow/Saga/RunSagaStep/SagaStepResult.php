<?php

declare(strict_types=1);

namespace components\ApplicationWorkflow\Saga\RunSagaStep;

use components\ApplicationWorkflow\Saga\DefineSaga\SagaStepDefinition;
use DateTimeImmutable;
use DateTimeInterface;

final readonly class SagaStepResult
{
    public string                 $stepName;
    public bool                   $success;
    public array                  $output;
    public string|null            $error;
    public int                    $attempt;
    public float                  $durationMs;
    public DateTimeImmutable|null $completedAt;

    private function __construct(
        string                 $stepName,
        bool                   $success,
        array|null             $output = null,
        string|null            $error = null,
        int|null               $attempt = null,
        float|null             $durationMs = null,
        DateTimeImmutable|null $completedAt = null
    )
    {
        $output            ??= [];
        $attempt           ??= 1;
        $durationMs        ??= 0.0;
        $this->stepName    = $stepName;
        $this->success     = $success;
        $this->output      = $output;
        $this->error       = $error;
        $this->attempt     = $attempt;
        $this->durationMs  = $durationMs;
        $this->completedAt = $completedAt;
    }

    public static function success(
        string     $stepName,
        array|null $output = null,
        int|null   $attempt = null,
        float      $durationMs = 0.0
    ) : self
    {
        $output  ??= [];
        $attempt ??= 1;

        return new self(
            stepName   : $stepName,
            success    : true,
            output     : $output,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable()
        );
    }

    public static function failure(
        string   $stepName,
        string   $error,
        int|null $attempt = null,
        float    $durationMs = 0.0
    ) : self
    {
        $attempt ??= 1;

        return new self(
            stepName   : $stepName,
            success    : false,
            error      : $error,
            attempt    : $attempt,
            durationMs : $durationMs,
            completedAt: new DateTimeImmutable()
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
            'completed_at' => $this->completedAt?->format(format: DateTimeInterface::ISO8601),
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
        int|null  $maxRetries = null,
        int|null  $retryDelayMs = null,
        int|null  $timeoutSeconds = null,
        bool|null $continueOnFailure = null,
        bool      $isolationPerStep = true
    )
    {
        $maxRetries              ??= 0;
        $retryDelayMs            ??= 1000;
        $timeoutSeconds          ??= 30;
        $continueOnFailure       ??= false;
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