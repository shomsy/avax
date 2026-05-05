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

final class QueryBuilderSelectTest extends TestCase
{
    public function test_select_where_order_and_limit_are_compiled_with_bindings() : void
    {
        $executor = new RecordingQueryExecutor(rows: [
                                                         [
                                                             'id'   => 123,
                                                             'name' => 'Milos',
                                                         ],
                                                     ]);

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $rows = $builder
            ->from(table: 'users')
            ->select('id', 'name')
            ->where(column: 'id', operator: '=', value: 123)
            ->orderBy(column: 'name')
            ->limit(limit: 1)
            ->get();

        self::assertSame(
            expected: [
                          [
                              'id'   => 123,
                              'name' => 'Milos',
                          ],
                      ],
            actual  : $rows,
        );

        self::assertCount(expectedCount: 1, haystack: $executor->queries);

        $recordedQuery = $executor->queries[0];
        $sql           = self::normalizeSql($recordedQuery['sql']);

        self::assertSame(expected: [123], actual: $recordedQuery['bindings']);

        self::assertStringStartsWith(prefix: 'SELECT ', string: $sql);
        self::assertMatchesRegularExpression(pattern: '/SELECT .*id.*name/i', string: $sql);
        self::assertMatchesRegularExpression(pattern: '/FROM .*users/i', string: $sql);
        self::assertMatchesRegularExpression(pattern: '/WHERE .*id.*= \?/i', string: $sql);
        self::assertMatchesRegularExpression(pattern: '/ORDER BY .*name.* ASC/i', string: $sql);
        self::assertMatchesRegularExpression(pattern: '/LIMIT 1/i', string: $sql);
    }

    private static function normalizeSql(string $sql) : string
    {
        return preg_replace(pattern: '/\s+/', replacement: ' ', subject: trim($sql)) ?? $sql;
    }
}

final class RecordingQueryExecutor implements ExecutorInterface
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
     * @param list<array<string, mixed>> $rows
     */
    public function __construct(array $rows = [])
    {
        $this->rows = $rows;
    }

    /**
     * @param list<mixed> $bindings
     *
     * @return list<array<string, mixed>>
     */
    public function query(
        string          $sql,
        array           $bindings = [],
        ?ExecutionScope $executionScope = null,
    ) : array
    {
        $this->queries[] = [
            'sql'      => $sql,
            'bindings' => array_values($bindings),
        ];

        return $this->rows;
    }

    /**
     * @param list<mixed> $bindings
     */
    public function execute(
        string          $sql,
        array           $bindings = [],
        ?ExecutionScope $executionScope = null,
    ) : ExecutionResult
    {
        return ExecutionResult::success(affectedRows: 1);
    }

    public function getDriverName() : string
    {
        return 'mysql';
    }
}
