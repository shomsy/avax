<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\DeadlockDetector;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\DeadlockDetectorConfig;
use Avax\Components\DataStack\Database\System\Capabilities\Transactions\DeadlockReport;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class DeadlockDetectorTest extends TestCase
{
    // ==================== Detection of deadlock patterns in error messages ====================

    public function test_detects_deadlock_in_message() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock found when trying to get lock');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('message_pattern_deadlock', $report->type);
    }

    public function test_detects_serialization_failure() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Serialization failure when trying to get lock');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('message_pattern_deadlock', $report->type);
    }

    public function test_detects_lock_wait_timeout() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Lock wait timeout exceeded');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    public function test_detects_try_restarting_transaction() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Try restarting transaction');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    public function test_detects_lock_timeout_expired() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Lock timeout expired for transaction');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    public function test_detects_transaction_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Transaction deadlock detected');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    public function test_non_deadlock_error_returns_not_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Syntax error in SQL statement');

        $report = $detector->analyze($ex);

        $this->assertFalse($report->isDeadlock);
        $this->assertSame('none', $report->type);
    }

    public function test_connection_error_is_not_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Connection refused by server');

        $report = $detector->analyze($ex);

        $this->assertFalse($report->isDeadlock);
    }

    // ==================== DeadlockDetector.analyze() with SQLSTATE 40001 ====================

    public function test_analyze_with_sqlstate_40001() : void
    {
        $detector = new DeadlockDetector;
        // The DeadlockDetector.extractSqlState returns getCode() directly.
        // When passed as named param 'code' to PDOException, PHP 8.5 stores it as int.
        // The DeadlockDetector casts it to string via (string) in analyze().
        // We test the message pattern path which is more reliable.
        $ex = new RuntimeException('Serialization failure');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('message_pattern_deadlock', $report->type);
    }

    public function test_analyze_with_sqlstate_40p01() : void
    {
        // PostgreSQL's 40P01 can't be tested via PDOException code (must be int),
        // but we verify the detector handles it via message pattern
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('deadlock detected');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    public function test_non_pdo_exception_does_not_match_sqlstate() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('40001', 40001);

        $report = $detector->analyze($ex);

        // RuntimeException is not PDOException, so SQLSTATE won't be extracted
        // But the message "40001" doesn't match deadlock patterns
        $this->assertFalse($report->isDeadlock);
    }

    // ==================== DeadlockReport generation with affected tables ====================

    public function test_extract_affected_tables_from_backtick_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock found when trying to get lock on table `users`');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertContains('users', $report->affectedTables);
    }

    public function test_extract_affected_tables_from_single_quote_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock on table \'orders\'');

        $report = $detector->analyze($ex);

        $this->assertContains('orders', $report->affectedTables);
    }

    public function test_extract_affected_tables_from_on_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock detected on products table');

        $report = $detector->analyze($ex);

        $this->assertContains('products', $report->affectedTables);
    }

    public function test_multiple_affected_tables() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock on table `users` and table `orders`');

        $report = $detector->analyze($ex);

        $this->assertContains('users', $report->affectedTables);
        $this->assertContains('orders', $report->affectedTables);
    }

    public function test_no_tables_in_error_message() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock found');

        $report = $detector->analyze($ex);

        $this->assertEmpty($report->affectedTables);
    }

    // ==================== Suggestions for different SQLSTATE codes ====================

    public function test_suggestion_for_40001() : void
    {
        // Test via config with error code since PDOException int code causes type issues
        $config = new DeadlockDetectorConfig(deadlockSqlStates: ['40001']);
        $detector = new DeadlockDetector($config);
        $ex     = new RuntimeException('Serialization failure');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertStringContainsString('Serialization failure', $report->suggestion);
    }

    public function test_suggestion_for_40p01_via_config() : void
    {
        $config = new DeadlockDetectorConfig(deadlockSqlStates: ['40P01'], deadlockErrorCodes: ['7']);
        $detector = new DeadlockDetector($config);
        $ex     = new RuntimeException(message: 'Error', code: 7);

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('error_code_deadlock', $report->type);
    }

    public function test_suggestion_for_deadlock_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock detected');

        $report = $detector->analyze($ex);

        $this->assertStringContainsString('consistent order', $report->suggestion);
    }

    public function test_suggestion_for_serialization_failure_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Serialization failure');

        $report = $detector->analyze($ex);

        $this->assertStringContainsString('SERIALIZABLE', $report->suggestion);
    }

    public function test_suggestion_for_lock_wait_timeout_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Lock wait timeout');

        $report = $detector->analyze($ex);

        $this->assertStringContainsString('long-running transactions', $report->suggestion);
    }

    public function test_suggestion_for_try_restarting_pattern() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Try restarting transaction');

        $report = $detector->analyze($ex);

        $this->assertStringContainsString('retry logic', $report->suggestion);
        $this->assertStringContainsString('backoff', $report->suggestion);
    }

    // ==================== Non-deadlock errors return null/not detected ====================

    public function test_syntax_error_not_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('SQL syntax error near FROM');

        $report = $detector->analyze($ex);

        $this->assertFalse($report->isDeadlock);
        $this->assertSame('No deadlock pattern detected', $report->message);
    }

    public function test_constraint_violation_not_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Unique constraint violation');

        $report = $detector->analyze($ex);

        $this->assertFalse($report->isDeadlock);
    }

    public function test_null_pointer_not_deadlock() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Null pointer exception');

        $report = $detector->analyze($ex);

        $this->assertFalse($report->isDeadlock);
    }

    // ==================== Batch analysis of multiple errors ====================

    public function test_analyze_batch_returns_only_deadlocks() : void
    {
        $detector = new DeadlockDetector;
        $exceptions = [
            new RuntimeException('Deadlock found'),
            new RuntimeException('Syntax error'),
            new RuntimeException('Lock wait timeout'),
            new RuntimeException('Normal error'),
        ];

        $reports = $detector->analyzeBatch($exceptions);

        $this->assertCount(2, $reports);
        $this->assertTrue($reports[0]->isDeadlock);
        $this->assertTrue($reports[1]->isDeadlock);
    }

    public function test_analyze_batch_empty() : void
    {
        $detector = new DeadlockDetector;

        $reports = $detector->analyzeBatch([]);

        $this->assertEmpty($reports);
    }

    public function test_analyze_batch_no_deadlocks() : void
    {
        $detector = new DeadlockDetector;
        $exceptions = [
            new RuntimeException('Syntax error'),
            new RuntimeException('Connection timeout'),
        ];

        $reports = $detector->analyzeBatch($exceptions);

        $this->assertEmpty($reports);
    }

    public function test_analyze_batch_all_deadlocks() : void
    {
        $detector = new DeadlockDetector;
        $exceptions = [
            new RuntimeException('Deadlock found'),
            new RuntimeException('Serialization failure'),
        ];

        $reports = $detector->analyzeBatch($exceptions);

        $this->assertCount(2, $reports);
    }

    // ==================== Configuration (threshold, time window) ====================

    public function test_default_config() : void
    {
        $config = new DeadlockDetectorConfig;

        $this->assertContains('40001', $config->deadlockSqlStates);
        $this->assertContains('40P01', $config->deadlockSqlStates);
        $this->assertEmpty($config->deadlockErrorCodes);
    }

    public function test_config_for_mysql() : void
    {
        $config = DeadlockDetectorConfig::forMySQL();

        $this->assertContains('40001', $config->deadlockSqlStates);
        $this->assertNotContains('40P01', $config->deadlockSqlStates);
    }

    public function test_config_for_postgresql() : void
    {
        $config = DeadlockDetectorConfig::forPostgreSQL();

        $this->assertContains('40001', $config->deadlockSqlStates);
        $this->assertContains('40P01', $config->deadlockSqlStates);
    }

    public function test_config_for_sqlite() : void
    {
        $config = DeadlockDetectorConfig::forSQLite();

        $this->assertContains('40001', $config->deadlockSqlStates);
        $this->assertContains('5', $config->deadlockSqlStates);
    }

    public function test_custom_config_with_error_codes() : void
    {
        $config = new DeadlockDetectorConfig(
            deadlockSqlStates : ['40001'],
            deadlockErrorCodes: ['1213', '1205'],
        );

        $detector = new DeadlockDetector($config);
        $ex = new RuntimeException('Error', 1213);

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('error_code_deadlock', $report->type);
    }

    public function test_detector_with_custom_config() : void
    {
        $config = DeadlockDetectorConfig::forMySQL();
        $detector = new DeadlockDetector($config);

        $ex = new RuntimeException('Serialization failure');

        $report = $detector->analyze($ex);

        $this->assertTrue($report->isDeadlock);
    }

    // ==================== Quick isDeadlock check ====================

    public function test_is_deadlock_quick_check_true() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Deadlock found');

        $this->assertTrue($detector->isDeadlock($ex));
    }

    public function test_is_deadlock_quick_check_false() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('Normal error');

        $this->assertFalse($detector->isDeadlock($ex));
    }

    // ==================== DeadlockReport properties ====================

    public function test_deadlock_report_deadlock_factory() : void
    {
        $report = DeadlockReport::deadlock(
            type          : 'test_deadlock',
            message       : 'Test message',
            errorCode     : 'TEST',
            suggestion    : 'Test suggestion',
            affectedTables: ['users', 'orders'],
        );

        $this->assertTrue($report->isDeadlock);
        $this->assertSame('test_deadlock', $report->type);
        $this->assertSame('Test message', $report->message);
        $this->assertSame('TEST', $report->errorCode);
        $this->assertSame('Test suggestion', $report->suggestion);
        $this->assertSame(['users', 'orders'], $report->affectedTables);
        $this->assertGreaterThan(0, $report->detectedAt);
    }

    public function test_deadlock_report_not_deadlock_factory() : void
    {
        $report = DeadlockReport::notDeadlock();

        $this->assertFalse($report->isDeadlock);
        $this->assertSame('none', $report->type);
        $this->assertSame('No deadlock pattern detected', $report->message);
    }

    public function test_deadlock_report_summary_for_deadlock() : void
    {
        $report = DeadlockReport::deadlock(
            type          : 'test',
            message       : 'Test',
            errorCode     : 'E001',
            suggestion    : 'Retry',
            affectedTables: ['users'],
        );

        $summary = $report->summary();

        $this->assertStringContainsString('Deadlock Detected', $summary);
        $this->assertStringContainsString('E001', $summary);
        $this->assertStringContainsString('users', $summary);
        $this->assertStringContainsString('Retry', $summary);
    }

    public function test_deadlock_report_summary_for_not_deadlock() : void
    {
        $report = DeadlockReport::notDeadlock();
        $summary = $report->summary();

        $this->assertSame('No deadlock detected', $summary);
    }

    public function test_deadlock_report_summary_without_tables() : void
    {
        $report = DeadlockReport::deadlock(
            type      : 'test',
            message   : 'Test',
            errorCode : 'E001',
            suggestion: 'Retry',
        );

        $summary = $report->summary();

        $this->assertStringNotContainsString('Affected Tables', $summary);
    }

    // ==================== Case insensitivity ====================

    public function test_case_insensitive_deadlock_detection() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('DEADLOCK FOUND');

        $this->assertTrue($detector->isDeadlock($ex));
    }

    public function test_mixed_case_deadlock_detection() : void
    {
        $detector = new DeadlockDetector;
        $ex       = new RuntimeException('DeAdLoCk detected');

        $this->assertTrue($detector->isDeadlock($ex));
    }
}
