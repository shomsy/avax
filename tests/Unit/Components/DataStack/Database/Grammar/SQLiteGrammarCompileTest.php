<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database\Grammar;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\SQLiteGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use PHPUnit\Framework\TestCase;

final class SQLiteGrammarCompileTest extends TestCase
{
    private SQLiteGrammar $grammar;

    public function test_compile_select_basic(): void
    {
        $state = new QueryState();
        $state = $state->withFrom(table: 'users');

        $sql = $this->grammar->compileSelect($state);

        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('users', $sql);
    }

    public function test_compile_select_with_columns(): void
    {
        $state = new QueryState();
        $state = $state->withFrom(table: 'users')
            ->withColumns(columns: ['id', 'name']);

        $sql = $this->grammar->compileSelect($state);

        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('id', $sql);
        $this->assertStringContainsString('name', $sql);
    }

    public function test_compile_insert(): void
    {
        $state = new QueryState();
        $state = $state->withFrom(table: 'users')
            ->withValues(values: ['name' => 'John', 'email' => 'john@test.com']);

        $sql = $this->grammar->compileInsert($state);

        $this->assertStringContainsString('INSERT INTO', $sql);
        $this->assertStringContainsString('users', $sql);
    }

    public function test_compile_update(): void
    {
        $state = new QueryState();
        $state = $state->withFrom(table: 'users')
            ->withUpdateColumns(columns: ['name' => 'Jane']);

        $sql = $this->grammar->compileUpdate($state);

        $this->assertStringContainsString('UPDATE', $sql);
        $this->assertStringContainsString('users', $sql);
    }

    public function test_compile_delete(): void
    {
        $state = new QueryState();
        $state = $state->withFrom(table: 'users');

        $sql = $this->grammar->compileDelete($state);

        $this->assertStringContainsString('DELETE FROM', $sql);
        $this->assertStringContainsString('users', $sql);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->grammar = new SQLiteGrammar();
    }
}
