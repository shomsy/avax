<?php

declare(strict_types=1);

namespace Avax\Tests\Query;

use Avax\Database\Database;
use Avax\Database\Modules\Query\Builder\QueryBuilder;
use Avax\Database\Modules\Query\Query;
use Avax\Tests\TestCase;
use Override;
use Throwable;

class BuilderTest extends TestCase
{
    public function testBasicSelect() : void
    {
        $results = Query::table('users')->select('id', 'name')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('John Doe', $results[0]['name']);
    }

    public function testWhereClauses() : void
    {
        $builder = Query::table('users')->where('id', 1)->orWhere('email', 'test@example.com');

        $this->assertInstanceOf(QueryBuilder::class, $builder);
    }

    public function testJoins() : void
    {
        $builder = Query::table('users')
            ->join('posts', 'users.id', '=', 'posts.user_id')
            ->select('users.name', 'posts.title');

        $this->assertInstanceOf(QueryBuilder::class, $builder);
    }

    public function testAggregates() : void
    {
        $count = Query::table('users')->count();

        $this->assertSame(1, $count);
    }

    /**
     * @throws Throwable
     */
    #[Override]
    protected function setUp() : void
    {
        parent::setUp();

        // Create the users table for testing
        Database::schema()->create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->timestamps();
        });

        // Seed some data
        Database::table('users')->insert(['name' => 'John Doe', 'email' => 'john@example.com']);
    }
}
