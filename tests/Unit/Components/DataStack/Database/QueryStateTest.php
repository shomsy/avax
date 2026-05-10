<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\JoinNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\OrderNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\AST\WhereNode;
use Avax\Components\DataStack\Database\System\Capabilities\Query\State\QueryState;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the immutable QueryState value object.
 */
final class QueryStateTest extends TestCase
{
    #[Test]
    public function default_state_has_sensible_defaults() : void
    {
        $state = new QueryState();

        self::assertSame(expected: ['*'], actual: $state->columns);
        self::assertNull(actual: $state->from);
        self::assertNull(actual: $state->limit);
        self::assertNull(actual: $state->offset);
        self::assertFalse(condition: $state->distinct);
        self::assertEmpty(actual: $state->joins);
        self::assertEmpty(actual: $state->wheres);
        self::assertEmpty(actual: $state->groups);
        self::assertEmpty(actual: $state->havings);
        self::assertEmpty(actual: $state->orders);
        self::assertEmpty(actual: $state->values);
        self::assertEmpty(actual: $state->updateColumns);
        self::assertEmpty(actual: $state->ctes);
        self::assertEmpty(actual: $state->windows);
        self::assertEmpty(actual: $state->getBindings());
    }

    #[Test]
    public function with_from_sets_table() : void
    {
        $state    = new QueryState();
        $newState = $state->withFrom(table: 'users');

        self::assertNull(actual: $state->from);
        self::assertSame(expected: 'users', actual: $newState->from);
    }

    #[Test]
    public function with_columns_replaces_columns() : void
    {
        $state    = new QueryState();
        $newState = $state->withColumns(columns: ['id', 'name']);

        self::assertSame(expected: ['*'], actual: $state->columns);
        self::assertSame(expected: ['id', 'name'], actual: $newState->columns);
    }

    #[Test]
    public function with_distinct_sets_flag() : void
    {
        $state    = new QueryState();
        $newState = $state->withDistinct(distinct: true);

        self::assertFalse(condition: $state->distinct);
        self::assertTrue(condition: $newState->distinct);
    }

    #[Test]
    public function with_limit_sets_limit() : void
    {
        $state    = new QueryState();
        $newState = $state->withLimit(limit: 10);

        self::assertNull(actual: $state->limit);
        self::assertSame(expected: 10, actual: $newState->limit);
    }

    #[Test]
    public function with_offset_sets_offset() : void
    {
        $state    = new QueryState();
        $newState = $state->withOffset(offset: 20);

        self::assertNull(actual: $state->offset);
        self::assertSame(expected: 20, actual: $newState->offset);
    }

    #[Test]
    public function with_values_sets_mutation_payload() : void
    {
        $state    = new QueryState();
        $newState = $state->withValues(values: ['name' => 'John']);

        self::assertEmpty(actual: $state->values);
        self::assertSame(expected: ['name' => 'John'], actual: $newState->values);
    }

    #[Test]
    public function with_update_columns_sets_target_columns() : void
    {
        $state    = new QueryState();
        $newState = $state->withUpdateColumns(columns: ['email', 'name']);

        self::assertEmpty(actual: $state->updateColumns);
        self::assertSame(expected: ['email', 'name'], actual: $newState->updateColumns);
    }

    #[Test]
    public function add_join_appends_to_joins() : void
    {
        $state    = new QueryState();
        $joinNode = new JoinNode(table: 'posts', type: 'inner', first: 'users.id', operator: '=', second: 'posts.user_id');

        $newState = $state->addJoin(joinNode: $joinNode);

        self::assertEmpty(actual: $state->joins);
        self::assertCount(expectedCount: 1, haystack: $newState->joins);
        self::assertSame(expected: $joinNode, actual: $newState->joins[0]);
    }

    #[Test]
    public function add_where_appends_to_wheres() : void
    {
        $state     = new QueryState();
        $whereNode = new WhereNode(column: 'status', operator: '=', value: 'active');

        $newState = $state->addWhere(where: $whereNode);

        self::assertEmpty(actual: $state->wheres);
        self::assertCount(expectedCount: 1, haystack: $newState->wheres);
        self::assertSame(expected: $whereNode, actual: $newState->wheres[0]);
    }

