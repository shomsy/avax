<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Lifecycle;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Exceptions\TransactionException;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RunTransaction\Transaction;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class TransactionAfterCommitSafetyTest extends TestCase
{
    private function createTransactionWithMockConnection(): Transaction
    {
        $pdo = $this->createMock(PDO::class);
        $connection = $this->createMock(DatabaseConnection::class);
        $connection->method('getConnection')->willReturn($pdo);

        return Transaction::on($connection);
    }

    public function test_after_commit_runs_after_successful_commit(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });
        $transaction->commit();

        $this->assertTrue($executed);
    }

    public function test_after_commit_does_not_run_before_commit(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });

        $this->assertFalse($executed);
    }

    public function test_after_commit_does_not_run_on_rollback(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });
        $transaction->rollback();

        $this->assertFalse($executed);
    }

    public function test_rollback_clears_pending_after_commit_actions(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });

        // First rollback clears afterCommit.
        $transaction->rollback();
        $this->assertFalse($executed);

        // Start a new transaction to verify callbacks were cleared.
        $transaction->begin();
        // After the first rollback cleared them, no old callbacks should exist.
        $transaction->commit();

        $this->assertFalse($executed);
    }

    public function test_after_rollback_runs_on_rollback(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterRollback(static function () use (&$executed): void {
            $executed = true;
        });
        $transaction->rollback();

        $this->assertTrue($executed);
    }

    public function test_after_rollback_does_not_run_on_commit(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin();
        $transaction->afterRollback(static function () use (&$executed): void {
            $executed = true;
        });
        $transaction->commit();

        $this->assertFalse($executed);
    }

    public function test_commit_failure_does_not_run_after_commit(): void
    {
        $pdo = $this->createMock(PDO::class);
        $pdo->method('commit')->willThrowException(new RuntimeException('Commit failed'));

        $connection = $this->createMock(DatabaseConnection::class);
        $connection->method('getConnection')->willReturn($pdo);

        $transaction = Transaction::on($connection);
        $executed = false;

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });

        $this->expectException(TransactionException::class);
        $transaction->commit();

        $this->assertFalse($executed);
    }

    public function test_nested_transaction_after_commit_runs_only_on_outermost_commit(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $callCount = 0;

        $transaction->begin(); // Outer
        $transaction->afterCommit(static function () use (&$callCount): void {
            $callCount++;
        });

        $transaction->begin(); // Inner (savepoint)
        $transaction->afterCommit(static function () use (&$callCount): void {
            $callCount++;
        });

        $transaction->commit(); // Release savepoint — afterCommit should NOT run yet.
        $this->assertSame(0, $callCount);

        $transaction->commit(); // Outermost commit — both afterCommit callbacks run.
        $this->assertSame(2, $callCount);
    }

    public function test_nested_rollback_does_not_run_after_commit(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        $transaction->begin(); // Outer
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });

        $transaction->begin(); // Inner (savepoint)
        $transaction->rollback(); // Rollback to savepoint

        $this->assertFalse($executed);

        // Now rollback the outer transaction.
        $transaction->rollback();
        $this->assertFalse($executed);
    }

    public function test_after_commit_without_transaction_runs_immediately(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $executed = false;

        // No transaction started — afterCommit runs immediately.
        $transaction->afterCommit(static function () use (&$executed): void {
            $executed = true;
        });

        $this->assertTrue($executed);
    }

    public function test_after_commit_callbacks_do_not_leak_between_transactions(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $firstCount = 0;
        $secondCount = 0;

        // First transaction.
        $transaction->begin();
        $transaction->afterCommit(static function () use (&$firstCount): void {
            $firstCount++;
        });
        $transaction->commit();

        $this->assertSame(1, $firstCount);

        // Second transaction — old callbacks should NOT run.
        $transaction->begin();
        $transaction->afterCommit(static function () use (&$secondCount): void {
            $secondCount++;
        });
        $transaction->commit();

        // First callback did NOT run again (still 1, not 2).
        $this->assertSame(1, $firstCount);
        $this->assertSame(1, $secondCount);
    }

    public function test_transaction_nesting_level(): void
    {
        $transaction = $this->createTransactionWithMockConnection();

        $this->assertSame(0, $transaction->getNestingLevel());

        $transaction->begin();
        $this->assertSame(1, $transaction->getNestingLevel());

        $transaction->begin();
        $this->assertSame(2, $transaction->getNestingLevel());

        $transaction->commit();
        $this->assertSame(1, $transaction->getNestingLevel());

        $transaction->commit();
        $this->assertSame(0, $transaction->getNestingLevel());
    }

    public function test_multiple_after_commit_callbacks_run_in_registration_order(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $order = [];

        $transaction->begin();
        $transaction->afterCommit(static function () use (&$order): void {
            $order[] = 'first';
        });
        $transaction->afterCommit(static function () use (&$order): void {
            $order[] = 'second';
        });
        $transaction->afterCommit(static function () use (&$order): void {
            $order[] = 'third';
        });
        $transaction->commit();

        $this->assertSame(['first', 'second', 'third'], $order);
    }

    public function test_multiple_after_rollback_callbacks_run_in_registration_order(): void
    {
        $transaction = $this->createTransactionWithMockConnection();
        $order = [];

        $transaction->begin();
        $transaction->afterRollback(static function () use (&$order): void {
            $order[] = 'first';
        });
        $transaction->afterRollback(static function () use (&$order): void {
            $order[] = 'second';
        });
        $transaction->rollback();

        $this->assertSame(['first', 'second'], $order);
    }
}
