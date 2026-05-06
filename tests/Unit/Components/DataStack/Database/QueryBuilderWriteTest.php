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

final class QueryBuilderWriteTest extends TestCase
{
    public function test_insert_compiles_sql_and_bindings(): void
    {
        $executor = new RecordingWriteExecutor();

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $result = $builder
            ->from(table: 'users')
            ->insert([
                'name' => 'Milos',
                'email' => 'milos@example.test',
            ]);

        self::assertTrue($result);
        self::assertCount(1, $executor->executions);

        $execution = $executor->executions[0];

        self::assertMatchesRegularExpression('/INSERT INTO .*users/i', self::normalizeSql($execution['sql']));
        self::assertSame(['Milos', 'milos@example.test'], $execution['bindings']);
    }

    private static function normalizeSql(string $sql): string
    {
        return preg_replace('/\s+/', ' ', trim($sql)) ?? $sql;
    }

    public function test_update_compiles_sql_and_bindings(): void
    {
        $executor = new RecordingWriteExecutor();

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $result = $builder
            ->from(table: 'users')
            ->where(column: 'id', operator: '=', value: 123)
            ->update([
                'name' => 'Milos',
            ]);

        self::assertTrue($result);
        self::assertCount(1, $executor->executions);

        $execution = $executor->executions[0];
        $sql = self::normalizeSql($execution['sql']);

        self::assertMatchesRegularExpression('/UPDATE .*users/i', $sql);
        self::assertMatchesRegularExpression('/SET .*name.*= \?/i', $sql);
        self::assertMatchesRegularExpression('/WHERE .*id.*= \?/i', $sql);
        self::assertSame(['Milos', 123], $execution['bindings']);
    }

    public function test_delete_compiles_sql_and_bindings(): void
    {
        $executor = new RecordingWriteExecutor();

        $builder = new QueryBuilder(
            grammar     : new MySQLGrammar(),
            orchestrator: new QueryOrchestrator(executor: $executor),
        );

        $result = $builder
            ->from(table: 'users')
            ->where(column: 'id', operator: '=', value: 123)
            ->delete();

        self::assertTrue($result);
        self::assertCount(1, $executor->executions);

        $execution = $executor->executions[0];
        $sql = self::normalizeSql($execution['sql']);

        self::assertMatchesRegularExpression('/DELETE FROM .*users/i', $sql);
        self::assertMatchesRegularExpression('/WHERE .*id.*= \?/i', $sql);
        self::assertSame([123], $execution['bindings']);
    }
}

final class RecordingWriteExecutor implements ExecutorInterface
{
    /**
     * @var list<array{sql: string, bindings: list<mixed>}>
     */
    public array $executions = [];

    /**
     * @return list<array<string, mixed>>
     */
    public function query(
        string $sql,
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): array {
        return [];
    }

    /**
     * @param  list<mixed>  $bindings
     */
    public function execute(
        string $sql,
        array $bindings = [],
        ?ExecutionScope $executionScope = null,
    ): ExecutionResult {
        $this->executions[] = [
            'sql' => $sql,
            'bindings' => array_values($bindings),
        ];

        return ExecutionResult::success(affectedRows: 1);
    }

    public function getDriverName(): string
    {
        return 'mysql';
    }
}
