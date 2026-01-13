<?php

declare(strict_types=1);

namespace Avax\Tests\Query;

use Avax\Database\Database;
use Avax\Database\Modules\Query\Builder\QueryBuilder;
use Avax\Database\Modules\Query\Query;
use Avax\Tests\TestCase;
use Throwable;

class BuilderTest extends TestCase
{
    public function test_basic_select(): void
    {
        $results = Query::table('users')->select('id', 'name')->get();

        $this->assertCount(expectedCount: 1, haystack: $results);
        $this->assertEquals(expected: 'John Doe', actual: $results[0]['name']);
    }

    public function test_where_clauses(): void
    {
        $builder = Query::table('users')->where('id', 1)->orWhere('email', 'test@example.com');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }

    public function test_joins(): void
    {
        $builder = Query::table('users')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->select('users.name', 'posts.title');

        $this->assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }

    public function test_aggregates(): void
    {
        $count = Query::table('users')->count();

        $this->assertSame(expected: 1, actual: $count);
    }

    /**
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Create the users table for testing
        Database::schema()->create('users', static function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        // Seed some data
        Database::table('users')->insert(['name' => 'John Doe', 'email' => 'john@example.com']);
    }
}
