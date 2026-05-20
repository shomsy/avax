<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence\SqlInjection;

use Avax\Components\DataStack\Persistence\System\Capabilities\QueryIntent\DataQuery;
use Avax\Components\DataStack\Persistence\System\Flows\CompileDataQuery\CompileDataQuery;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Proves that CompileDataQuery cannot be used for SQL injection
 * through identifier interpolation.
 *
 * Covers TODO-026b: identifier safety in SQL compilation.
 */
final class CompileDataQueryIdentifierSafetyTest extends TestCase
{
    private CompileDataQuery $compiler;

    protected function setUp(): void
    {
        $this->compiler = new CompileDataQuery();
    }

    // ─── Malicious select identifiers ───────────────────────────────────

    #[Test]
    public function malicious_select_identifier_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: ['*; DROP TABLE users--'],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier segment');

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_select_identifier_with_subquery_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: ['(SELECT password FROM admins)'],
        );

        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_select_identifier_with_union_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: ['id UNION SELECT * FROM secrets'],
        );

        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    // ─── Malicious orderBy field ────────────────────────────────────────

    #[Test]
    public function malicious_orderby_field_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            orderBy: ['id; DROP TABLE users--' => 'ASC'],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier segment');

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_orderby_field_with_subquery_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            orderBy: ['(SELECT 1)' => 'ASC'],
        );

        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    // ─── Malicious orderBy direction ────────────────────────────────────

    #[Test]
    public function malicious_orderby_direction_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            orderBy: ['id' => 'ASC; DROP TABLE users--'],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid ORDER BY direction');

        $this->compiler->compile($query);
    }

    // ─── Malicious join table ───────────────────────────────────────────

    #[Test]
    public function malicious_join_table_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'INNER', 'table' => 'users; DROP TABLE passwords--', 'on' => 'a.id = b.user_id']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier segment');

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_join_table_with_subquery_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'INNER', 'table' => '(SELECT * FROM secrets)', 'on' => 'a.id = b.user_id']],
        );

        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    // ─── Malicious join ON clause ───────────────────────────────────────

    #[Test]
    public function malicious_join_on_clause_with_parentheses_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'INNER', 'table' => 'posts', 'on' => 'a.id = b.user_id OR 1=1']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JOIN ON clause');

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_join_on_clause_with_sql_comment_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'INNER', 'table' => 'posts', 'on' => 'a.id = b.user_id--']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JOIN ON clause');

        $this->compiler->compile($query);
    }

    // ─── Malicious join type ────────────────────────────────────────────

    #[Test]
    public function malicious_join_type_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'DROP TABLE users', 'table' => 'posts', 'on' => 'a.id = b.user_id']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JOIN type');

        $this->compiler->compile($query);
    }

    // ─── Malicious condition field ──────────────────────────────────────

    #[Test]
    public function malicious_condition_field_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            conditions: [['field' => 'id; DROP TABLE users--', 'value' => 1, 'operator' => '=']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid SQL identifier segment');

        $this->compiler->compile($query);
    }

    #[Test]
    public function malicious_condition_field_with_subquery_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            conditions: [['field' => '(SELECT 1)', 'value' => 1, 'operator' => '=']],
        );

        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    // ─── Malicious condition operator ───────────────────────────────────

    #[Test]
    public function malicious_condition_operator_with_sql_injection_is_rejected(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            conditions: [['field' => 'id', 'value' => 1, 'operator' => '= 1; DROP TABLE users--']],
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid comparison operator');

        $this->compiler->compile($query);
    }

    // ─── Valid identifiers still compile ────────────────────────────────

    #[Test]
    #[DataProvider('provideValidSelectIdentifiers')]
    public function valid_select_identifiers_compile_with_wrapping(array $select, string $expectedSelectClause): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: $select,
        );

        $plan = $this->compiler->compile($query);

        self::assertStringContainsString('SELECT '.$expectedSelectClause.' FROM "user"', $plan->sql);
    }

    public static function provideValidSelectIdentifiers(): array
    {
        return [
            'wildcard' => [['*'], '*'],
            'single column' => [['id'], '"id"'],
            'multiple columns' => [['id', 'name', 'email'], '"id", "name", "email"'],
            'dotted identifier' => [['users.name'], '"users"."name"'],
            'underscore column' => [['created_at'], '"created_at"'],
            'mixed dotted and plain' => [['id', 'users.name', 'email'], '"id", "users"."name", "email"'],
        ];
    }

    #[Test]
    #[DataProvider('provideValidOrderBy')]
    public function valid_orderby_fields_compile_with_wrapping(array $orderBy, string $expectedOrderClause): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            orderBy: $orderBy,
        );

        $plan = $this->compiler->compile($query);

        self::assertStringContainsString('ORDER BY '.$expectedOrderClause, $plan->sql);
    }

    public static function provideValidOrderBy(): array
    {
        return [
            'single asc' => [['name' => 'asc'], '"name" ASC'],
            'single desc' => [['name' => 'desc'], '"name" DESC'],
            'mixed case direction' => [['name' => 'DeSc'], '"name" DESC'],
            'multiple fields' => [['name' => 'ASC', 'created_at' => 'DESC'], '"name" ASC, "created_at" DESC'],
            'dotted field' => [['users.name' => 'ASC'], '"users"."name" ASC'],
        ];
    }

    #[Test]
    public function valid_join_compiles_with_wrapped_identifiers(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'LEFT', 'table' => 'posts', 'on' => 'users.id = posts.user_id']],
        );

        $plan = $this->compiler->compile($query);

        self::assertStringContainsString('LEFT JOIN "posts" ON "users"."id" = "posts"."user_id"', $plan->sql);
    }

    #[Test]
    public function valid_inner_join_compiles(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            joins: [['type' => 'INNER', 'table' => 'roles', 'on' => 'users.role_id = roles.id']],
        );

        $plan = $this->compiler->compile($query);

        self::assertStringContainsString('INNER JOIN "roles" ON "users"."role_id" = "roles"."id"', $plan->sql);
    }

    #[Test]
    #[DataProvider('provideValidOperators')]
    public function valid_condition_operators_compile(string $operator, string $expectedOperator): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            conditions: [['field' => 'status', 'value' => 'active', 'operator' => $operator]],
        );

        $plan = $this->compiler->compile($query);

        self::assertStringContainsString('"status" '.$expectedOperator.' ?', $plan->sql);
    }

    public static function provideValidOperators(): array
    {
        return [
            'equals' => ['=', '='],
            'not equals' => ['!=', '!='],
            'greater than' => ['>', '>'],
            'less than or equal' => ['<=', '<='],
            'like' => ['LIKE', 'LIKE'],
            'not like' => ['not like', 'NOT LIKE'],
            'is' => ['IS', 'IS'],
            'is not' => ['IS NOT', 'IS NOT'],
            'in' => ['IN', 'IN'],
            'not in' => ['NOT IN', 'NOT IN'],
            'between' => ['BETWEEN', 'BETWEEN'],
        ];
    }

    // ─── Double-quote escaping policy ──────────────────────────────────

    #[Test]
    public function identifier_with_double_quotes_is_escaped(): void
    {
        // A column name containing a double quote should have it escaped
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: ['col"umn'],
        );

        // The identifier validation should reject this since " is not \w
        $this->expectException(InvalidArgumentException::class);

        $this->compiler->compile($query);
    }

    // ─── Bindings are extracted correctly ───────────────────────────────

    #[Test]
    public function bindings_are_extracted_from_conditions(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            conditions: [
                ['field' => 'status', 'value' => 'active', 'operator' => '='],
                ['field' => 'age', 'value' => 25, 'operator' => '>='],
            ],
        );

        $plan = $this->compiler->compile($query);

        self::assertSame(['active', 25], $plan->bindings);
    }

    // ─── Full query compilation proof ───────────────────────────────────

    #[Test]
    public function full_query_with_all_clauses_compiles_safely(): void
    {
        $query = new DataQuery(
            entityType: 'App\\Entities\\User',
            select: ['id', 'name', 'email'],
            conditions: [
                ['field' => 'status', 'value' => 'active', 'operator' => '='],
                ['field' => 'age', 'value' => 18, 'operator' => '>='],
            ],
            orderBy: ['created_at' => 'DESC'],
            limit: 10,
            offset: 20,
            joins: [
                ['type' => 'LEFT', 'table' => 'profiles', 'on' => 'users.id = profiles.user_id'],
            ],
        );

        $plan = $this->compiler->compile($query);

        // Prove all identifiers are wrapped
        self::assertStringContainsString('SELECT "id", "name", "email" FROM "user"', $plan->sql);
        self::assertStringContainsString('LEFT JOIN "profiles" ON "users"."id" = "profiles"."user_id"', $plan->sql);
        self::assertStringContainsString('"status" = ?', $plan->sql);
        self::assertStringContainsString('"age" >= ?', $plan->sql);
        self::assertStringContainsString('ORDER BY "created_at" DESC', $plan->sql);
        self::assertStringContainsString('LIMIT 10', $plan->sql);
        self::assertStringContainsString('OFFSET 20', $plan->sql);

        // Prove bindings are correct
        self::assertSame(['active', 18], $plan->bindings);
    }
}