    #[Test]
    public function add_group_appends_to_groups() : void
    {
        $state    = new QueryState();
        $newState = $state->addGroup(column: 'category');

        self::assertEmpty(actual: $state->groups);
        self::assertSame(expected: ['category'], actual: $newState->groups);
    }

    #[Test]
    public function with_groups_replaces_groups() : void
    {
        $state    = new QueryState()->addGroup(column: 'old');
        $newState = $state->withGroups(groups: ['new1', 'new2']);

        self::assertSame(expected: ['old'], actual: $state->groups);
        self::assertSame(expected: ['new1', 'new2'], actual: $newState->groups);
    }

    #[Test]
    public function add_having_appends_to_havings() : void
    {
        $state  = new QueryState();
        $having = ['column' => 'total', 'operator' => '>', 'value' => 100];

        $newState = $state->addHaving(having: $having);

        self::assertEmpty(actual: $state->havings);
        self::assertCount(expectedCount: 1, haystack: $newState->havings);
        self::assertSame(expected: $having, actual: $newState->havings[0]);
    }

    #[Test]
    public function add_order_appends_to_orders() : void
    {
        $state     = new QueryState();
        $orderNode = new OrderNode(column: 'name', direction: 'DESC');

        $newState = $state->addOrder(orderNode: $orderNode);

        self::assertEmpty(actual: $state->orders);
        self::assertCount(expectedCount: 1, haystack: $newState->orders);
        self::assertSame(expected: $orderNode, actual: $newState->orders[0]);
    }

    #[Test]
    public function with_cte_adds_common_table_expression() : void
    {
        $state    = new QueryState();
        $newState = $state->withCte(name: 'cte1', query: 'SELECT 1');

        self::assertCount(expectedCount: 1, haystack: $newState->ctes);
        self::assertSame(expected: 'cte1', actual: $newState->ctes[0]['name']);
        self::assertSame(expected: 'SELECT 1', actual: $newState->ctes[0]['query']);
        self::assertFalse(condition: $newState->ctes[0]['recursive']);
    }

    #[Test]
    public function with_cte_can_be_recursive() : void
    {
        $state    = new QueryState();
        $newState = $state->withCte(name: 'tree', query: 'SELECT 1', recursive: true);

        self::assertTrue(condition: $newState->ctes[0]['recursive']);
    }

    #[Test]
    public function with_window_adds_window_definition() : void
    {
        $state    = new QueryState();
        $newState = $state->withWindow(name: 'w1', window: '(PARTITION BY x)');

        self::assertArrayHasKey(key: 'w1', array: $newState->windows);
        self::assertSame(expected: '(PARTITION BY x)', actual: $newState->windows['w1']);
    }

    // ============================================================
    // BINDING BAG
    // ============================================================

    #[Test]
    public function add_binding_adds_single_value() : void
    {
        $state    = new QueryState();
        $newState = $state->addBinding(value: 'test');

        self::assertEmpty(actual: $state->getBindings());
        self::assertSame(expected: ['test'], actual: $newState->getBindings());
    }

    #[Test]
    public function merge_bindings_adds_multiple_values() : void
    {
        $state    = new QueryState();
        $newState = $state->mergeBindings(values: ['a', 'b', 'c']);

        self::assertSame(expected: ['a', 'b', 'c'], actual: $newState->getBindings());
    }

    #[Test]
    public function bindings_accumulate_across_operations() : void
    {
        $state = new QueryState()
            ->addBinding(value: 'a')
            ->addBinding(value: 'b')
            ->mergeBindings(values: ['c', 'd']);

        self::assertSame(expected: ['a', 'b', 'c', 'd'], actual: $state->getBindings());
    }

