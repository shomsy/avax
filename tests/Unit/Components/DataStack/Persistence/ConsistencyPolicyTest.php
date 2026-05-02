<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

// VectorClock and ConsistencyPolicy are defined in ConsistencyPolicy.php but PSR-4 can't
// auto-load VectorClock since the filename doesn't match. Force-load the file.
require_once __DIR__ . '/../../../../../components/DataStack/Persistence/System/Capabilities/Consistency/ConsistencyPolicy.php';

use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\Conflict;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictPair;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolution;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\ConflictResolutionResult;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\EventualConsistency;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\VectorClock;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\VersionedValue;
use PHPUnit\Framework\TestCase;
use RuntimeException;

final class ConsistencyPolicyTest extends TestCase
{
    // ==================== VectorClock: increment ====================

    public function test_increment_initial_empty_clock(): void
    {
        $clock       = VectorClock::empty();
        $incremented = $clock->increment('nodeA');

        $this->assertSame(['nodeA' => 1], $incremented->toArray());
    }

    public function test_increment_existing_node(): void
    {
        $clock       = VectorClock::initial('nodeA');
        $incremented = $clock->increment('nodeA');

        $this->assertSame(['nodeA' => 2], $incremented->toArray());
    }

    public function test_increment_adds_new_node(): void
    {
        $clock       = VectorClock::initial('nodeA');
        $incremented = $clock->increment('nodeB');

        $this->assertSame(['nodeA' => 1, 'nodeB' => 1], $incremented->toArray());
    }

    public function test_increment_is_immutable(): void
    {
        $clock       = VectorClock::initial('nodeA');
        $incremented = $clock->increment('nodeA');

        $this->assertSame(['nodeA' => 1], $clock->toArray());
        $this->assertSame(['nodeA' => 2], $incremented->toArray());
    }

    public function test_multiple_increments(): void
    {
        $clock = VectorClock::empty();
        $clock = $clock->increment('nodeA');
        $clock = $clock->increment('nodeB');
        $clock = $clock->increment('nodeA');

        $this->assertSame(['nodeA' => 2, 'nodeB' => 1], $clock->toArray());
    }

    // ==================== VectorClock: merge ====================

    public function test_merge_takes_maximum(): void
    {
        $clockA = new VectorClock(['nodeA' => 3, 'nodeB' => 1]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 5]);

        $merged = $clockA->merge($clockB);

