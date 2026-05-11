<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\ExecutorInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\BaseGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\JoinNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\NestedWhereNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\OrderNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\WhereNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Comprehensive tests for SQL grammar compilation (BaseGrammar + MySQLGrammar).
 */
final class QueryGrammarTest extends TestCase
{
    private BaseGrammar  $baseGrammar;
    private MySQLGrammar $mySQLGrammar;

    #[Test]
    public function base_grammar_uses_double_quotes() : void
    {
        self::assertSame(expected: '"users"', actual: $this->baseGrammar->wrap(value: 'users'));
    }

    // ============================================================
    // WRAP / IDENTIFIER QUOTING
    // ============================================================

    #[Test]
    public function mysql_grammar_uses_backticks() : void
    {
        self::assertSame(expected: '`users`', actual: $this->mySQLGrammar->wrap(value: 'users'));
    }

    #[Test]
    public function wrap_handles_asterisk_without_quotes() : void
    {
        self::assertSame(expected: '*', actual: $this->baseGrammar->wrap(value: '*'));
        self::assertSame(expected: '*', actual: $this->mySQLGrammar->wrap(value: '*'));
    }

    #[Test]
    public function wrap_handles_functions_without_quotes() : void
    {
        self::assertSame(expected: 'COUNT(*)', actual: $this->baseGrammar->wrap(value: 'COUNT(*)'));
        self::assertSame(expected: 'COUNT(*)', actual: $this->mySQLGrammar->wrap(value: 'COUNT(*)'));
    }

    #[Test]
    public function wrap_handles_dotted_names() : void
    {
        self::assertSame(expected: '"users"."name"', actual: $this->baseGrammar->wrap(value: 'users.name'));
        self::assertSame(expected: '`users`.`name`', actual: $this->mySQLGrammar->wrap(value: 'users.name'));
    }

    #[Test]
    public function wrap_handles_expression_objects() : void
    {
        $expression = new Expression(value: 'NOW()');

        self::assertSame(expected: 'NOW()', actual: $this->baseGrammar->wrap(value: $expression));
        self::assertSame(expected: 'NOW()', actual: $this->mySQLGrammar->wrap(value: $expression));
    }

    #[Test]
    public function wrap_escapes_quotes_in_segments() : void
    {
        self::assertSame(expected: '"user""s"', actual: $this->baseGrammar->wrap(value: 'user"s'));
    }

    #[Test]
    public function mysql_wrap_escapes_backticks_in_segments() : void
    {
        self::assertSame(expected: '`user``s`', actual: $this->mySQLGrammar->wrap(value: 'user`s'));
    }

    #[Test]
    public function compile_select_basic() : void
    {
        $state = new QueryState(columns: ['id', 'name'], from: 'users');
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT "id", "name" FROM "users"', actual: $sql);
    }

    // ============================================================
    // SELECT COMPILATION
    // ============================================================

    #[Test]
    public function compile_select_with_distinct() : void
    {
        $state = new QueryState(columns: ['name'], from: 'users', distinct: true);
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT DISTINCT "name" FROM "users"', actual: $sql);
    }

    #[Test]
    public function compile_select_with_limit() : void
    {
        $state = new QueryState(columns: ['*'], from: 'users', limit: 10);
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" LIMIT 10', actual: $sql);
    }

    #[Test]
    public function compile_select_with_offset() : void
    {
        $state = new QueryState(columns: ['*'], from: 'users', offset: 20);
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" OFFSET 20', actual: $sql);
    }

    #[Test]
    public function compile_select_with_limit_and_offset() : void
    {
        $state = new QueryState(columns: ['*'], from: 'users', limit: 10, offset: 20);
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" LIMIT 10 OFFSET 20', actual: $sql);
    }

    #[Test]
    public function compile_select_with_orders() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            orders : [
                         new OrderNode(column: 'name', direction: 'ASC'),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" ORDER BY "name" ASC', actual: $sql);
    }

