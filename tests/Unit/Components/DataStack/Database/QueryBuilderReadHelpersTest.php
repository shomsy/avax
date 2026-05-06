<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Query\Builder\QueryBuilder;
use Avax\Components\DataStack\Database\System\Capabilities\Query\DTO\ExecutionResult;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\ExecutorInterface;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Execution\QueryOrchestrator;
use Avax\Components\DataStack\Database\System\Capabilities\Query\Grammar\MySQLGrammar;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\Trackers\ExecutionScope;
use PHPUnit\Framework\TestCase;

final class QueryBuilderReadHelpersTest extends TestCase
{
    public function test_where_in_between_join_offset_and_distinct_compile_sql_and_bindings(): void
    {
        $executor = new RecordingReadHelperExecutor(rows: [
            ['id' => 1, 'name' => 'Milos'],
        ]);

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $rows = $builder
            ->from(table: 'users')
            ->distinct()
            ->select('users.id', 'users.name')
            ->join(table: 'profiles', first: 'profiles.user_id', operator: '=', second: 'users.id')
            ->whereIn(column: 'users.id', values: [1, 2, 3])
            ->whereBetween(column: 'users.age', values: [30, 40])
            ->orderByDesc(column: 'users.created_at')
            ->limit(limit: 10)
            ->offset(offset: 20)
            ->get();

        self::assertSame([['id' => 1, 'name' => 'Milos']], $rows);
        self::assertCount(1, $executor->queries);

        $query = $executor->queries[0];
        $sql = self::normalizeSql($query['sql']);

        self::assertStringStartsWith('SELECT DISTINCT', $sql);
        self::assertMatchesRegularExpression('/FROM .*users/i', $sql);
        self::assertMatchesRegularExpression('/JOIN .*profiles.* ON .*profiles.*user_id.* = .*users.*id/i', $sql);
        self::assertMatchesRegularExpression('/WHERE .*users.*id.* IN \(\?, \?, \?\)/i', $sql);
        self::assertMatchesRegularExpression('/AND .*users.*age.* BETWEEN \? AND \?/i', $sql);
        self::assertMatchesRegularExpression('/ORDER BY .*users.*created_at.* DESC/i', $sql);
        self::assertMatchesRegularExpression('/LIMIT 10/i', $sql);
        self::assertMatchesRegularExpression('/OFFSET 20/i', $sql);

        self::assertSame([1, 2, 3, 30, 40], $query['bindings']);
    }

    private static function normalizeSql(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
    }

    public function test_first_value_count_and_find_use_expected_queries(): void
    {
        $executor = new RecordingReadHelperExecutor(rows: [
            ['id' => 123, 'name' => 'Milos', 'aggregate' => 7],
        ]);

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        self::assertSame(
            ['id' => 123, 'name' => 'Milos', 'aggregate' => 7],
            $builder->from(table: 'users')->first(),
        );

        self::assertSame(
            'Milos',
            $builder->from(table: 'users')->value(column: 'name'),
        );

        self::assertSame(
            7,
            $builder->from(table: 'users')->count(),
        );

        self::assertSame(
            ['id' => 123, 'name' => 'Milos', 'aggregate' => 7],
            $builder->from(table: 'users')->find(id: 123),
        );

        self::assertGreaterThanOrEqual(4, count($executor->queries));
    }

    public function test_nested_where_compiles_parenthesized_conditions_and_bindings(): void
    {
        $executor = new RecordingReadHelperExecutor(rows: [
            ['id' => 1, 'name' => 'Milos'],
        ]);

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $rows = $builder
            ->from(table: 'users')
            ->where(column: static function (QueryBuilder $query): QueryBuilder {
                return $query
                    ->where(column: 'status', operator: '=', value: 'active')
                    ->orWhere(column: 'role', operator: '=', value: 'admin');
            })
            ->get();

        self::assertSame([['id' => 1, 'name' => 'Milos']], $rows);
        self::assertCount(1, $executor->queries);

        $query = $executor->queries[0];
        $sql = self::normalizeSql($query['sql']);

        self::assertStringContainsString('WHERE (`status` = ? OR `role` = ?)', $sql);
        self::assertSame(['active', 'admin'], $query['bindings']);
    }
}

final class RecordingReadHelperExecutor implements ExecutorInterface
{
    /**
     * @var list<array{sql: string, bindings: list<mixed>}>
     */
    public array $queries = [];

    /**
     * @var list<array<string, mixed>>
     */
    private array $rows;

    /**
     * @param  list<array<string, mixed>>  $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    /**
     * @param  list<mixed>  $bindings
     * @return list<array<string, mixed>>
     */
    public function query(
        string $sql,
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): array {
        $this->queries[] = [
            'sql' => $sql,
            'bindings' => array_values($bindings),
        ];

        return $this->rows;
    }

    /**
     * @param  list<mixed>  $bindings
     */
    public function execute(
        string $sql,
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): ExecutionResult {
        return ExecutionResult::success(affectedRows: 1);
    }

    public function getDriverName(): string
    {
        return 'mysql';
    }
}