    #[Test]
    public function reset_bindings_clears_all() : void
    {
        $state = new QueryState()
            ->addBinding(value: 'a')
            ->addBinding(value: 'b');

        $cleared = $state->resetBindings();

        self::assertSame(expected: ['a', 'b'], actual: $state->getBindings());
        self::assertEmpty(actual: $cleared->getBindings());
    }

    // ============================================================
    // CHAINING
    // ============================================================

    #[Test]
    public function with_methods_can_be_chained() : void
    {
        $state = (new QueryState())
            ->withFrom(table: 'users')
            ->withColumns(columns: ['id', 'name'])
            ->withDistinct(distinct: true)
            ->withLimit(limit: 10)
            ->withOffset(offset: 20);

        self::assertSame(expected: 'users', actual: $state->from);
        self::assertSame(expected: ['id', 'name'], actual: $state->columns);
        self::assertTrue(condition: $state->distinct);
        self::assertSame(expected: 10, actual: $state->limit);
        self::assertSame(expected: 20, actual: $state->offset);
    }

    #[Test]
    public function each_with_method_produces_new_instance() : void
    {
        $state = new QueryState();

        $s1 = $state->withFrom(table: 'users');
        $s2 = $state->withFrom(table: 'posts');

        self::assertNotSame(expected: $s1, actual: $s2);
        self::assertSame(expected: 'users', actual: $s1->from);
        self::assertSame(expected: 'posts', actual: $s2->from);
    }

    // ============================================================
    // READONLY / IMMUTABILITY
    // ============================================================

    #[Test]
    public function original_state_is_not_mutated_by_with_methods() : void
    {
        $original = new QueryState(columns: ['*'], from: 'users');

        $modified = $original
            ->withColumns(columns: ['id'])
            ->withFrom(table: 'posts')
            ->addBinding(value: 'test');

        self::assertSame(expected: ['*'], actual: $original->columns);
        self::assertSame(expected: 'users', actual: $original->from);
        self::assertEmpty(actual: $original->getBindings());

        self::assertSame(expected: ['id'], actual: $modified->columns);
        self::assertSame(expected: 'posts', actual: $modified->from);
        self::assertSame(expected: ['test'], actual: $modified->getBindings());
    }

    // ============================================================
    // CONSTRUCTOR WITH EXPLICIT VALUES
    // ============================================================

    #[Test]
    public function constructor_accepts_explicit_values() : void
    {
        $wheres = [new WhereNode(column: 'status', operator: '=', value: 'active')];
        $orders = [new OrderNode(column: 'name', direction: 'ASC')];
        $joins  = [new JoinNode(table: 'posts', type: 'inner')];

        $state = new QueryState(
            columns      : ['id', 'name'],
            from         : 'users',
            joins        : $joins,
            wheres       : $wheres,
            groups       : ['category'],
            havings      : [],
            orders       : $orders,
            limit        : 10,
            offset       : 5,
            values       : ['email' => 'test@test.com'],
            updateColumns: ['email'],
            distinct     : true,
        );

        self::assertSame(expected: ['id', 'name'], actual: $state->columns);
        self::assertSame(expected: 'users', actual: $state->from);
        self::assertCount(expectedCount: 1, haystack: $state->joins);
        self::assertCount(expectedCount: 1, haystack: $state->wheres);
        self::assertSame(expected: ['category'], actual: $state->groups);
        self::assertCount(expectedCount: 1, haystack: $state->orders);
        self::assertSame(expected: 10, actual: $state->limit);
        self::assertSame(expected: 5, actual: $state->offset);
        self::assertSame(expected: ['email' => 'test@test.com'], actual: $state->values);
        self::assertSame(expected: ['email'], actual: $state->updateColumns);
        self::assertTrue(condition: $state->distinct);
    }

    #[Test]
    public function binding_bag_is_isolated_between_instances() : void
    {
        $state1 = new QueryState()->addBinding(value: 'a');
        $state2 = $state1->addBinding(value: 'b');

        self::assertSame(expected: ['a'], actual: $state1->getBindings());
        self::assertSame(expected: ['a', 'b'], actual: $state2->getBindings());
    }
}
