<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Transactions\IsolationLevel;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the IsolationLevel enum: SQL generation, dialect support, strictness.
 */
final class IsolationLevelTest extends TestCase
{
    // ============================================================
    // ENUM VALUES
    // ============================================================

    #[Test]
    public function has_four_isolation_levels() : void
    {
        self::assertCount(expectedCount: 4, haystack: IsolationLevel::cases());
    }

    #[Test]
    public function enum_values_match_expected_strings() : void
    {
        self::assertSame(expected: 'READ UNCOMMITTED', actual: IsolationLevel::READ_UNCOMMITTED->value);
        self::assertSame(expected: 'READ COMMITTED', actual: IsolationLevel::READ_COMMITTED->value);
        self::assertSame(expected: 'REPEATABLE READ', actual: IsolationLevel::REPEATABLE_READ->value);
        self::assertSame(expected: 'SERIALIZABLE', actual: IsolationLevel::SERIALIZABLE->value);
    }

    // ============================================================
    // DEFAULT SQL GENERATION
    // ============================================================

    #[Test]
    public function default_sql_uses_standard_syntax() : void
    {
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            actual  : IsolationLevel::READ_UNCOMMITTED->toSql(),
        );
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            actual  : IsolationLevel::READ_COMMITTED->toSql(),
        );
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            actual  : IsolationLevel::REPEATABLE_READ->toSql(),
        );
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            actual  : IsolationLevel::SERIALIZABLE->toSql(),
        );
    }

    // ============================================================
    // MySQL DIALECT SQL
    // ============================================================

    #[Test]
    public function mysql_uses_session_syntax() : void
    {
        self::assertSame(
            expected: 'SET SESSION TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            actual  : IsolationLevel::READ_UNCOMMITTED->toSql(dialect: 'mysql'),
        );
        self::assertSame(
            expected: 'SET SESSION TRANSACTION ISOLATION LEVEL READ COMMITTED',
            actual  : IsolationLevel::READ_COMMITTED->toSql(dialect: 'mysql'),
        );
        self::assertSame(
            expected: 'SET SESSION TRANSACTION ISOLATION LEVEL REPEATABLE READ',
            actual  : IsolationLevel::REPEATABLE_READ->toSql(dialect: 'mysql'),
        );
        self::assertSame(
            expected: 'SET SESSION TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            actual  : IsolationLevel::SERIALIZABLE->toSql(dialect: 'mysql'),
        );
    }

    // ============================================================
    // PostgreSQL DIALECT SQL
    // ============================================================

    #[Test]
    public function postgresql_uses_standard_syntax() : void
    {
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            actual  : IsolationLevel::READ_COMMITTED->toSql(dialect: 'postgresql'),
        );
    }

    // ============================================================
    // SQLite DIALECT SQL
    // ============================================================

    #[Test]
    public function sqlite_read_uncommitted_uses_pragma() : void
    {
        self::assertSame(
            expected: 'PRAGMA read_uncommitted = true',
            actual  : IsolationLevel::READ_UNCOMMITTED->toSql(dialect: 'sqlite'),
        );
    }

    #[Test]
    public function sqlite_other_levels_return_empty_string() : void
    {
        self::assertSame(expected: '', actual: IsolationLevel::READ_COMMITTED->toSql(dialect: 'sqlite'));
        self::assertSame(expected: '', actual: IsolationLevel::REPEATABLE_READ->toSql(dialect: 'sqlite'));
        self::assertSame(expected: '', actual: IsolationLevel::SERIALIZABLE->toSql(dialect: 'sqlite'));
    }

    // ============================================================
    // SQL Server DIALECT SQL
    // ============================================================

    #[Test]
    public function sqlserver_uses_standard_syntax() : void
    {
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL READ UNCOMMITTED',
            actual  : IsolationLevel::READ_UNCOMMITTED->toSql(dialect: 'sqlserver'),
        );
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            actual  : IsolationLevel::SERIALIZABLE->toSql(dialect: 'sqlserver'),
        );
    }

    // ============================================================
    // UNKNOWN DIALECT
    // ============================================================

    #[Test]
    public function unknown_dialect_returns_default_sql() : void
    {
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL READ COMMITTED',
            actual  : IsolationLevel::READ_COMMITTED->toSql(dialect: 'oracle'),
        );
    }

    #[Test]
    public function null_dialect_returns_default_sql() : void
    {
        self::assertSame(
            expected: 'SET TRANSACTION ISOLATION LEVEL SERIALIZABLE',
            actual  : IsolationLevel::SERIALIZABLE->toSql(dialect: null),
        );
    }

    // ============================================================
    // DIALECT SUPPORT
    // ============================================================

    #[Test]
    public function mysql_supports_all_levels() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            self::assertTrue(
                condition: $level->supports(dialect: 'mysql'),
                message  : "MySQL should support {$level->value}",
            );
        }
    }

    #[Test]
    public function postgresql_supports_all_levels() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            self::assertTrue(
                condition: $level->supports(dialect: 'postgresql'),
                message  : "PostgreSQL should support {$level->value}",
            );
        }
    }

    #[Test]
    public function sqlite_supports_subset_of_levels() : void
    {
        self::assertTrue(condition: IsolationLevel::READ_UNCOMMITTED->supports(dialect: 'sqlite'));
        self::assertTrue(condition: IsolationLevel::READ_COMMITTED->supports(dialect: 'sqlite'));
        self::assertFalse(condition: IsolationLevel::REPEATABLE_READ->supports(dialect: 'sqlite'));
        self::assertTrue(condition: IsolationLevel::SERIALIZABLE->supports(dialect: 'sqlite'));
    }

    #[Test]
    public function sqlserver_supports_all_levels() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            self::assertTrue(
                condition: $level->supports(dialect: 'sqlserver'),
                message  : "SQL Server should support {$level->value}",
            );
        }
    }

    #[Test]
    public function unknown_dialect_is_not_supported() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            self::assertFalse(
                condition: $level->supports(dialect: 'unknown'),
                message  : "Unknown dialect should not support {$level->value}",
            );
        }
    }

    // ============================================================
    // STRICTNESS
    // ============================================================

    #[Test]
    public function strictness_increases_with_level() : void
    {
        self::assertSame(expected: 1, actual: IsolationLevel::READ_UNCOMMITTED->strictness());
        self::assertSame(expected: 2, actual: IsolationLevel::READ_COMMITTED->strictness());
        self::assertSame(expected: 3, actual: IsolationLevel::REPEATABLE_READ->strictness());
        self::assertSame(expected: 4, actual: IsolationLevel::SERIALIZABLE->strictness());
    }

    #[Test]
    public function is_stricter_than_works_correctly() : void
    {
        self::assertTrue(condition: IsolationLevel::SERIALIZABLE->isStricterThan(other: IsolationLevel::READ_COMMITTED));
        self::assertTrue(condition: IsolationLevel::READ_COMMITTED->isStricterThan(other: IsolationLevel::READ_UNCOMMITTED));
        self::assertFalse(condition: IsolationLevel::READ_UNCOMMITTED->isStricterThan(other: IsolationLevel::SERIALIZABLE));
        self::assertFalse(condition: IsolationLevel::READ_COMMITTED->isStricterThan(other: IsolationLevel::READ_COMMITTED));
    }

    // ============================================================
    // DESCRIPTIONS
    // ============================================================

    #[Test]
    public function each_level_has_a_description() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            $description = $level->description();
            self::assertNotEmpty(actual: $description, message: "{$level->value} should have a description");
        }
    }

    #[Test]
    public function read_uncommitted_description_mentions_dirty_reads() : void
    {
        $desc = IsolationLevel::READ_UNCOMMITTED->description();
        self::assertStringContainsString(needle: 'dirty reads', haystack: $desc);
    }

    #[Test]
    public function serializable_description_mentions_highest_isolation() : void
    {
        $desc = IsolationLevel::SERIALIZABLE->description();
        self::assertStringContainsString(needle: 'Highest isolation', haystack: $desc);
    }

    // ============================================================
    // SQL CONTAINS_EXPECTED_LEVEL
    // ============================================================

    #[Test]
    public function generated_sql_contains_isolation_level_name() : void
    {
        foreach (IsolationLevel::cases() as $level) {
            $sql = $level->toSql(dialect: 'mysql');
            self::assertStringContainsString(
                needle  : $level->value,
                haystack: $sql,
                message : "SQL for {$level->value} should contain the level name",
            );
        }
    }
}
