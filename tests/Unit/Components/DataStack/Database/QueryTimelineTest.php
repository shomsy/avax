<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry\QuerySpan;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryTimeline;
use PHPUnit\Framework\TestCase;

final class QueryTimelineTest extends TestCase
{
    public function test_add_span_to_timeline() : void
    {
        $timeline = new QueryTimeline();
        $span     = $this->createSpan('SELECT * FROM users', 0.0, 1.0);

        $result = $timeline->add($span);

        $this->assertSame($timeline, $result);
        $this->assertCount(1, $timeline->all());
        $this->assertSame($span, $timeline->all()[0]);
    }

    // ==================== Recording queries with timestamps, duration, SQL, bindings ====================

    private function createSpan(
        string $query = 'SELECT * FROM users',
        float  $startTime = 0.0,
        float  $endTime = 0.0,
        string $connection = null,
        int    $rows = null,
        string $error = null,
        array  $bindings = [],
    ) : QuerySpan
    {
        return new QuerySpan(
            query     : $query,
            bindings  : $bindings,
            startTime : $startTime,
            endTime   : $endTime,
            connection: $connection,
            rows      : $rows,
            error     : $error,
        );
    }

    public function test_add_multiple_spans() : void
    {
        $timeline = new QueryTimeline();
        $span1    = $this->createSpan('SELECT * FROM users');
        $span2    = $this->createSpan('INSERT INTO logs');
        $span3    = $this->createSpan('UPDATE users SET name = ?');

        $timeline->add($span1)->add($span2)->add($span3);

        $this->assertCount(3, $timeline->all());
    }

    public function test_spans_preserve_order() : void
    {
        $timeline = new QueryTimeline();
        $span1    = $this->createSpan('SELECT 1');
        $span2    = $this->createSpan('SELECT 2');

        $timeline->add($span1)->add($span2);

        $this->assertSame('SELECT 1', $timeline->all()[0]->query);
        $this->assertSame('SELECT 2', $timeline->all()[1]->query);
    }

    public function test_all_returns_array_of_spans() : void
    {
        $timeline = new QueryTimeline();

        $this->assertIsArray($timeline->all());
        $this->assertEmpty($timeline->all());
    }

    // ==================== Duration calculation ====================

    public function test_total_duration_ms() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', 0.0, 0.5)); // 500ms
        $timeline->add($this->createSpan('SELECT 2', 0.0, 0.3)); // 300ms

