<?php

declare(strict_types=1);

namespace Avax\Components\Operations\Resilience\System\Flows\ExecuteWithReliability;

use Avax\Components\Operations\Resilience\System\Capabilities\CircuitBreaker\CircuitBreaker;
use Avax\Components\Operations\Resilience\System\Capabilities\Fallback\Fallback;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryOptions;
use Avax\Components\Operations\Resilience\System\Capabilities\Retry\RetryResult;
use Avax\Components\Operations\Resilience\System\Capabilities\Timeout\Timeout;
use Avax\Components\Operations\Resilience\System\Foundation\Values\ReliabilityResult;
use Closure;
use Throwable;

/**
 * Unified reliability engine that composes retry, circuit breaker, timeout, and fallback.
 *
 * Execution order:
 * 1. Circuit breaker gate check
 * 2. Timeout wrapper
 * 3. Retry loop
 * 4. Fallback on exhaustion
 */
final class ExecuteWithReliability
{
    private int $maxAttempts = 3;
    private int $backoffMs = 100;
    private ?int $timeoutMs = null;
    private int $cbFailureThreshold = 5;
    private int $cbCooldownSeconds = 30;
    private ?Closure $fallback = null;

    public function withMaxAttempts(int $max) : self
    {
        $this->maxAttempts = $max;

        return $this;
    }

    public function withBackoffMs(int $ms) : self
    {
        $this->backoffMs = $ms;

        return $this;
    }

    public function withTimeoutMs(?int $ms) : self
    {
        $this->timeoutMs = $ms;

        return $this;
    }

    public function withCircuitBreaker(int $failureThreshold, int $cooldownSeconds) : self
    {
        $this->cbFailureThreshold = $failureThreshold;
        $this->cbCooldownSeconds = $cooldownSeconds;

        return $this;
    }

    public function withFallback(Closure $fallback) : self
    {
        $this->fallback = $fallback;

        return $this;
    }

    /**
     * Execute an operation with full reliability composition.
     *
     * @template TResult
     * @param Closure(): TResult $operation
     * @return TResult|mixed
     */
    public function execute(Closure $operation) : mixed
    {
        $circuitBreaker = new CircuitBreaker(
            failureThreshold: $this->cbFailureThreshold,
            cooldownSeconds: $this->cbCooldownSeconds,
        );

        try {
            return $circuitBreaker->run(function () use ($operation) {
                $retryOptions = new RetryOptions(
                    attempts: $this->maxAttempts,
                    backoffMs: $this->backoffMs,
                    timeoutMs: $this->timeoutMs,
                );

                $retryResult = $this->runWithTimeout($operation, $retryOptions);

                if ($retryResult->success) {
                    return $retryResult->result;
                }

                return $this->handleFailure($retryResult);
            });
        } catch (Throwable $e) {
            if ($this->fallback !== null) {
                return ($this->fallback)($e);
            }

            throw $e;
        }
    }

    /**
     * @param Closure(): mixed $operation
     */
    private function runWithTimeout(Closure $operation, RetryOptions $options) : RetryResult
    {
        $lastException = null;
        $attemptNumber = 0;

        while ($attemptNumber < $options->attempts) {
            $attemptNumber++;

            try {
                if ($this->timeoutMs !== null) {
                    $result = $this->executeWithTimeout($operation, $this->timeoutMs);
                } else {
                    $result = $operation();
                }

                return new RetryResult(
                    success: true,
                    result: $result,
                    attempts: $attemptNumber,
                    lastException: null,
                );
            } catch (Throwable $e) {
                $lastException = $e;

                if ($attemptNumber < $options->attempts) {
                    $this->sleep($options->backoffMs);
                }
            }
        }

        return new RetryResult(
            success: false,
            result: null,
            attempts: $attemptNumber,
            lastException: $lastException,
        );
    }

    /**
     * @param Closure(): mixed $operation
     */
    private function executeWithTimeout(Closure $operation, int $timeoutMs) : mixed
    {
        $timeout = new Timeout(timeoutMs: $timeoutMs);

        return $timeout->run($operation);
    }

    private function handleFailure(RetryResult $retryResult) : mixed
    {
        if ($this->fallback !== null) {
            return ($this->fallback)($retryResult->lastException);
        }

        throw $retryResult->lastException ?? new \RuntimeException('Operation failed after retries');
    }

    private function sleep(int $backoffMs) : void
    {
        $jitter = (int) ($backoffMs * (1 + random_int(0, 100) / 100));
        usleep($jitter * 1000);
    }
}
