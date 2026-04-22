<?php

declare(strict_types=1);

namespace Avax\Tests\Foundation\Database\QueryBuilder;

use Avax\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Tests\TestCase;
use Override;
use Throwable;

class BuilderTest extends TestCase
{
    public function test_basic_select() : void
    {
        $results = $this->database->table(table: 'users')->select('id', 'name')->get();

        $this->assertCount(expectedCount: 1, haystack: $results);
        $this->assertEquals(expected: 'John Doe', actual: $results[0]['name']);
    }

    public function test_where_clauses() : void
    {
        $builder = $this->database->table(table: 'users')
            ->where(column: 'id', value: 1)
            ->orWhere(column: 'email', value: 'test@example.com');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }

    public function test_joins() : void
    {
        $builder = $this->database->table(table: 'users')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->select('users.name', 'posts.title');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }

    public function test_aggregates() : void
    {
        $count = $this->database->table(table: 'users')->count();

        $this->assertSame(expected: 1, actual: $count);
    }

    /**
     * @throws Throwable
     */
    #[Override]
    protected function setUp() : void
    {
        parent::setUp();

        $pdo = $this->database->connections()->pdo();

        // noinspection SqlNoDataSourceInspection
        $pdo->exec(
            statement: 'CREATE TABLE users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                email TEXT NOT NULL,
                created_at TEXT NULL,
                updated_at TEXT NULL
            )'
        );

        // noinspection SqlNoDataSourceInspection
        $pdo->exec(
            statement: 'CREATE TABLE posts (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id INTEGER NOT NULL,
                title TEXT NOT NULL
            )'
        );

        $this->database->table(table: 'users')->insert(['name' => 'John Doe', 'email' => 'john@example.com']);
        $this->database->table(table: 'posts')->insert(['user_id' => 1, 'title' => 'Hello']);
    }
}
