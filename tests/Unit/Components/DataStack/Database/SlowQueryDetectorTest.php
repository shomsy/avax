<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Database;

use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\OpenTelemetry\QuerySpan;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\QueryTimeline;
use Avax\Components\DataStack\Database\System\Capabilities\Telemetry\SlowQueryDetector;
use PHPUnit\Framework\TestCase;

final class SlowQueryDetectorTest extends TestCase
{
    public function test_record_slow_query_returns_true() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT * FROM users', 0.0, 2.0); // 2000ms

        $result = $detector->record($span);

        $this->assertTrue($result);
    }

    // ==================== Detecting queries above threshold ====================

    private function createSpan(
        string $query = 'SELECT * FROM users',
        float  $startTime = 0.0,
        float  $endTime = 0.0,
    ) : QuerySpan
    {
        return new QuerySpan(
            query    : $query,
            startTime: $startTime,
            endTime  : $endTime,
        );
    }

    public function test_record_fast_query_returns_false() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT * FROM users', 0.0, 0.5); // 500ms

        $result = $detector->record($span);

        $this->assertFalse($result);
    }

    public function test_record_query_at_exact_threshold() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT * FROM users', 0.0, 1.0); // 1000ms - NOT slow (> not >=)

        $result = $detector->record($span);

        $this->assertFalse($result);
    }

    public function test_record_query_just_above_threshold() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT * FROM users', 0.0, 1.001); // 1001ms

        $result = $detector->record($span);

        $this->assertTrue($result);
    }

    public function test_default_threshold_is_1000ms() : void
    {
        $detector = new SlowQueryDetector();
        $span     = $this->createSpan('SELECT 1', 0.0, 1.5); // 1500ms

        $this->assertTrue($detector->record($span));
    }

    public function test_custom_threshold() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 500);
        $span     = $this->createSpan('SELECT 1', 0.0, 0.6); // 600ms

        $this->assertTrue($detector->record($span));
    }

    // ==================== Report generation with slow queries ====================

    public function test_all_returns_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW1', 0.0, 2.0));
        $detector->record($this->createSpan('FAST', 0.0, 0.5));
        $detector->record($this->createSpan('SLOW2', 0.0, 3.0));

        $slowQueries = $detector->all();

        $this->assertCount(2, $slowQueries);
        $this->assertSame('SLOW1', $slowQueries[0]->query);
        $this->assertSame('SLOW2', $slowQueries[1]->query);
    }

    public function test_all_empty_when_no_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('FAST1', 0.0, 0.1));
        $detector->record($this->createSpan('FAST2', 0.0, 0.2));

        $this->assertEmpty($detector->all());
    }

    public function test_all_returns_query_span_objects() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT 1', 0.0, 2.0);
        $detector->record($span);

        $all = $detector->all();

        $this->assertInstanceOf(QuerySpan::class, $all[0]);
        $this->assertSame($span, $all[0]);
    }

    // ==================== Fingerprint grouping ====================

    public function test_slow_queries_have_fingerprints() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SELECT * FROM users WHERE id = ?', 0.0, 2.0));
        $detector->record($this->createSpan('SELECT * FROM orders WHERE id = ?', 0.0, 3.0));

        $all = $detector->all();

        $fp1 = $all[0]->getFingerprint();
        $fp2 = $all[1]->getFingerprint();

        $this->assertIsString($fp1);
        $this->assertIsString($fp2);
    }

    public function test_same_query_pattern_has_same_fingerprint() : void
    {
        $span1 = $this->createSpan('SELECT * FROM users WHERE id = ?', 0.0, 2.0);
        $span2 = $this->createSpan('SELECT * FROM users WHERE id = ?', 0.0, 3.0);

        $this->assertSame($span1->getFingerprint(), $span2->getFingerprint());
    }

    // ==================== Statistics calculation ====================

    public function test_total_slow_query_count() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW1', 0.0, 2.0));
        $detector->record($this->createSpan('SLOW2', 0.0, 3.0));
        $detector->record($this->createSpan('FAST', 0.0, 0.5));

        $this->assertCount(2, $detector->all());
    }

    public function test_total_duration_of_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW1', 0.0, 2.0)); // 2000ms
        $detector->record($this->createSpan('SLOW2', 0.0, 3.0)); // 3000ms

        $total = array_sum(array_map(
                               static fn (QuerySpan $s) => $s->getDurationMs(),
                               $detector->all()
                           ));

        $this->assertSame(5000.0, $total);
    }

    public function test_average_duration_of_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW1', 0.0, 2.0));
        $detector->record($this->createSpan('SLOW2', 0.0, 4.0));

        $all       = $detector->all();
        $durations = array_map(static fn (QuerySpan $s) => $s->getDurationMs(), $all);
        $avg       = array_sum($durations) / count($durations);

        $this->assertSame(3000.0, $avg);
    }

    public function test_max_duration_of_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW1', 0.0, 2.0));
        $detector->record($this->createSpan('SLOWEST', 0.0, 5.0));
        $detector->record($this->createSpan('SLOW2', 0.0, 3.0));

        $all       = $detector->all();
        $durations = array_map(static fn (QuerySpan $s) => $s->getDurationMs(), $all);
        $max       = max($durations);

        $this->assertSame(5000.0, $max);
    }

    // ==================== Timeline integration ====================

    public function test_integration_with_query_timeline() : void
    {
        $timeline = new QueryTimeline();
        $detector = new SlowQueryDetector(thresholdMs: 1000);

        $span1 = $this->createSpan('SELECT 1', 0.0, 0.5);
        $span2 = $this->createSpan('SELECT 2', 0.0, 2.0);
        $span3 = $this->createSpan('SELECT 3', 0.0, 3.0);

        $timeline->add($span1)->add($span2)->add($span3);

        foreach ($timeline->all() as $span) {
            $detector->record($span);
        }

        $this->assertCount(3, $timeline->all());
        $this->assertCount(2, $detector->all());
    }

    public function test_timeline_and_detector_consistency() : void
    {
        $timeline = new QueryTimeline();
        $detector = new SlowQueryDetector(thresholdMs: 1000);

        $spans = [
            $this->createSpan('FAST', 0.0, 0.1),
            $this->createSpan('SLOW', 0.0, 2.0),
            $this->createSpan('FAST2', 0.0, 0.2),
            $this->createSpan('SLOW2', 0.0, 1.5),
        ];

        foreach ($spans as $span) {
            $timeline->add($span);
            $detector->record($span);
        }

        $slowFromTimeline = array_values(array_filter(
                                             $timeline->all(),
                                             static fn (QuerySpan $s) => $s->isSlow(1000)
                                         ));

        $this->assertCount(count($detector->all()), $slowFromTimeline);
    }

    // ==================== Configuration (threshold, max entries) ====================

    public function test_zero_threshold_marks_all_as_slow() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 0);
        $span     = $this->createSpan('SELECT 1', 0.0, 0.001); // 1ms

        $this->assertTrue($detector->record($span));
    }

    public function test_high_threshold_marks_none_as_slow() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 10000);
        $span     = $this->createSpan('SELECT 1', 0.0, 1.0); // 1000ms

        $this->assertFalse($detector->record($span));
    }

    public function test_reset_clears_slow_queries() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $detector->record($this->createSpan('SLOW', 0.0, 2.0));
        $detector->record($this->createSpan('SLOW2', 0.0, 3.0));

        $detector->reset();

        $this->assertEmpty($detector->all());
    }

    public function test_multiple_recordings_accumulate() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);

        for ($i = 0; $i < 5; $i++) {
            $detector->record($this->createSpan("SLOW{$i}", 0.0, 2.0));
        }

        $this->assertCount(5, $detector->all());
    }

    // ==================== Severity classification (via SlowPersistenceReport pattern) ====================

    public function test_query_span_is_slow_with_various_thresholds() : void
    {
        $span = $this->createSpan('SELECT 1', 0.0, 0.5); // 500ms

        $this->assertFalse($span->isSlow(1000));
        $this->assertTrue($span->isSlow(400));
        $this->assertTrue($span->isSlow(100));
    }

    public function test_severity_via_duration_ratio() : void
    {
        // Simulating severity classification based on how much over threshold
        $detector100  = new SlowQueryDetector(thresholdMs: 100);
        $detector1000 = new SlowQueryDetector(thresholdMs: 1000);

        $span = $this->createSpan('SELECT 1', 0.0, 1.0); // 1000ms

        $detector100->record($span); // 10x over -> CRITICAL level
        $detector1000->record($span); // exactly at threshold -> not slow

        $this->assertCount(1, $detector100->all());
        $this->assertCount(0, $detector1000->all());
    }

    public function test_severity_levels_simulation() : void
    {
        // Test severity classification pattern:
        // ratio >= 10.0 => CRITICAL
        // ratio >= 5.0  => SEVERE
        // ratio >= 2.0  => WARNING
        // default        => SLOW

        $threshold = 100.0;

        // SLOW: just over threshold (1.5x)
        $span1 = $this->createSpan('SLOW', 0.0, 0.15); // 150ms
        // WARNING: 2x over
        $span2 = $this->createSpan('WARNING', 0.0, 0.2); // 200ms
        // SEVERE: 5x over
        $span3 = $this->createSpan('SEVERE', 0.0, 0.5); // 500ms
        // CRITICAL: 10x over
        $span4 = $this->createSpan('CRITICAL', 0.0, 1.0); // 1000ms

        $detector = new SlowQueryDetector(thresholdMs: (int) $threshold);

        $this->assertTrue($detector->record($span1));
        $this->assertTrue($detector->record($span2));
        $this->assertTrue($detector->record($span3));
        $this->assertTrue($detector->record($span4));

        $all = $detector->all();
        $this->assertCount(4, $all);

        // Verify duration ratios
        $ratios = array_map(
            static fn (QuerySpan $s) => $s->getDurationMs() / $threshold,
            $all
        );

        $this->assertSame(1.5, $ratios[0]);
        $this->assertSame(2.0, $ratios[1]);
        $this->assertSame(5.0, $ratios[2]);
        $this->assertSame(10.0, $ratios[3]);
    }

    // ==================== Edge cases ====================

    public function test_zero_duration_query() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT 1', 0.0, 0.0);

        $this->assertFalse($detector->record($span));
    }

    public function test_very_slow_query() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = $this->createSpan('SELECT 1', 0.0, 60.0); // 60 seconds

        $this->assertTrue($detector->record($span));
        $this->assertSame(60000.0, $detector->all()[0]->getDurationMs());
    }

    public function test_query_with_bindings() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = new QuerySpan(
            query    : 'SELECT * FROM users WHERE id = ?',
            bindings : [42],
            startTime: 0.0,
            endTime  : 2.0,
        );

        $detector->record($span);

        $this->assertCount(1, $detector->all());
        $this->assertSame([42], $detector->all()[0]->bindings);
    }

    public function test_query_with_connection() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = new QuerySpan(
            query     : 'SELECT 1',
            startTime : 0.0,
            endTime   : 2.0,
            connection: 'primary',
        );

        $detector->record($span);

        $this->assertSame('primary', $detector->all()[0]->connection);
    }

    public function test_query_with_error() : void
    {
        $detector = new SlowQueryDetector(thresholdMs: 1000);
        $span     = new QuerySpan(
            query    : 'SELECT 1',
            startTime: 0.0,
            endTime  : 2.0,
            error    : 'Timeout',
        );

        $detector->record($span);

        $this->assertSame('Timeout', $detector->all()[0]->error);
    }
}