        $this->assertSame(['nodeA' => 3, 'nodeB' => 5], $merged->toArray());
    }

    public function test_merge_with_new_nodes(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeB' => 2]);

        $merged = $clockA->merge($clockB);

        $this->assertSame(['nodeA' => 1, 'nodeB' => 2], $merged->toArray());
    }

    public function test_merge_is_immutable(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeB' => 2]);

        $merged = $clockA->merge($clockB);

        $this->assertSame(['nodeA' => 1], $clockA->toArray());
        $this->assertSame(['nodeB' => 2], $clockB->toArray());
    }

    public function test_merge_empty_clocks(): void
    {
        $clockA = VectorClock::empty();
        $clockB = VectorClock::empty();

        $merged = $clockA->merge($clockB);

        $this->assertSame([], $merged->toArray());
    }

    // ==================== VectorClock: happened-before ====================

    public function test_happened_before_true(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeA' => 2]);

        $this->assertTrue($clockA->happenedBefore($clockB));
    }

    public function test_happened_before_false_equal_clocks(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeA' => 1]);

        $this->assertFalse($clockA->happenedBefore($clockB));
    }

    public function test_happened_before_false_concurrent(): void
    {
        $clockA = new VectorClock(['nodeA' => 2, 'nodeB' => 1]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);

        $this->assertFalse($clockA->happenedBefore($clockB));
    }

    public function test_happened_before_false_greater(): void
    {
        $clockA = new VectorClock(['nodeA' => 5]);
        $clockB = new VectorClock(['nodeA' => 1]);

        $this->assertFalse($clockA->happenedBefore($clockB));
    }

    public function test_happened_before_multiple_nodes(): void
    {
        $clockA = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);
        $clockB = new VectorClock(['nodeA' => 2, 'nodeB' => 3]);

        $this->assertTrue($clockA->happenedBefore($clockB));
    }

    public function test_happened_before_with_missing_nodes(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 1]);

        $this->assertTrue($clockA->happenedBefore($clockB));
    }

    // ==================== VectorClock: concurrent detection ====================

    public function test_is_concurrent_true(): void
    {
        $clockA = new VectorClock(['nodeA' => 2, 'nodeB' => 1]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);

        $this->assertTrue($clockA->isConcurrent($clockB));
    }

    public function test_is_concurrent_false_causally_related(): void
    {
        $clockA = new VectorClock(['nodeA' => 1]);
        $clockB = new VectorClock(['nodeA' => 2]);

        $this->assertFalse($clockA->isConcurrent($clockB));
    }

    public function test_is_concurrent_false_equal_clocks(): void
    {
        $clockA = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);

        $this->assertFalse($clockA->isConcurrent($clockB));
    }

    // ==================== VectorClock: other methods ====================

    public function test_vector_clock_empty_factory(): void
    {
        $clock = VectorClock::empty();

        $this->assertSame([], $clock->toArray());
    }

    public function test_vector_clock_initial_factory(): void
    {
        $clock = VectorClock::initial('nodeA');

        $this->assertSame(['nodeA' => 1], $clock->toArray());
    }

    public function test_vector_clock_happened_after(): void
    {
        $clockA = new VectorClock(['nodeA' => 2]);
        $clockB = new VectorClock(['nodeA' => 1]);

        $this->assertTrue($clockA->happenedAfter($clockB));
    }

    public function test_vector_clock_equals(): void
    {
        $clockA = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);
        $clockB = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);
        $clockC = new VectorClock(['nodeA' => 1, 'nodeB' => 3]);

        $this->assertTrue($clockA->equals($clockB));
        $this->assertFalse($clockA->equals($clockC));
    }

    public function test_vector_clock_to_string(): void
    {
        $clock = new VectorClock(['nodeA' => 1, 'nodeB' => 2]);

        $str = (string) $clock;

        $this->assertStringContainsString('nodeA:1', $str);
        $this->assertStringContainsString('nodeB:2', $str);
    }

    // ==================== EventualConsistency: mergeReplicas ====================

    public function test_merge_replicas_single_value(): void
    {
        $policy = new EventualConsistency();
        $value  = VersionedValue::create('data', 'nodeA');

        $merged = $policy->mergeReplicas([$value]);

        $this->assertSame('data', $merged->value);
    }

    public function test_merge_replicas_causally_ordered(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = $v1->update('v2');

        $merged = $policy->mergeReplicas([$v1, $v2]);

        $this->assertSame('v2', $merged->value);
    }

    public function test_merge_replicas_concurrent_conflict(): void
    {
        $policy = new EventualConsistency();
        $base   = VersionedValue::create('base', 'nodeA');
        $v1     = $base->update('from_nodeA');
        $v2     = VersionedValue::create('from_nodeB', 'nodeB');

        $merged = $policy->mergeReplicas([$v1, $v2]);

        $this->assertNotNull($merged->value);
    }

    public function test_merge_replicas_empty_throws(): void
    {
        $policy = new EventualConsistency();

        $this->expectException(RuntimeException::class);

        $policy->mergeReplicas([]);
    }

    // ==================== EventualConsistency: conflict detection ====================

    public function test_detect_conflict_concurrent_writes(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('value_a', 'nodeA');
        $v2     = VersionedValue::create('value_b', 'nodeB');

        $this->assertTrue($policy->detectConflict($v1, $v2));
    }

    public function test_detect_no_conflict_causal(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = $v1->update('v2');

        $this->assertFalse($policy->detectConflict($v1, $v2));
    }

    public function test_detect_conflict_non_versioned_values(): void
    {
        $policy = new EventualConsistency();

        $this->assertFalse($policy->detectConflict('value_a', 'value_b'));
    }

    // ==================== ConflictResolution: lastWriteWins ====================

    public function test_last_write_wins_selects_newer(): void
    {
        $strategy = ConflictResolution::lastWriteWins();

        $result = $strategy->resolve(
            valueA : 'old_value',
            valueB : 'new_value',
            context: ['timestampA' => 100.0, 'timestampB' => 200.0],
        );

        $this->assertSame('new_value', $result);
    }

    public function test_last_write_wins_equal_timestamps(): void
    {
        $strategy = ConflictResolution::lastWriteWins();

        $result = $strategy->resolve(
            valueA : 'value_a',
            valueB : 'value_b',
            context: ['timestampA' => 100.0, 'timestampB' => 100.0],
        );

        $this->assertSame('value_b', $result);
    }

    public function test_last_write_wins_missing_timestamps(): void
    {
        $strategy = ConflictResolution::lastWriteWins();

        $result = $strategy->resolve('value_a', 'value_b', []);

        $this->assertSame('value_b', $result);
    }

    // ==================== ConflictResolution: firstWriteWins ====================

    public function test_first_write_wins_selects_older(): void
    {
        $strategy = ConflictResolution::firstWriteWins();

        $result = $strategy->resolve(
            valueA : 'first_value',
            valueB : 'second_value',
            context: ['timestampA' => 100.0, 'timestampB' => 200.0],
        );

        $this->assertSame('first_value', $result);
    }

    public function test_first_write_wins_equal_timestamps(): void
    {
        $strategy = ConflictResolution::firstWriteWins();

        $result = $strategy->resolve(
            valueA : 'value_a',
            valueB : 'value_b',
            context: ['timestampA' => 100.0, 'timestampB' => 100.0],
        );

        $this->assertSame('value_a', $result);
    }

    // ==================== ConflictResolution: highestValueWins ====================

    public function test_highest_value_wins_numeric(): void
    {
        $strategy = ConflictResolution::highestValueWins();

        $result = $strategy->resolve(
            valueA : 10,
            valueB : 20,
            context: [],
        );

        $this->assertSame(20, $result);
    }

    public function test_highest_value_wins_float(): void
    {
        $strategy = ConflictResolution::highestValueWins();

        $result = $strategy->resolve(
            valueA : 1.5,
            valueB : 2.7,
            context: [],
        );

        $this->assertSame(2.7, $result);
    }

    public function test_highest_value_wins_non_numeric_fallback(): void
    {
        $strategy = ConflictResolution::highestValueWins();

        $result = $strategy->resolve(
            valueA : 'a',
            valueB : 'b',
            context: ['timestampA' => 100.0, 'timestampB' => 200.0],
        );

        $this->assertSame('b', $result);
    }

    // ==================== ConflictResolution: merge ====================

    public function test_merge_arrays(): void
    {
        $strategy = ConflictResolution::merge();

        $result = $strategy->resolve(
            valueA : ['a', 'b'],
            valueB : ['c', 'd'],
            context: [],
        );

        $this->assertSame(['a', 'b', 'c', 'd'], $result);
    }

    public function test_merge_strings(): void
    {
        $strategy = ConflictResolution::merge();

        $result = $strategy->resolve(
            valueA : 'hello ',
            valueB : 'world',
            context: [],
        );

        $this->assertSame('hello world', $result);
    }

    public function test_merge_non_mergeable_fallback(): void
    {
        $strategy = ConflictResolution::merge();

        $result = $strategy->resolve(
            valueA : 10,
            valueB : 20,
            context: ['timestampA' => 100.0, 'timestampB' => 200.0],
        );

        $this->assertSame(20, $result);
    }

    // ==================== Custom conflict resolver ====================

    public function test_custom_resolver(): void
    {
        $resolver = static fn (mixed $a, mixed $b) => "merged:{$a}+{$b}";
        $strategy = ConflictResolution::custom($resolver);

        $result = $strategy->resolve('hello', 'world', []);

        $this->assertSame('merged:hello+world', $result);
    }

    public function test_custom_resolver_with_context(): void
    {
        $resolver = static fn (mixed $a, mixed $b, array $ctx) => $ctx['preferred'] ?? $a;
        $strategy = ConflictResolution::custom($resolver);

        $result = $strategy->resolve('a', 'b', ['preferred' => 'b']);

        $this->assertSame('b', $result);
    }

    // ==================== Conflict history tracking ====================

    public function test_conflict_history_records_conflicts(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = VersionedValue::create('v2', 'nodeB');

        $policy->resolveConflict($v1, $v2, ['key' => 'test_key']);

        $this->assertSame(1, $policy->conflictCount());
        $this->assertCount(1, $policy->getConflicts());
    }

    public function test_conflict_history_clear(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = VersionedValue::create('v2', 'nodeB');

        $policy->resolveConflict($v1, $v2, ['key' => 'test_key']);
        $policy->clearConflicts();

        $this->assertSame(0, $policy->conflictCount());
        $this->assertEmpty($policy->getConflicts());
    }

    public function test_conflict_history_max_retained(): void
    {
        $policy = new EventualConsistency(maxConflictHistory: 2);

        for ($i = 0; $i < 5; $i++) {
            $v1 = VersionedValue::create("v1_{$i}", "nodeA_{$i}");
            $v2 = VersionedValue::create("v2_{$i}", "nodeB_{$i}");
            $policy->resolveConflict($v1, $v2, ['key' => "key_{$i}"]);
        }

        $this->assertSame(2, $policy->conflictCount());
    }

    // ==================== VersionedValue properties ====================

    public function test_versioned_value_create(): void
    {
        $value = VersionedValue::create('data', 'nodeA');

        $this->assertSame('data', $value->value);
        $this->assertSame('nodeA', $value->nodeId);
        $this->assertGreaterThan(0, $value->timestamp);
        $this->assertSame(['nodeA' => 1], $value->clock->toArray());
    }

    public function test_versioned_value_update(): void
    {
        $original = VersionedValue::create('v1', 'nodeA');
        $updated  = $original->update('v2');

        $this->assertSame('v2', $updated->value);
        $this->assertSame('nodeA', $updated->nodeId);
        $this->assertSame(['nodeA' => 2], $updated->clock->toArray());
    }

    public function test_versioned_value_update_preserves_other_nodes(): void
    {
        $original = new VersionedValue(
            value    : 'v1',
            clock    : new VectorClock(['nodeA' => 2, 'nodeB' => 3]),
            nodeId   : 'nodeA',
            timestamp: microtime(true),
        );
        $updated = $original->update('v2');

        $this->assertSame(['nodeA' => 3, 'nodeB' => 3], $updated->clock->toArray());
    }

    public function test_versioned_value_with_custom_clock(): void
    {
        $clock = new VectorClock(['nodeA' => 5, 'nodeB' => 3]);
        $value = VersionedValue::create('data', 'nodeA', $clock);

        $this->assertSame(['nodeA' => 5, 'nodeB' => 3], $value->clock->toArray());
    }

    public function test_versioned_value_with_custom_timestamp(): void
    {
        $value = VersionedValue::create('data', 'nodeA', timestamp: 12345.0);

        $this->assertSame(12345.0, $value->timestamp);
    }

    // ==================== EventualConsistency interface methods ====================

    public function test_eventual_consistency_name(): void
    {
        $policy = new EventualConsistency();

        $this->assertSame('eventual', $policy->name());
    }

    public function test_eventual_consistency_can_read(): void
    {
        $policy = new EventualConsistency();

        $this->assertTrue($policy->canRead('current'));
        $this->assertTrue($policy->canRead('current', 'pending'));
    }

    public function test_eventual_consistency_can_write(): void
    {
        $policy = new EventualConsistency();

        $this->assertTrue($policy->canWrite('current', 'new'));
    }

    public function test_eventual_consistency_description(): void
    {
        $policy = new EventualConsistency();
        $desc   = $policy->description();

        $this->assertStringContainsString('eventual', strtolower($desc));
        $this->assertStringContainsString('vector clock', strtolower($desc));
    }

    public function test_eventual_consistency_consistency_level(): void
    {
        $policy = new EventualConsistency();

        $this->assertSame(0.3, $policy->consistencyLevel());
    }

    public function test_eventual_consistency_resolution_strategy_name(): void
    {
        $policy = new EventualConsistency();

        $this->assertSame('last_write_wins', $policy->resolutionStrategyName());
    }

    public function test_eventual_consistency_with_custom_strategy(): void
    {
        $policy = new EventualConsistency(ConflictResolution::firstWriteWins());

        $this->assertSame('first_write_wins', $policy->resolutionStrategyName());
    }

    // ==================== EventualConsistency resolveConflict with non-versioned values ====================

    public function test_resolve_conflict_non_versioned_returns_first(): void
    {
        $policy = new EventualConsistency();

        $result = $policy->resolveConflict('valueA', 'valueB');

        $this->assertSame('valueA', $result);
    }

    // ==================== EventualConsistency resolveConflict with causal ordering ====================

    public function test_resolve_conflict_causal_ordering(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = $v1->update('v2');

        $result = $policy->resolveConflict($v1, $v2);

        $this->assertInstanceOf(VersionedValue::class, $result);
        $this->assertSame('v2', $result->value);
    }

    public function test_resolve_conflict_causal_reverse_order(): void
    {
        $policy = new EventualConsistency();
        $v1     = VersionedValue::create('v1', 'nodeA');
        $v2     = $v1->update('v2');

        $result = $policy->resolveConflict($v2, $v1);

        $this->assertInstanceOf(VersionedValue::class, $result);
        $this->assertSame('v2', $result->value);
    }

    // ==================== ConflictPair ====================

    public function test_conflict_pair_choose_a(): void
    {
        $pair = new ConflictPair(valueA: 'a', valueB: 'b');

        $this->assertSame('a', $pair->chooseA());
    }

    public function test_conflict_pair_choose_b(): void
    {
        $pair = new ConflictPair(valueA: 'a', valueB: 'b');

        $this->assertSame('b', $pair->chooseB());
    }

    public function test_conflict_pair_age(): void
    {
        $pair = new ConflictPair(valueA: 'a', valueB: 'b', createdAt: microtime(true) - 1.0);

        $age = $pair->age();

        $this->assertGreaterThanOrEqual(0.9, $age);
    }

    public function test_conflict_pair_summary(): void
    {
        $pair = new ConflictPair(valueA: 'a', valueB: 'b');

        $summary = $pair->summary();

        $this->assertStringContainsString('Conflict', $summary);
        $this->assertStringContainsString('a', $summary);
        $this->assertStringContainsString('b', $summary);
    }

    // ==================== Conflict factory methods ====================

    public function test_conflict_from_values(): void
    {
        $v1 = VersionedValue::create('a', 'nodeA');
        $v2 = VersionedValue::create('b', 'nodeB');

        $conflict = Conflict::fromValues($v1, $v2, 'test_key');

        $this->assertSame($v1, $conflict->valueA);
        $this->assertSame($v2, $conflict->valueB);
        $this->assertSame('test_key', $conflict->key);
        $this->assertGreaterThan(0, $conflict->detectedAt);
    }

    // ==================== ConflictResolutionResult ====================

    public function test_conflict_resolution_result_no_conflict(): void
    {
        $result = ConflictResolutionResult::noConflict('value');

        $this->assertSame('value', $result->resolvedValue);
        $this->assertSame('no_conflict', $result->strategy);
        $this->assertFalse($result->wasConflict);
    }

    public function test_conflict_resolution_result_resolved(): void
    {
        $result = ConflictResolutionResult::resolved('merged', 'custom', ['detail' => 'info']);

        $this->assertSame('merged', $result->resolvedValue);
        $this->assertSame('custom', $result->strategy);
        $this->assertTrue($result->wasConflict);
        $this->assertSame(['detail' => 'info'], $result->details);
    }

    // ==================== ConflictResolution strategy names ====================

    public function test_strategy_names(): void
    {
        $this->assertSame('last_write_wins', ConflictResolution::lastWriteWins()->name());
        $this->assertSame('first_write_wins', ConflictResolution::firstWriteWins()->name());
        $this->assertSame('highest_value_wins', ConflictResolution::highestValueWins()->name());
        $this->assertSame('lowest_value_wins', ConflictResolution::lowestValueWins()->name());
        $this->assertSame('merge', ConflictResolution::merge()->name());
        $this->assertSame('custom', ConflictResolution::custom(static fn () => null)->name());
        $this->assertSame('manual_intervention', ConflictResolution::manualIntervention()->name());
        $this->assertSame('node_priority', ConflictResolution::nodePriority(['a'])->name());
    }

    public function test_strategy_descriptions(): void
    {
        $this->assertStringContainsString('most recent', ConflictResolution::lastWriteWins()->description());
        $this->assertStringContainsString('earliest', ConflictResolution::firstWriteWins()->description());
        $this->assertStringContainsString('merge', ConflictResolution::merge()->description());
        $this->assertStringContainsString('highest', ConflictResolution::highestValueWins()->description());
        $this->assertStringContainsString('lowest', ConflictResolution::lowestValueWins()->description());
    }

    // ==================== lowestValueWins ====================

    public function test_lowest_value_wins_numeric(): void
    {
        $strategy = ConflictResolution::lowestValueWins();

        $result = $strategy->resolve(
            valueA : 10,
            valueB : 5,
            context: [],
        );

        $this->assertSame(5, $result);
    }

    // ==================== nodePriority ====================

    public function test_node_priority_selects_higher_priority(): void
    {
        $strategy = ConflictResolution::nodePriority(['nodeA', 'nodeB']);

        $result = $strategy->resolve(
            valueA : 'from_a',
            valueB : 'from_b',
            context: ['nodeIdA' => 'nodeA', 'nodeIdB' => 'nodeB'],
        );

        $this->assertSame('from_a', $result);
    }

    public function test_node_priority_fallback_to_last_write(): void
    {
        $strategy = ConflictResolution::nodePriority(['nodeA']);

        $result = $strategy->resolve(
            valueA : 'from_x',
            valueB : 'from_y',
            context: ['nodeIdA' => 'nodeX', 'nodeIdB' => 'nodeY', 'timestampA' => 100.0, 'timestampB' => 200.0],
        );

        $this->assertSame('from_y', $result);
    }

    // ==================== manualIntervention ====================

    public function test_manual_intervention_returns_conflict_pair(): void
    {
        $strategy = ConflictResolution::manualIntervention();

        $result = $strategy->resolve('a', 'b', []);

        $this->assertInstanceOf(ConflictPair::class, $result);
    }
}
