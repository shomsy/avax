<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\ConnectionContracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\IsolationLevel;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RetryPolicy;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Throwable;

/**
 * Comprehensive tests for the TransactionManager lifecycle.
 *
 * Covers begin/commit/rollback, savepoints, nested transactions,
 * isolation levels, retry logic, and callbacks.
 */
final class TransactionManagerTest extends TestCase
{
    private DatabaseConnection&MockObject $connection;
    private Transactions                  $transactions;

    #[Test]
    public function starts_with_no_active_transaction() : void
    {
        self::assertFalse(condition: $this->transactions->isActive());
        self::assertSame(expected: 0, actual: $this->transactions->depth());
        self::assertNull(actual: $this->transactions->currentSavepoint());
    }

    // ============================================================
    // INITIAL STATE
    // ============================================================

    #[Test]
    public function begin_starts_transaction() : void
    {
        $this->connection->expects(self::once())
            ->method('beginTransaction');

        $this->transactions->begin();

        self::assertTrue(condition: $this->transactions->isActive());
        self::assertSame(expected: 1, actual: $this->transactions->depth());
    }

    // ============================================================
    // BEGIN / COMMIT / ROLLBACK
    // ============================================================

    #[Test]
    public function commit_commits_transaction() : void
    {
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('commit');

        $this->transactions->begin();
        $this->transactions->commit();

        self::assertFalse(condition: $this->transactions->isActive());
        self::assertSame(expected: 0, actual: $this->transactions->depth());
    }

    #[Test]
    public function rollback_rolls_back_transaction() : void
    {
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('rollBack');

        $this->transactions->begin();
        $this->transactions->rollback();

        self::assertFalse(condition: $this->transactions->isActive());
        self::assertSame(expected: 0, actual: $this->transactions->depth());
    }

    #[Test]
    public function commit_without_transaction_throws() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot commit: no transaction is active');