    #[Test]
    public function compile_select_with_groups() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'orders',
            groups : ['customer_id'],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "orders" GROUP BY "customer_id"', actual: $sql);
    }

    #[Test]
    public function compile_select_with_joins() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            joins  : [
                         new JoinNode(
                             table   : 'posts',
                             type    : 'inner',
                             first   : 'users.id',
                             operator: '=',
                             second  : 'posts.user_id',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" INNER JOIN "posts" ON "users"."id" = "posts"."user_id"', actual: $sql);
    }

    #[Test]
    public function compile_select_with_wheres() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'status',
                             operator: '=',
                             value   : 'active',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" WHERE "status" = ?', actual: $sql);
    }

    #[Test]
    public function compile_select_with_multiple_wheres() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'status',
                             operator: '=',
                             value   : 'active',
                         ),
                         new WhereNode(
                             column  : 'age',
                             operator: '>=',
                             value   : 18,
                             boolean : 'AND',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" WHERE "status" = ? AND "age" >= ?', actual: $sql);
    }

    #[Test]
    public function compile_select_with_in_clause() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'id',
                             operator: 'IN',
                             value   : [1, 2, 3],
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" WHERE "id" IN (?, ?, ?)', actual: $sql);
    }

    #[Test]
    public function compile_select_with_between_clause() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'products',
            wheres : [
                         new WhereNode(
                             column  : 'price',
                             operator: 'BETWEEN',
                             value   : [10, 100],
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "products" WHERE "price" BETWEEN ? AND ?', actual: $sql);
    }

    #[Test]
    public function compile_select_with_null_check() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'deleted_at',
                             operator: 'IS NULL',
                             type    : 'Null',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" WHERE "deleted_at" IS NULL', actual: $sql);
    }

    #[Test]
    public function compile_select_with_raw_where() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'YEAR(created_at) = 2024',
                             operator: '',
                             type    : 'Raw',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" WHERE YEAR(created_at) = 2024', actual: $sql);
    }

    #[Test]
    public function compile_select_without_from_returns_select_only() : void
    {
        $state = new QueryState(columns: ['*']);
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT *', actual: $sql);
    }

    #[Test]
    public function compile_insert_single_row() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: ['name' => 'John', 'email' => 'john@example.com'],
        );
        $sql   = $this->baseGrammar->compileInsert(queryState: $state);

        self::assertSame(
            expected: 'INSERT INTO "users" ("name", "email") VALUES (?, ?)',
            actual  : $sql,
        );
    }

    // ============================================================
    // INSERT COMPILATION
    // ============================================================

    #[Test]
    public function compile_insert_batch() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: [
                        ['name' => 'John', 'email' => 'john@example.com'],
                        ['name' => 'Jane', 'email' => 'jane@example.com'],
                    ],
        );
        $sql   = $this->baseGrammar->compileInsert(queryState: $state);

        self::assertSame(
            expected: 'INSERT INTO "users" ("name", "email") VALUES (?, ?), (?, ?)',
            actual  : $sql,
        );
    }

    #[Test]
    public function compile_insert_with_expression() : void
    {
        $state = new QueryState(
            from  : 'posts',
            values: ['title' => 'Hello', 'created_at' => new Expression(value: 'NOW()')],
        );
        $sql   = $this->baseGrammar->compileInsert(queryState: $state);

        self::assertSame(
            expected: 'INSERT INTO "posts" ("title", "created_at") VALUES (?, NOW())',
            actual  : $sql,
        );
    }

    #[Test]
    public function compile_insert_throws_on_empty_values() : void
    {
        $state = new QueryState(from: 'users', values: []);

        $this->expectException(RuntimeException::class);
        $this->baseGrammar->compileInsert(queryState: $state);
    }

    #[Test]
    public function compile_update() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: ['name' => 'Updated'],
        );
        $sql   = $this->baseGrammar->compileUpdate(queryState: $state);

        self::assertSame(expected: 'UPDATE "users" SET "name" = ?', actual: $sql);
    }

    // ============================================================
    // UPDATE COMPILATION
    // ============================================================

    #[Test]
    public function compile_update_with_where() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: ['name' => 'Updated'],
            wheres: [
                        new WhereNode(
                            column  : 'id',
                            operator: '=',
                            value   : 1,
                        ),
                    ],
        );
        $sql   = $this->baseGrammar->compileUpdate(queryState: $state);

        self::assertSame(expected: 'UPDATE "users" SET "name" = ? WHERE "id" = ?', actual: $sql);
    }

    #[Test]
    public function compile_update_with_expression() : void
    {
        $state = new QueryState(
            from  : 'products',
            values: ['stock' => new Expression(value: 'stock - 1')],
        );
        $sql   = $this->baseGrammar->compileUpdate(queryState: $state);

        self::assertSame(expected: 'UPDATE "products" SET "stock" = stock - 1', actual: $sql);
    }

    #[Test]
    public function compile_delete() : void
    {
        $state = new QueryState(from: 'users');
        $sql   = $this->baseGrammar->compileDelete(queryState: $state);

        self::assertSame(expected: 'DELETE FROM "users"', actual: $sql);
    }

    // ============================================================
    // DELETE COMPILATION
    // ============================================================

    #[Test]
    public function compile_delete_with_where() : void
    {
        $state = new QueryState(
            from  : 'users',
            wheres: [
                        new WhereNode(
                            column  : 'id',
                            operator: '=',
                            value   : 1,
                        ),
                    ],
        );
        $sql   = $this->baseGrammar->compileDelete(queryState: $state);

        self::assertSame(expected: 'DELETE FROM "users" WHERE "id" = ?', actual: $sql);
    }

    #[Test]
    public function base_grammar_upsert_throws_runtime_exception() : void
    {
        $state = new QueryState(from: 'users', values: ['name' => 'John']);

        $this->expectException(RuntimeException::class);
        $this->baseGrammar->compileUpsert(queryState: $state, uniqueBy: ['name'], update: ['name']);
    }

    // ============================================================
    // UPSERT
    // ============================================================

    #[Test]
    public function mysql_grammar_upsert_generates_on_duplicate_key_update() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: ['name' => 'John', 'email' => 'john@example.com'],
        );
        $sql   = $this->mySQLGrammar->compileUpsert(
            queryState: $state,
            uniqueBy  : ['name'],
            update    : ['email'],
        );

        self::assertStringContainsString(needle: 'INSERT INTO `users`', haystack: $sql);
        self::assertStringContainsString(needle: 'ON DUPLICATE KEY UPDATE', haystack: $sql);
        self::assertStringContainsString(needle: '`email` = VALUES(`email`)', haystack: $sql);
    }

    #[Test]
    public function mysql_grammar_upsert_with_multiple_update_columns() : void
    {
        $state = new QueryState(
            from  : 'users',
            values: ['name' => 'John', 'email' => 'john@example.com', 'age' => 30],
        );
        $sql   = $this->mySQLGrammar->compileUpsert(
            queryState: $state,
            uniqueBy  : ['name'],
            update    : ['email', 'age'],
        );

        self::assertStringContainsString(needle: '`email` = VALUES(`email`)', haystack: $sql);
        self::assertStringContainsString(needle: '`age` = VALUES(`age`)', haystack: $sql);
    }

    #[Test]
    public function base_compile_truncate() : void
    {
        $sql = $this->baseGrammar->compileTruncate(table: 'users');

        self::assertSame(expected: 'TRUNCATE "users"', actual: $sql);
    }

    // ============================================================
    // TRUNCATE / DROP
    // ============================================================

    #[Test]
    public function mysql_compile_truncate() : void
    {
        $sql = $this->mySQLGrammar->compileTruncate(table: 'users');

        self::assertSame(expected: 'TRUNCATE TABLE `users`', actual: $sql);
    }

    #[Test]
    public function base_compile_drop_if_exists() : void
    {
        $sql = $this->baseGrammar->compileDropIfExists(table: 'users');

        self::assertSame(expected: 'DROP TABLE IF EXISTS "users"', actual: $sql);
    }

    #[Test]
    public function mysql_compile_drop_if_exists() : void
    {
        $sql = $this->mySQLGrammar->compileDropIfExists(table: 'users');

        self::assertSame(expected: 'DROP TABLE IF EXISTS `users`', actual: $sql);
    }

    #[Test]
    public function base_compile_create_database() : void
    {
        $sql = $this->baseGrammar->compileCreateDatabase(name: 'mydb');

        self::assertSame(expected: 'CREATE DATABASE "mydb"', actual: $sql);
    }

    #[Test]
    public function base_compile_drop_database() : void
    {
        $sql = $this->baseGrammar->compileDropDatabase(name: 'mydb');

        self::assertSame(expected: 'DROP DATABASE "mydb"', actual: $sql);
    }

    #[Test]
    public function base_random_order_uses_random() : void
    {
        self::assertSame(expected: 'RANDOM()', actual: $this->baseGrammar->compileRandomOrder());
    }

    // ============================================================
    // RANDOM ORDER
    // ============================================================

    #[Test]
    public function mysql_random_order_uses_rand() : void
    {
        self::assertSame(expected: 'RAND()', actual: $this->mySQLGrammar->compileRandomOrder());
    }

    #[Test]
    public function compile_select_with_cte() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'completed_orders',
            ctes   : [
                         ['name' => 'completed_orders', 'query' => 'SELECT * FROM orders WHERE status = "completed"', 'recursive' => false],
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertStringContainsString(needle: 'WITH "completed_orders" AS', haystack: $sql);
        self::assertStringNotContainsString(needle: 'RECURSIVE', haystack: $sql);
    }

    // ============================================================
    // CTE COMPILATION
    // ============================================================

    #[Test]
    public function compile_select_with_recursive_cte() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'tree',
            ctes   : [
                         ['name' => 'tree', 'query' => 'SELECT 1', 'recursive' => true],
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertStringContainsString(needle: 'WITH RECURSIVE', haystack: $sql);
    }

    #[Test]
    public function compile_select_with_cte_using_query_state() : void
    {
        $subQuery = new QueryState(
            columns: ['*'],
            from   : 'orders',
            wheres : [
                         new WhereNode(
                             column  : 'status',
                             operator: '=',
                             value   : 'completed',
                         ),
                     ],
        );

        $state = new QueryState(
            columns: ['*'],
            from   : 'completed_orders',
            ctes   : [
                         ['name' => 'completed_orders', 'query' => $subQuery, 'recursive' => false],
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertStringContainsString(needle: 'WITH', haystack: $sql);
        self::assertStringContainsString(needle: '"completed_orders" AS (SELECT', haystack: $sql);
    }

    #[Test]
    public function compile_select_with_cross_join() : void
    {
        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            joins  : [
                         new JoinNode(
                             table: 'roles',
                             type : 'cross',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: 'SELECT * FROM "users" CROSS JOIN "roles"', actual: $sql);
    }

    // ============================================================
    // CROSS JOIN
    // ============================================================

    #[Test]
    public function compile_select_with_nested_where() : void
    {
        $nestedBuilder        = new QueryBuilder(
            grammar     : $this->baseGrammar,
            orchestrator: new QueryOrchestrator(
                              executor: new class implements ExecutorInterface {
                                          public function query(string $sql, array $bindings = [], ExecutionScope|null $executionScope = null) : array { return []; }

                                          public function execute(string $sql, array $bindings = [], ExecutionScope|null $executionScope = null) : ExecutionResult { return ExecutionResult::success(affectedRows: 0); }

                                          public function getDriverName() : string { return 'mysql'; }
                                      },
                          ),
        );
        $nestedBuilder->state = $nestedBuilder->state
            ->withColumns(columns: ['*'])
            ->addWhere(where: new WhereNode(column: 'role', operator: '=', value: 'admin'));

        $state = new QueryState(
            columns: ['*'],
            from   : 'users',
            wheres : [
                         new WhereNode(
                             column  : 'status',
                             operator: '=',
                             value   : 'active',
                         ),
                         new NestedWhereNode(
                             query  : $nestedBuilder,
                             boolean: 'AND',
                         ),
                     ],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertStringContainsString(needle: 'WHERE "status" = ? AND ("role" = ?)', haystack: $sql);
    }

    // ============================================================
    // NESTED WHERE
    // ============================================================

    #[Test]
    public function compile_select_with_window_function() : void
    {
        $state = new QueryState(
            columns: ['name', 'running_total'],
            from   : 'orders',
            windows: ['running_total' => '(PARTITION BY customer_id ORDER BY created_at)'],
        );
        $sql   = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertStringContainsString(needle: '"running_total" OVER', haystack: $sql);
    }

    // ============================================================
    // WINDOW FUNCTIONS
    // ============================================================

    #[Test]
    public function compilation_is_deterministic() : void
    {
        $state = new QueryState(
            columns: ['id', 'name'],
            from   : 'users',
            wheres : [
                         new WhereNode(column: 'status', operator: '=', value: 'active'),
                     ],
            orders : [
                         new OrderNode(column: 'name', direction: 'ASC'),
                     ],
            limit  : 10,
        );

        $sql1 = $this->baseGrammar->compileSelect(queryState: $state);
        $sql2 = $this->baseGrammar->compileSelect(queryState: $state);

        self::assertSame(expected: $sql1, actual: $sql2);
    }

    // ============================================================
    // DETERMINISM
    // ============================================================

    protected function setUp() : void
    {
        $this->baseGrammar  = new BaseGrammar();
        $this->mySQLGrammar = new MySQLGrammar();
    }
}
