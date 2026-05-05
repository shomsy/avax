<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use PHPUnit\Framework\TestCase;

final class SchemaBlueprintTest extends TestCase
{
    public function test_blueprint_compiles_create_table_sql_for_common_columns() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $blueprint->id();
        $blueprint->string(name: 'name', length: 120);
        $blueprint->string(name: 'email')->unique();
        $blueprint->boolean(name: 'active')->default(value: true);
        $blueprint->timestamps();

        $sql = $blueprint->toSql(grammar: new MySQLGrammar());

        self::assertNotSame([], $sql);

        $joinedSql = self::normalizeSql(implode(' ', $sql));

        self::assertStringContainsString('CREATE TABLE', strtoupper($joinedSql));
        self::assertStringContainsString('users', $joinedSql);
        self::assertMatchesRegularExpression('/id/i', $joinedSql);
        self::assertMatchesRegularExpression('/name/i', $joinedSql);
        self::assertMatchesRegularExpression('/email/i', $joinedSql);
        self::assertMatchesRegularExpression('/active/i', $joinedSql);
        self::assertMatchesRegularExpression('/created_at/i', $joinedSql);
        self::assertMatchesRegularExpression('/updated_at/i', $joinedSql);
    }

    private static function normalizeSql(string $sql) : string
    {
        return preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
    }

    public function test_blueprint_compiles_alter_table_sql_for_drop_and_rename_column() : void
    {
        $blueprint = new Blueprint(table: 'users');

        $blueprint->setAlterMode();
        $blueprint->dropColumn('legacy_name');
        $blueprint->renameColumn(from: 'display_name', to: 'name');

        $sql = $blueprint->toSql(grammar: new MySQLGrammar());

        self::assertNotSame([], $sql);

        $joinedSql = self::normalizeSql(implode(' ', $sql));

        self::assertStringContainsString('ALTER TABLE', strtoupper($joinedSql));
        self::assertStringContainsString('users', $joinedSql);
        self::assertMatchesRegularExpression('/legacy_name/i', $joinedSql);
        self::assertMatchesRegularExpression('/display_name/i', $joinedSql);
        self::assertMatchesRegularExpression('/name/i', $joinedSql);
    }
}