        $this->transactions->commit();
    }

    #[Test]
    public function rollback_without_transaction_throws() : void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(message: 'Cannot rollback: no transaction is active');

        $this->transactions->rollback();
    }

    #[Test]
    public function begin_with_isolation_level_sets_isolation() : void
    {
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::once())
            ->method('exec')
            ->with(constraint: 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED');

        $this->transactions->begin(isolationLevel: IsolationLevel::READ_COMMITTED);
        $this->transactions->commit();
    }

    // ============================================================
    // ISOLATION LEVELS
    // ============================================================

    #[Test]
    public function begin_without_isolation_level_skips_exec() : void
    {
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::never())
            ->method('exec');

        $this->transactions->begin();
        $this->transactions->commit();
    }

    #[Test]
    public function all_isolation_levels_can_be_applied() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            $conn = $this->createMock(DatabaseConnection::class);
            $conn->expects(self::once())->method('beginTransaction');
            $conn->expects(self::once())->method('exec');

            $tx = new Transactions(databaseConnection: $conn);
            $tx->begin(isolationLevel: $level);
            $tx->commit();
        }
    }

    #[Test]
    public function nested_begin_creates_savepoint() : void
    {
        $execCalls = [];
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::exactly(2))
            ->method('exec')
            ->willReturnCallback(static function (string $sql) use (&$execCalls) {
                $execCalls[] = $sql;

                return false;
            });

        $this->transactions->begin();
        $this->transactions->begin();

        self::assertSame(expected: 2, actual: $this->transactions->depth());
        self::assertNotNull(actual: $this->transactions->currentSavepoint());

        $this->transactions->commit();
        $this->transactions->commit();

        self::assertCount(expectedCount: 2, haystack: $execCalls);
        self::assertStringContainsString(needle: 'SAVEPOINT', haystack: $execCalls[0]);
        self::assertStringContainsString(needle: 'RELEASE SAVEPOINT', haystack: $execCalls[1]);
    }

    // ============================================================
    // NESTED TRANSACTIONS (SAVEPOINTS)
    // ============================================================

    #[Test]
    public function nested_commit_releases_savepoint() : void
    {
        $execCalls = [];
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::exactly(2))
            ->method('exec')
            ->willReturnCallback(static function (string $sql) use (&$execCalls) {
                $execCalls[] = $sql;

                return false;
            });
        $this->connection->expects(self::once())
            ->method('commit');

        $this->transactions->begin();
        $this->transactions->begin();
        $this->transactions->commit();
        $this->transactions->commit();

        self::assertCount(expectedCount: 2, haystack: $execCalls);
        self::assertStringContainsString(needle: 'SAVEPOINT', haystack: $execCalls[0]);
        self::assertStringContainsString(needle: 'RELEASE SAVEPOINT', haystack: $execCalls[1]);
    }

    #[Test]
    public function nested_rollback_rolls_back_to_savepoint() : void
    {
        $execCalls = [];
        $this->connection->expects(self::once())
            ->method('beginTransaction');
        $this->connection->expects(self::exactly(2))
            ->method('exec')
            ->willReturnCallback(static function (string $sql) use (&$execCalls) {
                $execCalls[] = $sql;

                return false;
            });
        $this->connection->expects(self::once())
            ->method('commit');

        $this->transactions->begin();
        $this->transactions->begin();
        $this->transactions->rollback();
        $this->transactions->commit();

        self::assertCount(expectedCount: 2, haystack: $execCalls);
        self::assertStringContainsString(needle: 'SAVEPOINT', haystack: $execCalls[0]);
        self::assertStringContainsString(needle: 'ROLLBACK TO SAVEPOINT', haystack: $execCalls[1]);
    }

    #[Test]
    public function deeply_nested_transactions_work() : void
    {
        $execCalls = [];
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::exactly(4))->method('exec')
            ->willReturnCallback(static function (string $sql) use (&$execCalls) {
                $execCalls[] = $sql;

                return false;
            });
        $this->connection->expects(self::once())->method('commit');

        $this->transactions->begin();
        $this->transactions->begin();
        $this->transactions->begin();

        self::assertSame(expected: 3, actual: $this->transactions->depth());

        $this->transactions->commit();
        $this->transactions->commit();
        $this->transactions->commit();

        // Verify we got 2 SAVEPOINT and 2 RELEASE SAVEPOINT calls
        $savepointCalls = array_filter($execCalls, fn ($sql) => str_contains($sql, 'SAVEPOINT') && ! str_contains($sql, 'RELEASE'));
        $releaseCalls   = array_filter($execCalls, fn ($sql) => str_contains($sql, 'RELEASE SAVEPOINT'));
        self::assertCount(2, $savepointCalls);
        self::assertCount(2, $releaseCalls);
    }

    #[Test]
    public function savepoint_names_are_unique() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('exec');

        $this->transactions->begin();
        $this->transactions->begin();
        $sp1 = $this->transactions->currentSavepoint();

        $this->transactions->begin();
        $sp2 = $this->transactions->currentSavepoint();

        self::assertNotSame(expected: $sp1, actual: $sp2);

        $this->transactions->commit();
        $this->transactions->commit();
        $this->transactions->commit();
    }

    #[Test]
    public function transaction_closure_commits_on_success() : void
    {
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())->method('commit');
        $this->connection->expects(self::never())->method('rollBack');

        $result = $this->transactions->transaction(callback: static function () {
            return 'success';
        });

        self::assertSame(expected: 'success', actual: $result);
        self::assertFalse(condition: $this->transactions->isActive());
    }

    // ============================================================
    // TRANSACTION CLOSURE (automatic begin/commit/rollback)
    // ============================================================

    #[Test]
    public function transaction_closure_rolls_back_on_exception() : void
    {
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::never())->method('commit');
        $this->connection->expects(self::once())->method('rollBack');

        $this->expectException(RuntimeException::class);

        $this->transactions->transaction(callback: static function () {
            throw new RuntimeException(message: 'Something went wrong');
        });
    }

    #[Test]
    public function transaction_closure_rethrows_exception() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('rollBack');

        $exception = null;

        try {
            $this->transactions->transaction(callback: static function () {
                throw new InvalidArgumentException(message: 'Bad argument');
            });
        } catch (Throwable $e) {
            $exception = $e;
        }

        self::assertInstanceOf(expected: InvalidArgumentException::class, actual: $exception);
        self::assertSame(expected: 'Bad argument', actual: $exception->getMessage());
    }

    #[Test]
    public function transaction_receives_transaction_instance() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $receivedTx = null;

        $this->transactions->transaction(callback: function (Transactions $tx) use (&$receivedTx) {
            $receivedTx = $tx;
        });

        self::assertSame(expected: $this->transactions, actual: $receivedTx);
    }

    #[Test]
    public function transaction_with_retry_succeeds_on_first_attempt() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $attempts = 0;

        $result = $this->transactions->transactionWithRetry(
            callback   : static function () use (&$attempts) {
                $attempts++;

                return 'success';
            },
            retryPolicy: RetryPolicy::noRetry(),
        );

        self::assertSame(expected: 'success', actual: $result);
        self::assertSame(expected: 1, actual: $attempts);
    }

    // ============================================================
    // RETRY LOGIC
    // ============================================================

    #[Test]
    public function transaction_with_retry_retries_on_deadlock() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');
        $this->connection->method('rollBack');

        $attempts = 0;

        $result = $this->transactions->transactionWithRetry(
            callback   : function () use (&$attempts) {
                $attempts++;
                if ($attempts < 3) {
                    throw new PDOException(message: 'Deadlock found', code: 40001);
                }

                return 'success';
            },
            retryPolicy: RetryPolicy::forDeadlocks(maxAttempts: 5),
        );

        self::assertSame(expected: 'success', actual: $result);
        self::assertSame(expected: 3, actual: $attempts);
    }

    #[Test]
    public function transaction_with_retry_gives_up_after_max_attempts() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('rollBack');

        $attempts = 0;

        $this->expectException(PDOException::class);

        $this->transactions->transactionWithRetry(
            callback   : function () use (&$attempts) {
                $attempts++;
                throw new PDOException(message: 'Deadlock found', code: 40001);
            },
            retryPolicy: RetryPolicy::forDeadlocks(maxAttempts: 3),
        );

        self::assertSame(expected: 3, actual: $attempts);
    }

    #[Test]
    public function transaction_with_retry_throws_immediately_on_non_retryable_error() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('rollBack');

        $attempts = 0;

        $this->expectException(InvalidArgumentException::class);

        $this->transactions->transactionWithRetry(
            callback   : function () use (&$attempts) {
                $attempts++;
                throw new InvalidArgumentException(message: 'Not retryable');
            },
            retryPolicy: RetryPolicy::forDeadlocks(maxAttempts: 5),
        );

        self::assertSame(expected: 1, actual: $attempts);
    }

    #[Test]
    public function after_commit_callback_executes_on_commit() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $executed = false;

        $this->transactions->afterCommit(callback: static function () use (&$executed) {
            $executed = true;
        });

        $this->transactions->begin();
        $this->transactions->commit();

        self::assertTrue(condition: $executed);
    }

    // ============================================================
    // CALLBACKS (afterCommit / afterRollback)
    // ============================================================

    #[Test]
    public function after_commit_callback_does_not_execute_on_rollback() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('rollBack');

        $executed = false;

        $this->transactions->afterCommit(callback: static function () use (&$executed) {
            $executed = true;
        });

        $this->transactions->begin();
        $this->transactions->rollback();

        self::assertFalse(condition: $executed);
    }

    #[Test]
    public function after_rollback_callback_executes_on_rollback() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('rollBack');

        $executed = false;

        $this->transactions->afterRollback(callback: static function () use (&$executed) {
            $executed = true;
        });

        $this->transactions->begin();
        $this->transactions->rollback();

        self::assertTrue(condition: $executed);
    }

    #[Test]
    public function after_rollback_callback_does_not_execute_on_commit() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $executed = false;

        $this->transactions->afterRollback(callback: static function () use (&$executed) {
            $executed = true;
        });

        $this->transactions->begin();
        $this->transactions->commit();

        self::assertFalse(condition: $executed);
    }

    #[Test]
    public function multiple_callbacks_all_execute() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $count = 0;

        $this->transactions->afterCommit(callback: static function () use (&$count) {
            $count++;
        });
        $this->transactions->afterCommit(callback: static function () use (&$count) {
            $count++;
        });
        $this->transactions->afterCommit(callback: static function () use (&$count) {
            $count++;
        });

        $this->transactions->begin();
        $this->transactions->commit();

        self::assertSame(expected: 3, actual: $count);
    }

    #[Test]
    public function callback_failure_does_not_break_flow() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $secondExecuted = false;

        $this->transactions->afterCommit(callback: static function () {
            throw new RuntimeException(message: 'Callback failed');
        });
        $this->transactions->afterCommit(callback: static function () use (&$secondExecuted) {
            $secondExecuted = true;
        });

        $this->transactions->begin();
        $this->transactions->commit();

        // Second callback still executes (errors are caught and logged)
        self::assertTrue(condition: $secondExecuted);
    }

    #[Test]
    public function callbacks_are_cleared_after_execution() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('commit');

        $count = 0;

        $this->transactions->afterCommit(callback: static function () use (&$count) {
            $count++;
        });

        $this->transactions->begin();
        $this->transactions->commit();

        // Run a second transaction - callbacks should not fire again
        $this->transactions->begin();
        $this->transactions->commit();

        self::assertSame(expected: 1, actual: $count);
    }

    #[Test]
    public function reset_clears_all_state() : void
    {
        $this->connection->method('beginTransaction');

        $this->transactions->afterCommit(callback: static function () {});
        $this->transactions->afterRollback(callback: static function () {});
        $this->transactions->begin();
        $this->transactions->begin();

        $this->transactions->reset();

        self::assertFalse(condition: $this->transactions->isActive());
        self::assertSame(expected: 0, actual: $this->transactions->depth());
        self::assertNull(actual: $this->transactions->currentSavepoint());
    }

    // ============================================================
    // RESET
    // ============================================================

    #[Test]
    public function transaction_closure_with_isolation_level() : void
    {
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::once())->method('exec');
        $this->connection->expects(self::once())->method('commit');

        $result = $this->transactions->transaction(
            callback      : static function () {
                return 'done';
            },
            isolationLevel: IsolationLevel::SERIALIZABLE,
        );

        self::assertSame(expected: 'done', actual: $result);
    }

    // ============================================================
    // EDGE CASES
    // ============================================================

    #[Test]
    public function commit_on_nested_transaction_does_not_execute_outer_commit() : void
    {
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::exactly(2))->method('exec');
        $this->connection->expects(self::once())->method('commit');

        $this->transactions->begin();
        $this->transactions->begin();
        $this->transactions->commit(); // releases savepoint
        $this->transactions->commit(); // commits outer
    }

    #[Test]
    public function rollback_on_nested_transaction_does_not_execute_outer_rollback() : void
    {
        $execCalls = [];
        $this->connection->expects(self::once())->method('beginTransaction');
        $this->connection->expects(self::exactly(2))->method('exec')
            ->willReturnCallback(static function (string $sql) use (&$execCalls) {
                $execCalls[] = $sql;

                return false;
            });
        $this->connection->expects(self::once())->method('commit');

        $this->transactions->begin();
        $this->transactions->begin();
        $this->transactions->rollback(); // rolls back to savepoint
        $this->transactions->commit();   // commits outer

        self::assertCount(expectedCount: 2, haystack: $execCalls);
        self::assertStringContainsString(needle: 'SAVEPOINT', haystack: $execCalls[0]);
        self::assertStringContainsString(needle: 'ROLLBACK TO SAVEPOINT', haystack: $execCalls[1]);
    }

    #[Test]
    public function depth_tracks_nesting_accurately() : void
    {
        $this->connection->method('beginTransaction');
        $this->connection->method('exec');

        self::assertSame(expected: 0, actual: $this->transactions->depth());

        $this->transactions->begin();
        self::assertSame(expected: 1, actual: $this->transactions->depth());

        $this->transactions->begin();
        self::assertSame(expected: 2, actual: $this->transactions->depth());

        $this->transactions->begin();
        self::assertSame(expected: 3, actual: $this->transactions->depth());

        $this->transactions->commit();
        self::assertSame(expected: 2, actual: $this->transactions->depth());

        $this->transactions->rollback();
        self::assertSame(expected: 1, actual: $this->transactions->depth());

        $this->transactions->commit();
        self::assertSame(expected: 0, actual: $this->transactions->depth());
    }

    protected function setUp() : void
    {
        $this->connection   = $this->createMock(DatabaseConnection::class);
        $this->transactions = new Transactions(databaseConnection: $this->connection);
    }
}