        $this->assertSame(800.0, $timeline->totalDurationMs());
    }

    public function test_total_duration_empty_timeline() : void
    {
        $timeline = new QueryTimeline();

        $this->assertSame(0.0, $timeline->totalDurationMs());
    }

    public function test_total_duration_single_span() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', 1.0, 2.5));

        $this->assertSame(1500.0, $timeline->totalDurationMs());
    }

    // ==================== QuerySpan readonly properties ====================

    public function test_query_span_query_property() : void
    {
        $span = $this->createSpan('SELECT * FROM users');

        $this->assertSame('SELECT * FROM users', $span->query);
    }

    public function test_query_span_bindings_property() : void
    {
        $span = $this->createSpan('SELECT * FROM users WHERE id = ?', bindings: [1]);

        $this->assertSame([1], $span->bindings);
    }

    public function test_query_span_connection_property() : void
    {
        $span = $this->createSpan('SELECT 1', connection: 'mysql');

        $this->assertSame('mysql', $span->connection);
    }

    public function test_query_span_rows_property() : void
    {
        $span = $this->createSpan('SELECT 1', rows: 42);

        $this->assertSame(42, $span->rows);
    }

    public function test_query_span_error_property() : void
    {
        $span = $this->createSpan('SELECT 1', error: 'Syntax error');

        $this->assertSame('Syntax error', $span->error);
    }

    public function test_query_span_get_duration_ms() : void
    {
        $span = $this->createSpan('SELECT 1', 1.0, 2.5);

        $this->assertSame(1500.0, $span->getDurationMs());
    }

    public function test_query_span_get_duration_ms_zero() : void
    {
        $span = $this->createSpan('SELECT 1', 0.0, 0.0);

        $this->assertSame(0.0, $span->getDurationMs());
    }

    public function test_query_span_get_duration_ms_negative() : void
    {
        $span = $this->createSpan('SELECT 1', 2.0, 1.0);

        $this->assertSame(-1000.0, $span->getDurationMs());
    }

    public function test_query_span_is_slow() : void
    {
        $fastSpan = $this->createSpan('SELECT 1', 0.0, 0.5);
        $slowSpan = $this->createSpan('SELECT 1', 0.0, 2.0);

        $this->assertFalse($fastSpan->isSlow(1000));
        $this->assertTrue($slowSpan->isSlow(1000));
    }

    public function test_query_span_is_slow_custom_threshold() : void
    {
        $span = $this->createSpan('SELECT 1', 0.0, 0.2); // 200ms

        $this->assertTrue($span->isSlow(100));
        $this->assertFalse($span->isSlow(500));
    }

    public function test_query_span_get_fingerprint() : void
    {
        $span = $this->createSpan('SELECT * FROM users WHERE id = ?');

        $fingerprint = $span->getFingerprint();

        $this->assertIsString($fingerprint);
        $this->assertSame(32, strlen($fingerprint)); // md5 length
    }

    public function test_query_span_to_array() : void
    {
        $span = $this->createSpan(
            query     : 'SELECT * FROM users',
            startTime : 0.0,
            endTime   : 1.0,
            connection: 'mysql',
            rows      : 10,
            bindings  : ['test'],
        );

        $arr = $span->toArray();

        $this->assertArrayHasKey('query', $arr);
        $this->assertArrayHasKey('bindings', $arr);
        $this->assertArrayHasKey('duration_ms', $arr);
        $this->assertArrayHasKey('connection', $arr);
        $this->assertArrayHasKey('rows', $arr);
        $this->assertArrayHasKey('error', $arr);
        $this->assertArrayHasKey('fingerprint', $arr);
        $this->assertSame('SELECT * FROM users', $arr['query']);
        $this->assertSame(1000.0, $arr['duration_ms']);
        $this->assertSame('mysql', $arr['connection']);
        $this->assertSame(10, $arr['rows']);
    }

    // ==================== Filtering by query type ====================

    public function test_filter_spans_by_query_type_select() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT * FROM users'));
        $timeline->add($this->createSpan('INSERT INTO users'));
        $timeline->add($this->createSpan('SELECT * FROM orders'));

        $selects = array_values(array_filter(
                                    $timeline->all(),
                                    static fn (QuerySpan $s) => str_starts_with(strtoupper($s->query), 'SELECT'),
                                ));

        $this->assertCount(2, $selects);
    }

    public function test_filter_spans_by_query_type_insert() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT * FROM users'));
        $timeline->add($this->createSpan('INSERT INTO logs'));
        $timeline->add($this->createSpan('INSERT INTO audit'));

        $inserts = array_values(array_filter(
                                    $timeline->all(),
                                    static fn (QuerySpan $s) => str_starts_with(strtoupper($s->query), 'INSERT'),
                                ));

        $this->assertCount(2, $inserts);
    }

    public function test_filter_spans_by_query_type_update() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('UPDATE users SET name = ?'));
        $timeline->add($this->createSpan('DELETE FROM logs'));

        $updates = array_values(array_filter(
                                    $timeline->all(),
                                    static fn (QuerySpan $s) => str_starts_with(strtoupper($s->query), 'UPDATE'),
                                ));

        $this->assertCount(1, $updates);
    }

    public function test_filter_spans_by_query_type_delete() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('DELETE FROM users'));
        $timeline->add($this->createSpan('DELETE FROM logs'));
        $timeline->add($this->createSpan('SELECT 1'));

        $deletes = array_values(array_filter(
                                    $timeline->all(),
                                    static fn (QuerySpan $s) => str_starts_with(strtoupper($s->query), 'DELETE'),
                                ));

        $this->assertCount(2, $deletes);
    }

    // ==================== Filtering by connection ====================

    public function test_filter_spans_by_connection() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', connection: 'mysql'));
        $timeline->add($this->createSpan('SELECT 1', connection: 'pgsql'));
        $timeline->add($this->createSpan('SELECT 1', connection: 'mysql'));

        $mysqlSpans = array_values(array_filter(
                                       $timeline->all(),
                                       static fn (QuerySpan $s) => $s->connection === 'mysql',
                                   ));

        $this->assertCount(2, $mysqlSpans);
    }

    public function test_filter_spans_by_null_connection() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', connection: 'mysql'));
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('SELECT 1', connection: 'pgsql'));

        $nullConn = array_values(array_filter(
                                     $timeline->all(),
                                     static fn (QuerySpan $s) => $s->connection === null,
                                 ));

        $this->assertCount(1, $nullConn);
    }

    // ==================== Filtering errors ====================

    public function test_filter_spans_with_errors() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('SELECT 1', error: 'Timeout'));
        $timeline->add($this->createSpan('SELECT 1', error: 'Deadlock'));
        $timeline->add($this->createSpan('SELECT 1'));

        $errorSpans = array_values(array_filter(
                                       $timeline->all(),
                                       static fn (QuerySpan $s) => $s->error !== null,
                                   ));

        $this->assertCount(2, $errorSpans);
    }

    public function test_filter_spans_without_errors() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('SELECT 1', error: 'Timeout'));
        $timeline->add($this->createSpan('SELECT 1'));

        $okSpans = array_values(array_filter(
                                    $timeline->all(),
                                    static fn (QuerySpan $s) => $s->error === null,
                                ));

        $this->assertCount(2, $okSpans);
    }

    // ==================== Statistics ====================

    public function test_statistics_total_queries() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('SELECT 2'));
        $timeline->add($this->createSpan('SELECT 3'));

        $this->assertCount(3, $timeline->all());
    }

    public function test_statistics_average_duration() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', 0.0, 0.1)); // 100ms
        $timeline->add($this->createSpan('SELECT 2', 0.0, 0.2)); // 200ms
        $timeline->add($this->createSpan('SELECT 3', 0.0, 0.3)); // 300ms

        $total = $timeline->totalDurationMs();
        $avg      = $total / count($timeline->all());

        $this->assertSame(600.0, $total);
        $this->assertSame(200.0, $avg);
    }

    public function test_statistics_max_duration() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1', 0.0, 0.1));
        $timeline->add($this->createSpan('SELECT 2', 0.0, 0.5));
        $timeline->add($this->createSpan('SELECT 3', 0.0, 0.3));

        $durations = array_map(static fn (QuerySpan $s) => $s->getDurationMs(), $timeline->all());
        $max      = max($durations);

        $this->assertSame(500.0, $max);
    }

    public function test_statistics_slowest_query() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('FAST', 0.0, 0.1));
        $timeline->add($this->createSpan('SLOWEST', 0.0, 1.0));
        $timeline->add($this->createSpan('MEDIUM', 0.0, 0.5));

        $slowest         = null;
        $maxDuration = 0.0;
        foreach ($timeline->all() as $span) {
            if ($span->getDurationMs() > $maxDuration) {
                $maxDuration = $span->getDurationMs();
                $slowest = $span;
            }
        }

        $this->assertNotNull($slowest);
        $this->assertSame('SLOWEST', $slowest->query);
    }

    // ==================== Timeline reset ====================

    public function test_reset_clears_all_spans() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('SELECT 1'));
        $timeline->add($this->createSpan('SELECT 2'));

        $timeline->reset();

        $this->assertEmpty($timeline->all());
        $this->assertSame(0.0, $timeline->totalDurationMs());
    }

    public function test_reset_then_add_new_spans() : void
    {
        $timeline = new QueryTimeline();
        $timeline->add($this->createSpan('OLD'));
        $timeline->reset();
        $timeline->add($this->createSpan('NEW'));

        $this->assertCount(1, $timeline->all());
        $this->assertSame('NEW', $timeline->all()[0]->query);
    }
}
