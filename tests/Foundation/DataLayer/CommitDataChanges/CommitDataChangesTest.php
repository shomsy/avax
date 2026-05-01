<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\DataLayer\CommitDataChanges;

use Avax\Components\DataStack\Database\CommitDataChanges\CommitDataChanges;
use Avax\Components\DataStack\Database\CommitDataChanges\DataTransactionFailure;
use Avax\Components\DataStack\Database\CommitDataChanges\DataTransactionPolicy;
use Avax\Tests\TestCase;
use RuntimeException;

final class CommitDataChangesTest extends TestCase
{
    public function testRollbackClosesOpenTransaction() : void
    {
        $commitChanges = new CommitDataChanges();
        $transaction   = $commitChanges->open();

        $rolledBack = $commitChanges->rollback(transaction: $transaction);

        $this->assertSame('rolled_back', $rolledBack->status);
    }

    public function testRetryIsRejectedWhenWorkIsNotIdempotent() : void
    {
        $commitChanges = new CommitDataChanges();

        $this->expectException(DataTransactionFailure::class);

        $commitChanges->retryTransientFailure(
            work  : static fn () : string => 'not-safe',
            policy: new DataTransactionPolicy(maxAttempts: 2, retryTransientFailures: true, idempotent: false),
        );
    }

    public function testDeadlockRetryCanReplayIdempotentWork() : void
    {
        $commitChanges = new CommitDataChanges();
        $attempts      = 0;

        $result = $commitChanges->retryTransientFailure(
            work  : static function () use (&$attempts) : string {
                $attempts++;

                if ($attempts === 1) {
                    throw new RuntimeException(message: 'SQLSTATE[40001]: deadlock detected');
                }

                return 'committed';
            },
            policy: new DataTransactionPolicy(maxAttempts: 2, retryTransientFailures: true, idempotent: true),
        );

        $this->assertSame('committed', $result);
        $this->assertSame(2, $attempts);
    }
}
