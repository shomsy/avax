<?php

declare(strict_types=1);

namespace Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\StepRunner;

use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyKey;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Idempotency\IdempotencyStore;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Retries\RetryPolicy;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\SagaState\SagaStep;
use Avax\Components\Operations\ApplicationWorkflow\System\Capabilities\Timeouts\SagaTimeout;
use RuntimeException;
use Throwable;

/**
 * Executes saga steps with idempotency, retry, and timeout support.
 */
final class StepRunner
{
    private readonly IdempotencyStore $idempotencyStore;

    private readonly RetryPolicy $retryPolicy;

    private readonly SagaTimeout $sagaTimeout;

    /**
     * @var array<string, array{error: string, time: string}>
     */
    private array $failureLog = [];

    public function __construct(
        ?IdempotencyStore $idempotencyStore = null,
        ?RetryPolicy      $retryPolicy = null,
        ?SagaTimeout      $sagaTimeout = null,
    )
    {
        $this->idempotencyStore = $idempotencyStore ?? new IdempotencyStore();
        $this->retryPolicy      = $retryPolicy ?? RetryPolicy::none();
        $this->sagaTimeout      = $sagaTimeout ?? SagaTimeout::seconds(30);
    }

    /**
     * Execute a saga step with idempotency check, retry, and timeout handling.
     *
     * @param SagaStep       $sagaStep       The step to execute
     * @param mixed          $context        The saga context
     * @param IdempotencyKey $idempotencyKey The idempotency key for this execution
     *
     * @return mixed The result of the step execution
     *
     * @throws Throwable If the step fails after all retries
     */
    public function execute(
        SagaStep       $sagaStep,
        mixed          $context,
        IdempotencyKey $idempotencyKey,
    ) : mixed
    {
        // Check idempotency - if already executed, return cached result
        if ($this->idempotencyStore->hasExecuted($idempotencyKey)) {
            return $this->idempotencyStore->getResult($idempotencyKey);
        }

        $attempt       = 0;
        $lastException = null;

        while ( $this->retryPolicy->canRetry($attempt) ) {
            $attempt++;
            $startTime = hrtime(true);

            try {
                $result = $this->executeWithTimeout($sagaStep, $context);

                // Record successful execution
                $this->idempotencyStore->record($idempotencyKey, $result);

                return $result;
            } catch (Throwable $e) {
                $elapsed       = (hrtime(true) - $startTime) / 1_000_000; // ms
                $lastException = $e;

                // Record failure
                $this->recordFailure($idempotencyKey, $e, $attempt);

                // Check timeout
                if ($this->sagaTimeout->isExceeded((int) $elapsed)) {
                    throw new RuntimeException(
                        sprintf(
                            'Step "%s" timed out after %dms (limit: %dms)',
                            $sagaStep->name,
                            (int) $elapsed,
                            $this->sagaTimeout->timeoutMs,
                        ),
                        0,
                        $e,
                    );
                }

                // If no more retries allowed, throw
                if (! $this->retryPolicy->canRetry($attempt)) {
                    throw $e;
                }

                // Apply backoff delay
                $delay = $this->retryPolicy->getDelayForAttempt($attempt);
                if ($delay > 0) {
                    $this->sleep($delay);
                }
            }
        }

        // Should not reach here, but just in case
        throw $lastException ?? new RuntimeException('Step execution failed unexpectedly');
    }

    /**
     * Execute a step with timeout enforcement.
     */
    private function executeWithTimeout(SagaStep $sagaStep, mixed $context) : mixed
    {
        $startTime = hrtime(true);

        $result = $sagaStep->execute($context);

        $elapsed = (hrtime(true) - $startTime) / 1_000_000; // ms

        if ($this->sagaTimeout->isExceeded((int) $elapsed)) {
            throw new RuntimeException(
                sprintf(
                    'Step "%s" timed out after %dms (limit: %dms)',
                    $sagaStep->name,
                    (int) $elapsed,
                    $this->sagaTimeout->timeoutMs,
                ),
            );
        }

        return $result;
    }

    /**
     * Record a step failure.
     */
    private function recordFailure(IdempotencyKey $idempotencyKey, Throwable $throwable, int $attempt) : void
    {
        $this->failureLog[$idempotencyKey->toString()] = [
            'error'   => $throwable->getMessage(),
            'time'    => date('c'),
            'attempt' => $attempt,
        ];
    }

    /**
     * Sleep for a given number of milliseconds (testable).
     */
    private function sleep(int $ms) : void
    {
        usleep($ms * 1000);
    }

    /**
     * Get the failure log.
     *
     * @return array<string, array{error: string, time: string, attempt: int}>
     */
    public function getFailureLog() : array
    {
        return $this->failureLog;
    }
}
