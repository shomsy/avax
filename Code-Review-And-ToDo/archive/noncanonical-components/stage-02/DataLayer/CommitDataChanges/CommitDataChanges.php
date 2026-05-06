<?php

declare(strict_types=1);

namespace Avax\Components\DataLayer\CommitDataChanges;

use Throwable;

final class CommitDataChanges
{
    public function __construct(
        private object $databaseRuntime
    ) {
    }

    public function open(): object
    {
        if (method_exists($this->databaseRuntime, 'begin')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->begin();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->transactions()->begin();
        } else {
            throw new DataTransactionFailure(message: 'Cannot begin transaction');
        }

        return (object) ['status' => 'open'];
    }

    public function rollback(object $transaction): object
    {
        if (method_exists($this->databaseRuntime, 'rollback')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->rollback();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->transactions()->rollback();
        }

        return (object) ['status' => 'rolled_back'];
    }

    public function commit(?object $transaction = null): void
    {
        if (method_exists($this->databaseRuntime, 'commit')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->commit();
        } elseif (method_exists($this->databaseRuntime, 'transactions')) {
            // @phpstan-ignore-next-line
            $this->databaseRuntime->transactions()->commit();
        }
    }

    public function retryTransientFailure(callable $work, DataTransactionPolicy $policy): mixed
    {
        if (! $policy->idempotent) {
            throw new DataTransactionFailure(message: 'Work is not idempotent and policy forbids retry');
        }

        $attempt = 0;
        while (true) {
            try {
                $attempt++;

                return $work();
            } catch (Throwable $e) {
                $msg = strtolower($e->getMessage());
                $isTransient = str_contains($msg, 'deadlock') || str_contains($msg, '40001') || str_contains($msg, 'sqlstate');
                if (! $isTransient || ! $policy->retryTransientFailures) {
                    throw $e;
                }
                if ($attempt >= $policy->maxAttempts) {
                    throw $e;
                }
                // exponential backoff could be added
                usleep(100000 * $attempt); // 100ms * attempt
            }
        }
    }
}
