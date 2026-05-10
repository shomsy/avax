<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Exceptions\InvalidCriteriaException;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\ExecutorInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects\Expression;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Comprehensive tests for the QueryBuilder fluent API.
 *
 * Covers select, where, join, group, order, limit, offset, insert, update, delete,
 * pretend mode, cloning, immutability, and security.
 */
final class QueryBuilderTest extends TestCase
{
    private MySQLGrammar                                               $grammar;
    private QueryOrchestrator            $orchestrator;
    private ExecutorInterface&MockObject $executor;

    #[Test]
    public function select_defaults_to_asterisk() : void
    {
        $builder = $this->createBuilder()->from(table: 'users');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertSame(expected: 'SELECT * FROM `users`', actual: $sql);
    }

    private function createBuilder() : QueryBuilder
    {
        return new QueryBuilder(
            grammar     : $this->grammar,
            orchestrator: $this->orchestrator,
        );
    }

    // ============================================================
    // SELECT
    // ============================================================

    #[Test]
    public function select_with_specific_columns() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->select('id', 'name', 'email');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertSame(expected: 'SELECT `id`, `name`, `email` FROM `users`', actual: $sql);
    }

    #[Test]
    public function select_is_immutable() : void
    {
        $builder    = $this->createBuilder()->from(table: 'users');
        $newBuilder = $builder->select('id', 'name');

        self::assertSame(expected: ['*'], actual: $builder->state->columns);
        self::assertSame(expected: ['id', 'name'], actual: $newBuilder->state->columns);
    }

    #[Test]
    public function select_raw_injects_trusted_sql() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->selectRaw('COUNT(*) as total');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'COUNT(*) as total', haystack: $sql);
    }

    #[Test]
    public function select_raw_rejects_dangerous_input() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw('id; DROP TABLE users');
    }

    #[Test]
    public function select_raw_rejects_comments() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw('id -- comment');
    }

    #[Test]
    public function select_raw_rejects_block_comments() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw('id /* comment */');
    }

    #[Test]
    public function select_raw_rejects_control_characters() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw("id\x00");
    }

    #[Test]
    public function select_raw_rejects_empty_input() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw('');
    }

    #[Test]
    public function select_raw_rejects_non_ascii() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->selectRaw("id \xc3\xa9");
    }

    #[Test]
    public function distinct_adds_distinct_keyword() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->select('name')->distinct();
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'SELECT DISTINCT', haystack: $sql);
        self::assertStringContainsString(needle: '`name`', haystack: $sql);
    }

    #[Test]
    public function where_with_two_args_uses_equals_operator() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->where('status', 'active');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `status` = ?', haystack: $sql);
        self::assertSame(expected: ['active'], actual: $builder->state->getBindings());
    }

    // ============================================================
    // WHERE CONDITIONS
    // ============================================================

    #[Test]
    public function where_with_three_args_uses_specified_operator() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->where('age', '>=', 18);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `age` >= ?', haystack: $sql);
        self::assertSame(expected: [18], actual: $builder->state->getBindings());
    }

    #[Test]
    public function multiple_where_conditions_use_and() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('status', 'active')
            ->where('age', '>=', 18);

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `status` = ? AND `age` >= ?', haystack: $sql);
    }

    #[Test]
    public function or_where_uses_or_boolean() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('status', 'active')
            ->orWhere('role', 'admin');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'OR `role` = ?', haystack: $sql);
    }

    #[Test]
    public function where_in_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->whereIn('id', [1, 2, 3]);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `id` IN (?, ?, ?)', haystack: $sql);
        self::assertSame(expected: [1, 2, 3], actual: $builder->state->getBindings());
    }

    #[Test]
    public function where_in_with_empty_array() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->whereIn('id', []);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `id` IN ()', haystack: $sql);
    }

    #[Test]
    public function where_not_in_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->whereIn(column: 'id', values: [1, 2], not: true);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `id` NOT IN (?, ?)', haystack: $sql);
    }

    #[Test]
    public function or_where_in_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('status', 'active')
            ->orWhereIn('role', ['admin', 'moderator']);

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        // orWhereIn adds a WhereNode with IN operator and OR boolean
        self::assertStringContainsString(needle: '`role`', haystack: $sql);
        self::assertStringContainsString(needle: 'OR', haystack: $sql);
        self::assertStringContainsString(needle: 'IN (?, ?)', haystack: $sql);
        $bindings = $builder->state->getBindings();
        self::assertContains(needle: 'active', haystack: $bindings);
        self::assertContains(needle: 'admin', haystack: $bindings);
        self::assertContains(needle: 'moderator', haystack: $bindings);
    }

    #[Test]
    public function where_between_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'products')->whereBetween('price', [10, 100]);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `price` BETWEEN ? AND ?', haystack: $sql);
        self::assertSame(expected: [10, 100], actual: $builder->state->getBindings());
    }

    #[Test]
    public function where_between_requires_exactly_two_values() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidArgumentException::class);
        $builder->whereBetween('price', [10]);
    }

    #[Test]
    public function where_not_between_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'products')->whereBetween(column: 'price', values: [10, 100], not: true);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `price` NOT BETWEEN ? AND ?', haystack: $sql);
    }

    #[Test]
    public function where_null_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->whereNull('deleted_at');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `deleted_at` IS NULL', haystack: $sql);
    }

    #[Test]
    public function where_not_null_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->whereNotNull('email');
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `email` IS NOT NULL', haystack: $sql);
    }

    #[Test]
    public function nested_where_creates_parentheses() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('status', 'active')
            ->where(static fn ($q) => $q->where('role', 'admin')->orWhere('role', 'moderator'));

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `status` = ?', haystack: $sql);
        self::assertStringContainsString(needle: '(`role` = ?', haystack: $sql);
        self::assertStringContainsString(needle: 'OR `role`', haystack: $sql);
    }

    #[Test]
    public function join_compiles_inner_join() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->join('posts', 'users.id', '=', 'posts.user_id');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'INNER JOIN `posts` ON `users`.`id` = `posts`.`user_id`', haystack: $sql);
    }

    // ============================================================
    // JOINS
    // ============================================================

    #[Test]
    public function left_join_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->leftJoin('posts', 'users.id', '=', 'posts.user_id');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'LEFT JOIN `posts` ON `users`.`id` = `posts`.`user_id`', haystack: $sql);
    }

    #[Test]
    public function right_join_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->rightJoin('posts', 'users.id', '=', 'posts.user_id');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'RIGHT JOIN `posts` ON `users`.`id` = `posts`.`user_id`', haystack: $sql);
    }

    #[Test]
    public function cross_join_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->crossJoin('roles');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'CROSS JOIN `roles`', haystack: $sql);
    }

    #[Test]
    public function join_with_closure_uses_complex_on_clause() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->join('posts', static function ($join) {
                $join->on('users.id', '=', 'posts.user_id')
                    ->on('posts.published', '=', '1');
            });

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'INNER JOIN `posts` ON `users`.`id` = `posts`.`user_id`', haystack: $sql);
        self::assertStringContainsString(needle: '`posts`.`published`', haystack: $sql);
    }

    #[Test]
    public function group_by_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'orders')
            ->groupBy('customer_id');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'GROUP BY `customer_id`', haystack: $sql);
    }

    // ============================================================
    // GROUP BY / HAVING
    // ============================================================

    #[Test]
    public function group_by_accepts_multiple_columns() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'orders')
            ->groupBy('customer_id', 'status');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'GROUP BY `customer_id`, `status`', haystack: $sql);
    }

    #[Test]
    public function having_stores_in_state() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'orders')
            ->groupBy('customer_id')
            ->having('total', '>', 100);

        // Having is stored in state for later compilation
        self::assertCount(expectedCount: 1, haystack: $builder->state->havings);
        self::assertSame(expected: 'total', actual: $builder->state->havings[0]['column']);
        self::assertSame(expected: '>', actual: $builder->state->havings[0]['operator']);
        self::assertSame(expected: 100, actual: $builder->state->havings[0]['value']);
    }

    #[Test]
    public function order_by_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->orderBy('name', 'ASC');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'ORDER BY `name` ASC', haystack: $sql);
    }

    // ============================================================
    // ORDER BY
    // ============================================================

    #[Test]
    public function order_by_desc_compiles_correctly() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->orderByDesc('created_at');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'ORDER BY `created_at` DESC', haystack: $sql);
    }

    #[Test]
    public function latest_uses_created_at_desc() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->latest();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'ORDER BY `created_at` DESC', haystack: $sql);
    }

    #[Test]
    public function oldest_uses_created_at_asc() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->oldest();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'ORDER BY `created_at` ASC', haystack: $sql);
    }

    #[Test]
    public function in_random_order_uses_rand() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->inRandomOrder();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'ORDER BY RAND()', haystack: $sql);
    }

    #[Test]
    public function limit_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->limit(10);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'LIMIT 10', haystack: $sql);
    }

    // ============================================================
    // LIMIT / OFFSET
    // ============================================================

    #[Test]
    public function limit_rejects_negative_values() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->limit(-1);
    }

    #[Test]
    public function offset_compiles_correctly() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->offset(20);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'OFFSET 20', haystack: $sql);
    }

    #[Test]
    public function offset_rejects_negative_values() : void
    {
        $builder = $this->createBuilder();

        $this->expectException(InvalidCriteriaException::class);
        $builder->offset(-5);
    }

    #[Test]
    public function pagination_combines_limit_and_offset() : void
    {
        $builder = $this->createBuilder()->from(table: 'users')->limit(10)->offset(20);
        $sql     = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'LIMIT 10', haystack: $sql);
        self::assertStringContainsString(needle: 'OFFSET 20', haystack: $sql);
    }

    #[Test]
    public function insert_compiles_correct_sql() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql, array $bindings) : ExecutionResult {
                self::assertStringContainsString(needle: 'INSERT INTO `users`', haystack: $sql);
                self::assertStringContainsString(needle: '(`name`, `email`)', haystack: $sql);
                self::assertStringContainsString(needle: 'VALUES (?, ?)', haystack: $sql);
                self::assertSame(expected: ['John', 'john@example.com'], actual: $bindings);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->insert(values: ['name' => 'John', 'email' => 'john@example.com']);

        self::assertTrue(condition: $result);
    }

    // ============================================================
    // INSERT
    // ============================================================

    #[Test]
    public function insert_get_id_returns_generated_id() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturn(ExecutionResult::success(affectedRows: 1, lastInsertId: 42));

        $builder = $this->createBuilder()->from(table: 'users');
        $id      = $builder->insertGetId(values: ['name' => 'Jane']);

        self::assertSame(expected: 42, actual: $id);
    }

    #[Test]
    public function insert_get_id_rejects_batch_insert() : void
    {
        $builder = $this->createBuilder()->from(table: 'users');

        $this->expectException(RuntimeException::class);
        $builder->insertGetId(values: [
                                          ['name' => 'John'],
                                          ['name' => 'Jane'],
                                      ]);
    }

    #[Test]
    public function insert_get_id_throws_on_failure() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturn(ExecutionResult::failure());

        $builder = $this->createBuilder()->from(table: 'users');

        $this->expectException(RuntimeException::class);
        $builder->insertGetId(values: ['name' => 'Jane']);
    }

    #[Test]
    public function insert_get_id_throws_when_no_identifier_returned() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturn(ExecutionResult::success(affectedRows: 1, lastInsertId: null));

        $builder = $this->createBuilder()->from(table: 'users');

        $this->expectException(RuntimeException::class);
        $builder->insertGetId(values: ['name' => 'Jane']);
    }

    #[Test]
    public function update_compiles_correct_sql() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql, array $bindings) : ExecutionResult {
                self::assertStringContainsString(needle: 'UPDATE `users`', haystack: $sql);
                self::assertStringContainsString(needle: 'SET `email` = ?', haystack: $sql);
                self::assertStringContainsString(needle: 'WHERE `id` = ?', haystack: $sql);
                self::assertSame(expected: ['new@example.com', 1], actual: $bindings);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('id', '=', 1);

        $result = $builder->update(values: ['email' => 'new@example.com']);

        self::assertTrue(condition: $result);
    }

    // ============================================================
    // UPDATE
    // ============================================================

    #[Test]
    public function delete_compiles_correct_sql() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql, array $bindings) : ExecutionResult {
                self::assertStringContainsString(needle: 'DELETE FROM `users`', haystack: $sql);
                self::assertStringContainsString(needle: 'WHERE `id` = ?', haystack: $sql);
                self::assertSame(expected: [1], actual: $bindings);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('id', '=', 1);

        $result = $builder->delete();

        self::assertTrue(condition: $result);
    }

    // ============================================================
    // DELETE
    // ============================================================

    #[Test]
    public function pretend_mode_does_not_execute_queries() : void
    {
        $this->executor->expects(self::never())->method('query');

        $builder = $this->createBuilder()->from(table: 'users')->pretend();
        $result  = $builder->get();

        self::assertSame(expected: [], actual: $result);
    }

    // ============================================================
    // PRETEND MODE
    // ============================================================

    #[Test]
    public function pretend_mode_returns_success_for_mutations() : void
    {
        $this->executor->expects(self::never())->method('execute');

        $builder = $this->createBuilder()->from(table: 'users')->pretend();
        $result  = $builder->insert(values: ['name' => 'Test']);

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function pretend_mode_clones_builder() : void
    {
        $builder        = $this->createBuilder();
        $pretendBuilder = $builder->pretend();

        self::assertNotSame(expected: $builder, actual: $pretendBuilder);
    }

    #[Test]
    public function newQuery_creates_fresh_instance() : void
    {
        $builder    = $this->createBuilder()->from(table: 'users')->where('id', 1);
        $newBuilder = $builder->newQuery();

        self::assertEmpty(actual: $newBuilder->state->wheres);
        self::assertNull(actual: $newBuilder->state->from);
    }

    // ============================================================
    // CLONING / IMMUTABILITY
    // ============================================================

    #[Test]
    public function clone_preserves_grammar_and_orchestrator() : void
    {
        $builder = $this->createBuilder();
        $clone   = clone $builder;

        self::assertSame(expected: $builder->getGrammar(), actual: $clone->getGrammar());
    }

    #[Test]
    public function fluent_methods_return_new_instances() : void
    {
        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder
            ->select('id')
            ->where('status', 'active')
            ->orderBy('name')
            ->limit(10);

        self::assertNotSame(expected: $builder, actual: $result);
        self::assertSame(expected: ['*'], actual: $builder->state->columns);
        self::assertEmpty(actual: $builder->state->wheres);
    }

    #[Test]
    public function find_builds_where_id_query() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturnCallback(static function (string $sql) : array {
                self::assertStringContainsString(needle: 'WHERE `id` = ?', haystack: $sql);

                return [['id' => 1, 'name' => 'John']];
            });

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->find(id: 1);

        self::assertIsArray(actual: $result);
        self::assertSame(expected: 'John', actual: $result['name']);
    }

    // ============================================================
    // FIND / FIRST / VALUE / PLUCK
    // ============================================================

    #[Test]
    public function first_returns_single_record() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 1, 'name' => 'John'], ['id' => 2, 'name' => 'Jane']]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->first();

        self::assertIsArray(actual: $result);
        self::assertSame(expected: 'John', actual: $result['name']);
    }

    #[Test]
    public function first_with_key_returns_scalar() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 1, 'name' => 'John']]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->first(key: 'name');

        self::assertSame(expected: 'John', actual: $result);
    }

    #[Test]
    public function first_with_callable_transforms_result() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 1, 'name' => 'John']]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->first(key: static fn ($row) => strtoupper(string: $row['name']));

        self::assertSame(expected: 'JOHN', actual: $result);
    }

    #[Test]
    public function first_returns_default_when_no_results() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->first(default: 'no results');

        self::assertSame(expected: 'no results', actual: $result);
    }

    #[Test]
    public function first_with_dotted_key_accesses_nested_data() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['meta' => ['role' => 'admin']]]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->first(key: 'meta.role');

        self::assertSame(expected: 'admin', actual: $result);
    }

    #[Test]
    public function value_returns_single_column_value() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['name' => 'John']]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->value(column: 'name');

        self::assertSame(expected: 'John', actual: $result);
    }

    #[Test]
    public function value_returns_default_when_no_results() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->value(column: 'name', default: 'unknown');

        self::assertSame(expected: 'unknown', actual: $result);
    }

    #[Test]
    public function pluck_returns_flat_array() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([
                             ['name' => 'John'],
                             ['name' => 'Jane'],
                         ]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->pluck(value: 'name');

        self::assertSame(expected: ['John', 'Jane'], actual: $result);
    }

    #[Test]
    public function pluck_with_key_returns_associative_array() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([
                             ['id' => 1, 'name' => 'John'],
                             ['id' => 2, 'name' => 'Jane'],
                         ]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->pluck(value: 'name', key: 'id');

        self::assertSame(expected: [1 => 'John', 2 => 'Jane'], actual: $result);
    }

    #[Test]
    public function count_returns_integer() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['aggregate' => 42]]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->count();

        self::assertSame(expected: 42, actual: $result);
    }

    // ============================================================
    // COUNT / EXISTS
    // ============================================================

    #[Test]
    public function count_returns_zero_on_empty_result() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->count();

        self::assertSame(expected: 0, actual: $result);
    }

    #[Test]
    public function count_on_specific_column() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturnCallback(static function (string $sql) : array {
                self::assertStringContainsString(needle: 'COUNT(email)', haystack: $sql);

                return [['aggregate' => 10]];
            });

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->count(column: 'email');

        self::assertSame(expected: 10, actual: $result);
    }

    #[Test]
    public function exists_returns_true_when_records_found() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['id' => 1]]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->exists();

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function exists_returns_false_when_no_records() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([]);

        $builder = $this->createBuilder()->from(table: 'users');
        $result  = $builder->exists();

        self::assertFalse(condition: $result);
    }

    #[Test]
    public function raw_creates_expression_object() : void
    {
        $builder    = $this->createBuilder();
        $expression = $builder->raw(value: 'NOW()');

        self::assertInstanceOf(expected: Expression::class, actual: $expression);
        self::assertSame(expected: 'NOW()', actual: $expression->getValue());
        self::assertSame(expected: 'NOW()', actual: (string) $expression);
    }

    // ============================================================
    // RAW EXPRESSIONS
    // ============================================================

    #[Test]
    public function statement_executes_raw_sql() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql, array $bindings) : ExecutionResult {
                self::assertSame(expected: 'OPTIMIZE TABLE users', actual: $sql);
                self::assertEmpty(actual: $bindings);

                return ExecutionResult::success(affectedRows: 0);
            });

        $builder = $this->createBuilder();
        $result  = $builder->statement(query: 'OPTIMIZE TABLE users');

        self::assertTrue(condition: $result);
    }

    // ============================================================
    // STATEMENT
    // ============================================================

    #[Test]
    public function upsert_compiles_on_duplicate_key_update() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql) : ExecutionResult {
                self::assertStringContainsString(needle: 'INSERT INTO `users`', haystack: $sql);
                self::assertStringContainsString(needle: 'ON DUPLICATE KEY UPDATE', haystack: $sql);
                self::assertStringContainsString(needle: '`email` = VALUES(`email`)', haystack: $sql);

                return ExecutionResult::success(affectedRows: 2);
            });

        $builder  = $this->createBuilder()->from(table: 'users');
        $affected = $builder->upsert(
            values  : ['name' => 'John', 'email' => 'john@example.com'],
            uniqueBy: ['name'],
            update  : ['email'],
        );

        self::assertSame(expected: 2, actual: $affected);
    }

    // ============================================================
    // ADVANCED MUTATIONS (upsert, increment, decrement)
    // ============================================================

    #[Test]
    public function upsert_returns_zero_for_empty_values() : void
    {
        $this->executor->expects(self::never())->method('execute');

        $builder  = $this->createBuilder()->from(table: 'users');
        $affected = $builder->upsert(values: [], uniqueBy: ['id']);

        self::assertSame(expected: 0, actual: $affected);
    }

    #[Test]
    public function increment_builds_atomic_update() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql) : ExecutionResult {
                self::assertStringContainsString(needle: '`views` = `views` + 1', haystack: $sql);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()->from(table: 'posts')->where('id', 1);
        $result  = $builder->increment(column: 'views');

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function decrement_builds_atomic_update() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql) : ExecutionResult {
                self::assertStringContainsString(needle: '`stock` = `stock` - 5', haystack: $sql);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()->from(table: 'products')->where('id', 1);
        $result  = $builder->decrement(column: 'stock', amount: 5);

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function max_returns_maximum_value() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['aggregate' => 999]]);

        $builder = $this->createBuilder()->from(table: 'products');
        $result  = $builder->max(column: 'price');

        self::assertSame(expected: 999, actual: $result);
    }

    // ============================================================
    // AGGREGATES
    // ============================================================

    #[Test]
    public function min_returns_minimum_value() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['aggregate' => 1]]);

        $builder = $this->createBuilder()->from(table: 'products');
        $result  = $builder->min(column: 'price');

        self::assertSame(expected: 1, actual: $result);
    }

    #[Test]
    public function avg_returns_average_value() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['aggregate' => 50.5]]);

        $builder = $this->createBuilder()->from(table: 'products');
        $result  = $builder->avg(column: 'price');

        self::assertSame(expected: 50.5, actual: $result);
    }

    #[Test]
    public function sum_returns_total() : void
    {
        $this->executor->expects(self::once())
            ->method('query')
            ->willReturn([['aggregate' => 1000]]);

        $builder = $this->createBuilder()->from(table: 'orders');
        $result  = $builder->sum(column: 'amount');

        self::assertSame(expected: 1000, actual: $result);
    }

    #[Test]
    public function when_executes_callback_on_true_condition() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->when(condition: true, callback: static fn ($q) => $q->where('active', 1));

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `active` = ?', haystack: $sql);
    }

    // ============================================================
    // CONTROL STRUCTURES
    // ============================================================

    #[Test]
    public function when_skips_callback_on_false_condition() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->when(condition: false, callback: static fn ($q) => $q->where('active', 1));

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringNotContainsString(needle: 'WHERE', haystack: $sql);
    }

    #[Test]
    public function when_executes_default_callback_on_false() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->when(
                condition: false,
                callback : static fn ($q) => $q->where('active', 1),
                default  : static fn ($q) => $q->where('status', 'pending'),
            );

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `status` = ?', haystack: $sql);
    }

    #[Test]
    public function unless_executes_callback_on_false_condition() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->unless(condition: false, callback: static fn ($q) => $q->where('active', 1));

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `active` = ?', haystack: $sql);
    }

    #[Test]
    public function tap_allows_side_effects() : void
    {
        $tapped  = false;
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->tap(static function ($q) use (&$tapped) {
                $tapped = true;
            });

        self::assertTrue(condition: $tapped);
        self::assertInstanceOf(expected: QueryBuilder::class, actual: $builder);
    }

    #[Test]
    public function with_adds_common_table_expression() : void
    {
        $subQuery = $this->createBuilder()->from(table: 'orders')->where('status', 'completed');

        $builder = $this->createBuilder()
            ->with(name: 'completed_orders', query: $subQuery)
            ->from(table: 'completed_orders');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WITH', haystack: $sql);
        self::assertStringContainsString(needle: '`completed_orders` AS', haystack: $sql);
    }

    // ============================================================
    // CTEs AND WINDOW FUNCTIONS
    // ============================================================

    #[Test]
    public function with_recursive_adds_recursive_cte() : void
    {
        $builder = $this->createBuilder()
            ->withRecursive(name: 'tree', query: 'SELECT 1')
            ->from(table: 'tree');

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WITH RECURSIVE', haystack: $sql);
    }

    #[Test]
    public function using_soft_deletes_enables_filter() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->usingSoftDeletes()
            ->withSoftDeleteFilter();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `deleted_at` IS NULL', haystack: $sql);
    }

    // ============================================================
    // SOFT DELETES
    // ============================================================

    #[Test]
    public function with_trashed_includes_deleted_records() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->usingSoftDeletes()
            ->withTrashed()
            ->withSoftDeleteFilter();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringNotContainsString(needle: 'deleted_at', haystack: $sql);
    }

    #[Test]
    public function only_trashed_filters_for_deleted_records() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->usingSoftDeletes()
            ->onlyTrashed()
            ->withSoftDeleteFilter();

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'WHERE `deleted_at` IS NOT NULL', haystack: $sql);
    }

    #[Test]
    public function restore_nullifies_deleted_at() : void
    {
        $this->executor->expects(self::once())
            ->method('execute')
            ->willReturnCallback(static function (string $sql, array $bindings) : ExecutionResult {
                self::assertStringContainsString(needle: 'UPDATE', haystack: $sql);
                self::assertStringContainsString(needle: '`deleted_at`', haystack: $sql);
                self::assertContains(needle: null, haystack: $bindings);

                return ExecutionResult::success(affectedRows: 1);
            });

        $builder = $this->createBuilder()
            ->from(table: 'users')
            ->where('id', 1)
            ->usingSoftDeletes()
            ->onlyTrashed();

        $result = $builder->restore();

        self::assertTrue(condition: $result);
    }

    #[Test]
    public function get_grammar_returns_configured_grammar() : void
    {
        $builder = $this->createBuilder();

        self::assertInstanceOf(expected: MySQLGrammar::class, actual: $builder->getGrammar());
    }

    // ============================================================
    // GRAMMAR ACCESSOR
    // ============================================================

    #[Test]
    public function complex_query_compiles_all_clauses() : void
    {
        $builder = $this->createBuilder()
            ->from(table: 'orders')
            ->select('customer_id', 'SUM(amount) as total')
            ->join('customers', 'orders.customer_id', '=', 'customers.id')
            ->where('orders.status', 'completed')
            ->groupBy('customer_id')
            ->orderBy('total', 'DESC')
            ->limit(10)
            ->offset(20);

        $sql = $builder->getGrammar()->compileSelect(queryState: $builder->state);

        self::assertStringContainsString(needle: 'SELECT', haystack: $sql);
        self::assertStringContainsString(needle: 'FROM', haystack: $sql);
        self::assertStringContainsString(needle: 'JOIN', haystack: $sql);
        self::assertStringContainsString(needle: 'WHERE', haystack: $sql);
        self::assertStringContainsString(needle: 'GROUP BY', haystack: $sql);
        self::assertStringContainsString(needle: 'ORDER BY', haystack: $sql);
        self::assertStringContainsString(needle: 'LIMIT', haystack: $sql);
        self::assertStringContainsString(needle: 'OFFSET', haystack: $sql);
    }

    // ============================================================
    // FULL QUERY COMPILATION
    // ============================================================

    protected function setUp() : void
    {
        $this->grammar      = new MySQLGrammar();
        $this->executor     = $this->createMock(ExecutorInterface::class);
        $this->orchestrator = new QueryOrchestrator(executor: $this->executor);
    }
}
