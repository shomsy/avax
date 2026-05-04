<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Connections\Contracts\DatabaseConnection;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\IsolationLevel;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\RetryPolicy;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\Transactions;
use PDO;
use PDOException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Test double that implements DatabaseConnection and adds transaction methods.
 */
final class TestDatabaseConnection implements DatabaseConnection
{
    public array $calls = [];

    public ?PDOException $pendingException = null;

    public function getConnection() : PDO
    {
        throw new RuntimeException('Not implemented in test double');
    }

    public function ping() : bool
    {
        return true;
    }

    public function getName() : string
    {
        return 'test';
    }

    public function beginTransaction() : void
    {
        $this->calls[] = 'beginTransaction';
        if ($this->pendingException !== null) {
            $ex = $this->pendingException;
            $this->pendingException = null;

            throw $ex;
        }
    }

    public function commit() : void
    {
        $this->calls[] = 'commit';
    }

    public function rollBack() : void
    {
        $this->calls[] = 'rollBack';
    }

    public function exec(string $sql) : int|false
    {
        $this->calls[] = 'exec:' . $sql;

        return 0;
    }
}

final class TransactionsTest extends TestCase
{
    public function test_begin_starts_transaction() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();

        $this->assertTrue($manager->isActive());
        $this->assertSame(1, $manager->depth());
        $this->assertContains('beginTransaction', $conn->calls);
    }

    // ==================== begin(), commit(), rollback() lifecycle ====================

    private function createTestConnection() : TestDatabaseConnection
    {
        return new TestDatabaseConnection();
    }

    public function test_commit_commits_transaction() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->commit();

        $this->assertFalse($manager->isActive());
        $this->assertSame(0, $manager->depth());
        $this->assertContains('commit', $conn->calls);
    }

    public function test_rollback_rolls_back_transaction() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->rollback();

        $this->assertFalse($manager->isActive());
        $this->assertSame(0, $manager->depth());
        $this->assertContains('rollBack', $conn->calls);
    }

    public function test_commit_without_active_transaction_throws() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot commit: no transaction is active');

        $manager->commit();
    }

    public function test_rollback_without_active_transaction_throws() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot rollback: no transaction is active');

        $manager->rollback();
    }

    public function test_begin_sets_isolation_level() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin(IsolationLevel::SERIALIZABLE);

        $this->assertTrue($manager->isActive());
        $this->assertContains('beginTransaction', $conn->calls);
        $hasIsolationSql = array_reduce($conn->calls, static fn ($c, $call) => $c || str_contains((string) $call, 'ISOLATION LEVEL SERIALIZABLE'), false);
        $this->assertTrue($hasIsolationSql);
    }

    public function test_initial_state_is_not_active() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $this->assertFalse($manager->isActive());
        $this->assertSame(0, $manager->depth());
        $this->assertNull($manager->currentSavepoint());
    }

    // ==================== Nested transactions via savepoints ====================

    public function test_nested_begin_creates_savepoint() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->begin();

        $this->assertTrue($manager->isActive());
        $this->assertSame(2, $manager->depth());
        $this->assertNotNull($manager->currentSavepoint());
        $hasSavepoint = array_reduce($conn->calls, static fn ($c, $call) => $c || str_contains((string) $call, 'SAVEPOINT'), false);
        $this->assertTrue($hasSavepoint);
    }

    public function test_nested_commit_releases_savepoint() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->begin();
        $manager->commit();

        $this->assertTrue($manager->isActive());
        $this->assertSame(1, $manager->depth());
        $hasRelease = array_reduce($conn->calls, static fn ($c, $call) => $c || str_contains((string) $call, 'RELEASE SAVEPOINT'), false);
        $this->assertTrue($hasRelease);
    }

    public function test_nested_rollback_rolls_back_to_savepoint() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->begin();
        $manager->rollback();

        $this->assertTrue($manager->isActive());
        $this->assertSame(1, $manager->depth());
        $hasRollbackTo = array_reduce($conn->calls, static fn ($c, $call) => $c || str_contains((string) $call, 'ROLLBACK TO SAVEPOINT'), false);
        $this->assertTrue($hasRollbackTo);
    }

    public function test_deeply_nested_transactions() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $manager->begin();
        $manager->begin();
        $manager->begin();
        $manager->commit();

        $this->assertSame(3, $manager->depth());
        $savepointCount = array_reduce($conn->calls, static fn ($c, $call) => $c + (str_contains((string) $call, 'SAVEPOINT') && ! str_contains((string) $call, 'RELEASE') && ! str_contains((string) $call, 'ROLLBACK') ? 1 : 0), 0);
        $this->assertSame(3, $savepointCount);
    }

    public function test_savepoint_name_changes_on_each_nesting() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->begin();
        $this->assertNull($manager->currentSavepoint()); // depth 1, no savepoint
        $manager->begin();
        $savepoint1 = $manager->currentSavepoint();
        $manager->begin();
        $savepoint2 = $manager->currentSavepoint();

        $this->assertNotNull($savepoint1);
        $this->assertNotNull($savepoint2);
        $this->assertNotSame($savepoint1, $savepoint2);
    }

    // ==================== transaction() closure API ====================

    public function test_transaction_closure_auto_commits() : void
    {
        $conn   = $this->createTestConnection();
        $manager = new Transactions($conn);
        $result = $manager->transaction(static fn (Transactions $tm) => 'success');

        $this->assertSame('success', $result);
        $this->assertFalse($manager->isActive());
        $this->assertSame(0, $manager->depth());
        $this->assertContains('commit', $conn->calls);
    }

    public function test_transaction_closure_auto_rolls_back_on_exception() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Test error');

        $manager->transaction(static function () : void {
            throw new RuntimeException('Test error');
        });
    }

    public function test_transaction_closure_receives_manager_instance() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $manager->transaction(static function (Transactions $tm) use ($manager) : void {
            self::assertSame($manager, $tm);
            self::assertTrue($tm->isActive());
        });
    }

    public function test_transaction_with_isolation_level() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $manager->transaction(static fn () => 'done', IsolationLevel::REPEATABLE_READ);

        $hasIsolation = array_reduce($conn->calls, static fn ($c, $call) => $c || str_contains((string) $call, 'REPEATABLE READ'), false);
        $this->assertTrue($hasIsolation);
    }

    public function test_nested_transaction_closures() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $manager->transaction(static function (Transactions $tm) : void {
            $tm->transaction(static fn () => 'nested');
        });

        $this->assertFalse($manager->isActive());
        $this->assertContains('commit', $conn->calls);
    }

    // ==================== Retry on failure with RetryPolicy ====================

    public function test_transaction_with_retry_succeeds_on_second_attempt() : void
    {
        $conn = $this->createTestConnection();
        $attempt = 0;
        $manager = new Transactions($conn);

        $policy = new RetryPolicy(
            maxAttempts: 4,
            baseDelayMs: 0,
            maxDelayMs : 0,
            multiplier : 1.0,
            retryOn    : [PDOException::class],
            errorCodes : ['40001'],
        );

        $result = $manager->transactionWithRetry(
            static function (Transactions $tm) use (&$attempt) {
                $attempt++;
                if ($attempt < 3) {
                    throw new PDOException(message: 'Deadlock found', code: 40001);
                }

                return 'retried_success';
            },
            null,
            $policy,
        );

        $this->assertSame('retried_success', $result);
        $this->assertSame(3, $attempt);
    }

    public function test_transaction_with_retry_gives_up_after_max_attempts() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $policy = new RetryPolicy(
            maxAttempts: 2,
            baseDelayMs: 0,
            maxDelayMs : 0,
            multiplier : 1.0,
            retryOn    : [PDOException::class],
            errorCodes : ['40001'],
        );

        $this->expectException(PDOException::class);

        $manager->transactionWithRetry(
            static function () : void {
                throw new PDOException(message: 'Deadlock found', code: 40001);
            },
            null,
            $policy,
        );
    }

    public function test_transaction_with_retry_uses_default_deadlock_policy() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $result = $manager->transactionWithRetry(static fn () => 'default_policy');

        $this->assertSame('default_policy', $result);
    }

    public function test_transaction_with_retry_does_not_retry_non_retriable_exception() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $policy = new RetryPolicy(
            maxAttempts: 3,
            baseDelayMs: 0,
            maxDelayMs : 0,
            multiplier : 1.0,
            retryOn    : [PDOException::class],
            errorCodes : ['40001'],
        );

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Non-retriable error');

        $manager->transactionWithRetry(
            static function () : void {
                throw new RuntimeException('Non-retriable error');
            },
            null,
            $policy,
        );
    }

    // ==================== After-commit and after-rollback callbacks ====================

    public function test_after_commit_callback_executes_on_commit() : void
    {
        $conn    = $this->createTestConnection();
        $manager = new Transactions($conn);
        $executed = false;

        $manager->afterCommit(static function () use (&$executed) : void {
            $executed = true;
        });

        $manager->begin();
        $manager->commit();

        $this->assertTrue($executed);
    }

    public function test_after_commit_callback_does_not_execute_on_rollback() : void
    {
        $conn    = $this->createTestConnection();
        $manager = new Transactions($conn);
        $executed = false;

        $manager->afterCommit(static function () use (&$executed) : void {
            $executed = true;
        });

        $manager->begin();
        $manager->rollback();

        $this->assertFalse($executed);
    }

    public function test_after_rollback_callback_executes_on_rollback() : void
    {
        $conn    = $this->createTestConnection();
        $manager = new Transactions($conn);
        $executed = false;

        $manager->afterRollback(static function () use (&$executed) : void {
            $executed = true;
        });

        $manager->begin();
        $manager->rollback();

        $this->assertTrue($executed);
    }

    public function test_after_rollback_callback_does_not_execute_on_commit() : void
    {
        $conn    = $this->createTestConnection();
        $manager = new Transactions($conn);
        $executed = false;

        $manager->afterRollback(static function () use (&$executed) : void {
            $executed = true;
        });

        $manager->begin();
        $manager->commit();

        $this->assertFalse($executed);
    }

    public function test_multiple_after_commit_callbacks() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);
        $results = [];

        $manager->afterCommit(static function () use (&$results) : void {
            $results[] = 'first';
        });
        $manager->afterCommit(static function () use (&$results) : void {
            $results[] = 'second';
        });

        $manager->begin();
        $manager->commit();

        $this->assertSame(['first', 'second'], $results);
    }

    public function test_callbacks_are_cleared_after_execution() : void
    {
        $conn  = $this->createTestConnection();
        $manager = new Transactions($conn);
        $count = 0;

        $manager->afterCommit(static function () use (&$count) : void {
            $count++;
        });

        $manager->begin();
        $manager->commit();
        $this->assertSame(1, $count);

        $manager->begin();
        $manager->commit();
        $this->assertSame(1, $count);
    }

    // ==================== IsolationLevel enum SQL generation ====================

    public function test_isolation_level_to_sql_default() : void
    {
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            IsolationLevel::READ_UNCOMMITTED->toSql(),
        );
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            IsolationLevel::READ_COMMITTED->toSql(),
        );
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            IsolationLevel::REPEATABLE_READ->toSql(),
        );
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            IsolationLevel::SERIALIZABLE->toSql(),
        );
    }

    public function test_isolation_level_mysql_sql() : void
    {
        $this->assertSame(
            'SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            IsolationLevel::READ_UNCOMMITTED->toSql('mysql'),
        );
        $this->assertSame(
            'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            IsolationLevel::SERIALIZABLE->toSql('mysql'),
        );
    }

    public function test_isolation_level_postgresql_sql() : void
    {
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            IsolationLevel::READ_COMMITTED->toSql('postgresql'),
        );
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            IsolationLevel::SERIALIZABLE->toSql('postgresql'),
        );
    }

    public function test_isolation_level_sqlite_sql() : void
    {
        $this->assertSame(
            'PRAGMA read_uncommitted = true',
            IsolationLevel::READ_UNCOMMITTED->toSql('sqlite'),
        );
        $this->assertSame(
            '',
            IsolationLevel::SERIALIZABLE->toSql('sqlite'),
        );
    }

    public function test_isolation_level_sqlserver_sql() : void
    {
        $this->assertSame(
            'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            IsolationLevel::REPEATABLE_READ->toSql('sqlserver'),
        );
    }

    public function test_isolation_level_supports() : void
    {
        $this->assertTrue(IsolationLevel::READ_UNCOMMITTED->supports('mysql'));
        $this->assertTrue(IsolationLevel::READ_UNCOMMITTED->supports('postgresql'));
        $this->assertTrue(IsolationLevel::READ_UNCOMMITTED->supports('sqlite'));
        $this->assertTrue(IsolationLevel::SERIALIZABLE->supports('sqlserver'));
        $this->assertFalse(IsolationLevel::SERIALIZABLE->supports('oracle'));
    }

    public function test_isolation_level_strictness() : void
    {
        $this->assertSame(1, IsolationLevel::READ_UNCOMMITTED->strictness());
        $this->assertSame(2, IsolationLevel::READ_COMMITTED->strictness());
        $this->assertSame(3, IsolationLevel::REPEATABLE_READ->strictness());
        $this->assertSame(4, IsolationLevel::SERIALIZABLE->strictness());
    }

    public function test_isolation_level_is_stricter_than() : void
    {
        $this->assertTrue(IsolationLevel::SERIALIZABLE->isStricterThan(IsolationLevel::READ_COMMITTED));
        $this->assertFalse(IsolationLevel::READ_COMMITTED->isStricterThan(IsolationLevel::SERIALIZABLE));
        $this->assertFalse(IsolationLevel::READ_COMMITTED->isStricterThan(IsolationLevel::READ_COMMITTED));
    }

    public function test_isolation_level_description() : void
    {
        $this->assertStringContainsString('dirty reads', IsolationLevel::READ_UNCOMMITTED->description());
        $this->assertStringContainsString('dirty reads', IsolationLevel::READ_COMMITTED->description());
        $this->assertStringContainsString('non-repeatable reads', IsolationLevel::REPEATABLE_READ->description());
        $this->assertStringContainsString('Highest isolation', IsolationLevel::SERIALIZABLE->description());
    }

    // ==================== Deadlock RetryPolicy configuration ====================

    public function test_retry_policy_for_deadlocks() : void
    {
        $policy = RetryPolicy::forDeadlocks();

        $this->assertSame(5, $policy->maxAttempts);
        $this->assertSame(100, $policy->baseDelayMs);
        $this->assertSame(5000, $policy->maxDelayMs);
        $this->assertSame(2.0, $policy->multiplier);
        $this->assertContains(PDOException::class, $policy->retryOn);
        $this->assertContains('40001', $policy->errorCodes);
        $this->assertContains('40P01', $policy->errorCodes);
    }

    public function test_retry_policy_for_network_errors() : void
    {
        $policy = RetryPolicy::forNetworkErrors();

        $this->assertSame(3, $policy->maxAttempts);
        $this->assertSame(500, $policy->baseDelayMs);
        $this->assertSame(10000, $policy->maxDelayMs);
        $this->assertSame(1.5, $policy->multiplier);
    }

    public function test_retry_policy_for_timeouts() : void
    {
        $policy = RetryPolicy::forTimeouts();

        $this->assertSame(3, $policy->maxAttempts);
        $this->assertSame(200, $policy->baseDelayMs);
        $this->assertSame(3000, $policy->maxDelayMs);
    }

    public function test_retry_policy_no_retry() : void
    {
        $policy = RetryPolicy::noRetry();

        $this->assertSame(1, $policy->maxAttempts);
        $this->assertEmpty($policy->retryOn);
        $this->assertEmpty($policy->errorCodes);
    }

    public function test_retry_policy_should_retry_on_deadlock_message() : void
    {
        $policy = RetryPolicy::forDeadlocks();
        $ex = new RuntimeException('Deadlock found when trying to get lock');

        $this->assertTrue($policy->shouldRetry($ex, 1));
    }

    public function test_retry_policy_should_retry_on_serialization_failure() : void
    {
        $policy = RetryPolicy::forDeadlocks();
        $ex = new RuntimeException('Serialization failure');

        $this->assertTrue($policy->shouldRetry($ex, 1));
    }

    public function test_retry_policy_should_not_retry_beyond_max_attempts() : void
    {
        $policy = RetryPolicy::forDeadlocks(3);
        $ex = new RuntimeException('Deadlock found');

        $this->assertTrue($policy->shouldRetry($ex, 1));
        $this->assertTrue($policy->shouldRetry($ex, 2));
        $this->assertFalse($policy->shouldRetry($ex, 3));
    }

    public function test_retry_policy_get_delay_ms_exponential() : void
    {
        $policy = new RetryPolicy(
            maxAttempts: 5,
            baseDelayMs: 100,
            maxDelayMs : 5000,
            multiplier : 2.0,
        );

        $this->assertSame(100, $policy->getDelayMs(0));
        $this->assertSame(200, $policy->getDelayMs(1));
        $this->assertSame(400, $policy->getDelayMs(2));
        $this->assertSame(800, $policy->getDelayMs(3));
    }

    public function test_retry_policy_get_delay_ms_capped() : void
    {
        $policy = new RetryPolicy(
            maxAttempts: 10,
            baseDelayMs: 100,
            maxDelayMs : 500,
            multiplier : 3.0,
        );

        $this->assertSame(500, $policy->getDelayMs(3));
    }

    public function test_retry_policy_with_max_attempts() : void
    {
        $policy = RetryPolicy::forDeadlocks(5);
        $newPolicy = $policy->withMaxAttempts(10);

        $this->assertSame(5, $policy->maxAttempts);
        $this->assertSame(10, $newPolicy->maxAttempts);
    }

    public function test_retry_policy_has_retry_criteria() : void
    {
        $policyWith    = RetryPolicy::forDeadlocks();
        $policyWithout = new RetryPolicy();

        $this->assertTrue($policyWith->hasRetryCriteria());
        $this->assertFalse($policyWithout->hasRetryCriteria());
    }

    // ==================== Transaction state tracking ====================

    public function test_reset_clears_all_state() : void
    {
        $conn = $this->createTestConnection();
        $manager = new Transactions($conn);

        $manager->afterCommit(static function () : void {});
        $manager->afterRollback(static function () : void {});
        $manager->begin();
        $manager->begin();

        $manager->reset();

        $this->assertFalse($manager->isActive());
        $this->assertSame(0, $manager->depth());
        $this->assertNull($manager->currentSavepoint());
    }

    public function test_transaction_closure_callback_not_executed_on_rollback() : void
    {
        $conn    = $this->createTestConnection();
        $manager = new Transactions($conn);
        $callbackExecuted = false;

        $manager->afterCommit(static function () use (&$callbackExecuted) : void {
            $callbackExecuted = true;
        });

        try {
            $manager->transaction(static function () : void {
                throw new RuntimeException('Test');
            });
        } catch (RuntimeException) {
            // Expected
        }

        $this->assertFalse($callbackExecuted);
    }
}
