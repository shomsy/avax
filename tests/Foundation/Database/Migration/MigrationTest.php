<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\Migration;

use Avax\Database\System\Capabilities\Migrations\Design\Column\DSL\ColumnDefinition;
use Avax\Database\System\Capabilities\Migrations\Design\Column\Render\ColumnSQLRenderer;
use Avax\Database\System\Capabilities\Migrations\Design\Table\Blueprint;
use Avax\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Tests\TestCase;

class MigrationTest extends TestCase
{
    public function test_blueprint_generates_create_sql() : void
    {
        $blueprint = new Blueprint(table: 'users');
        $blueprint->id();
        $blueprint->string(name: 'email');
        $blueprint->timestamps();

        $sql = $blueprint->toSql(grammar: new MySQLGrammar());

        $this->assertCount(expectedCount: 1, haystack: $sql);
        // noinspection SqlNoDataSourceInspection
        $this->assertStringContainsString(needle: 'CREATE TABLE `users`', haystack: $sql[0]);
        $this->assertStringContainsString(needle: '`id` BIGINT', haystack: $sql[0]);
        $this->assertStringContainsString(needle: '`email` VARCHAR(255)', haystack: $sql[0]);
        $this->assertStringContainsString(needle: '`created_at` TIMESTAMP', haystack: $sql[0]);
        $this->assertStringContainsString(needle: '`updated_at` TIMESTAMP', haystack: $sql[0]);
    }

    public function test_column_renderer_generates_sql() : void
    {
        $renderer = new ColumnSQLRenderer();
        $column = new ColumnDefinition(name: 'name', type: 'VARCHAR(255)')
            ->nullable()
            ->unique();

        $sql = $renderer->render(column: $column, grammar: new MySQLGrammar());

        $this->assertStringContainsString(needle: '`name` VARCHAR(255)', haystack: $sql);
        $this->assertStringContainsString(needle: 'NULL', haystack: $sql);
        $this->assertStringContainsString(needle: 'UNIQUE', haystack: $sql);
    }
}
