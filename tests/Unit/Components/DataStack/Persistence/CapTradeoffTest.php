<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\DataStack\Persistence;

// CapTradeoff enum is defined in CapTradeoffPolicy.php but PSR-4 can't auto-load it
// since the filename doesn't match. Force-load the file.
require_once __DIR__ . '/../../../../../components/DataStack/Persistence/System/Capabilities/Consistency/CapTradeoffPolicy.php';

use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\CapTradeoff;
use Avax\Components\DataStack\Persistence\System\Capabilities\Consistency\CapTradeoffPolicy;
use PHPUnit\Framework\TestCase;

final class CapTradeoffTest extends TestCase
{
    // ==================== CapTradeoff enum: CP, AP, BALANCED ====================

    public function test_cp_value() : void
    {
        $this->assertSame('CP', CapTradeoff::CP->value);
    }

    public function test_ap_value() : void
    {
        $this->assertSame('AP', CapTradeoff::AP->value);
    }

    public function test_balanced_value() : void
    {
        $this->assertSame('BALANCED', CapTradeoff::BALANCED->value);
    }

    public function test_there_are_three_cases() : void
    {
        $cases = CapTradeoff::cases();

        $this->assertCount(3, $cases);
        $this->assertContains(CapTradeoff::CP, $cases);
        $this->assertContains(CapTradeoff::AP, $cases);
        $this->assertContains(CapTradeoff::BALANCED, $cases);
    }

    // ==================== CapTradeoff descriptions ====================

    public function test_cp_description() : void
    {
        $desc = CapTradeoff::CP->description();

        $this->assertStringContainsString('Consistency', $desc);
        $this->assertStringContainsString('Availability', $desc);
    }

    public function test_ap_description() : void
    {
        $desc = CapTradeoff::AP->description();

        $this->assertStringContainsString('Availability', $desc);
        $this->assertStringContainsString('Consistency', $desc);
    }

    public function test_balanced_description() : void
    {
        $desc = CapTradeoff::BALANCED->description();

        $this->assertStringContainsString('Balanced', $desc);
    }

    // ==================== consistencyLevel() ====================

    public function test_cp_consistency_level() : void
    {
        $this->assertSame(1.0, CapTradeoff::CP->consistencyLevel());
    }

    public function test_ap_consistency_level() : void
    {
        $this->assertSame(0.3, CapTradeoff::AP->consistencyLevel());
    }

    public function test_balanced_consistency_level() : void
    {
        $this->assertSame(0.7, CapTradeoff::BALANCED->consistencyLevel());
    }

    public function test_cp_has_higher_consistency_than_ap() : void
    {
        $this->assertGreaterThan(CapTradeoff::AP->consistencyLevel(), CapTradeoff::CP->consistencyLevel());
    }

    // ==================== availabilityLevel() ====================

    public function test_cp_availability_level() : void
    {
        $this->assertSame(0.5, CapTradeoff::CP->availabilityLevel());
    }

    public function test_ap_availability_level() : void
    {
        $this->assertSame(1.0, CapTradeoff::AP->availabilityLevel());
    }

    public function test_balanced_availability_level() : void
    {
        $this->assertSame(0.8, CapTradeoff::BALANCED->availabilityLevel());
    }

    public function test_ap_has_higher_availability_than_cp() : void
    {
        $this->assertGreaterThan(CapTradeoff::CP->availabilityLevel(), CapTradeoff::AP->availabilityLevel());
    }

    // ==================== shouldWaitForConsistentRead() ====================

    public function test_cp_waits_for_consistent_read() : void
    {
        $this->assertTrue(CapTradeoff::CP->shouldWaitForConsistentRead());
    }

    public function test_ap_does_not_wait() : void
    {
        $this->assertFalse(CapTradeoff::AP->shouldWaitForConsistentRead());
    }

    public function test_balanced_waits_for_consistent_read() : void
    {
        $this->assertTrue(CapTradeoff::BALANCED->shouldWaitForConsistentRead());
    }

    // ==================== shouldRejectInconsistentWrites() ====================

    public function test_cp_rejects_inconsistent_writes() : void
    {
        $this->assertTrue(CapTradeoff::CP->shouldRejectInconsistentWrites());
    }

    public function test_ap_accepts_inconsistent_writes() : void
    {
        $this->assertFalse(CapTradeoff::AP->shouldRejectInconsistentWrites());
    }

    public function test_balanced_rejects_inconsistent_writes() : void
    {
        $this->assertTrue(CapTradeoff::BALANCED->shouldRejectInconsistentWrites());
    }

    // ==================== recommendedConflictStrategy() ====================

    public function test_cp_recommends_last_write_wins() : void
    {
        $strategy = CapTradeoff::CP->recommendedConflictStrategy();

        $this->assertSame('last_write_wins', $strategy->name());
    }

    public function test_ap_recommends_merge() : void
    {
        $strategy = CapTradeoff::AP->recommendedConflictStrategy();

        $this->assertSame('merge', $strategy->name());
    }

    public function test_balanced_recommends_last_write_wins() : void
    {
        $strategy = CapTradeoff::BALANCED->recommendedConflictStrategy();

        $this->assertSame('last_write_wins', $strategy->name());
    }

    // ==================== prioritizesConsistency() ====================

    public function test_cp_prioritizes_consistency() : void
    {
        $this->assertTrue(CapTradeoff::CP->prioritizesConsistency());
    }

    public function test_ap_does_not_prioritize_consistency() : void
    {
        $this->assertFalse(CapTradeoff::AP->prioritizesConsistency());
    }

    public function test_balanced_prioritizes_consistency() : void
    {
        $this->assertTrue(CapTradeoff::BALANCED->prioritizesConsistency());
    }

    // ==================== prioritizesAvailability() ====================

    public function test_cp_does_not_prioritize_availability() : void
    {
        $this->assertFalse(CapTradeoff::CP->prioritizesAvailability());
    }

    public function test_ap_prioritizes_availability() : void
    {
        $this->assertTrue(CapTradeoff::AP->prioritizesAvailability());
    }

    public function test_balanced_prioritizes_availability() : void
    {
        $this->assertTrue(CapTradeoff::BALANCED->prioritizesAvailability());
    }

    // ==================== CapTradeoffPolicy: factory methods ====================

    public function test_strong_consistency_factory() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency();

        $this->assertSame(CapTradeoff::CP, $policy->tradeoff);
        $this->assertSame(0.0, $policy->maxStalenessSeconds);
        $this->assertFalse($policy->allowStaleReads);
        $this->assertSame(5000, $policy->consensusTimeoutMs);
        $this->assertSame(2, $policy->writeQuorum);
        $this->assertSame(2, $policy->readQuorum);
    }

    public function test_strong_consistency_custom_params() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency(
            writeQuorum       : 3,
            readQuorum        : 2,
            consensusTimeoutMs: 3000,
        );

        $this->assertSame(3, $policy->writeQuorum);
        $this->assertSame(2, $policy->readQuorum);
        $this->assertSame(3000, $policy->consensusTimeoutMs);
    }

    public function test_high_availability_factory() : void
    {
        $policy = CapTradeoffPolicy::highAvailability();

        $this->assertSame(CapTradeoff::AP, $policy->tradeoff);
        $this->assertSame(60.0, $policy->maxStalenessSeconds);
        $this->assertTrue($policy->allowStaleReads);
        $this->assertSame(1000, $policy->consensusTimeoutMs);
        $this->assertSame(1, $policy->writeQuorum);
        $this->assertSame(1, $policy->readQuorum);
    }

    public function test_high_availability_custom_params() : void
    {
        $policy = CapTradeoffPolicy::highAvailability(
            maxStalenessSeconds: 30.0,
            allowStaleReads    : false,
        );

        $this->assertSame(30.0, $policy->maxStalenessSeconds);
        $this->assertFalse($policy->allowStaleReads);
    }

    public function test_balanced_factory() : void
    {
        $policy = CapTradeoffPolicy::balanced();

        $this->assertSame(CapTradeoff::BALANCED, $policy->tradeoff);
        $this->assertSame(10.0, $policy->maxStalenessSeconds);
        $this->assertTrue($policy->allowStaleReads);
        $this->assertSame(3000, $policy->consensusTimeoutMs);
        $this->assertSame(2, $policy->writeQuorum);
        $this->assertSame(2, $policy->readQuorum);
    }

    public function test_balanced_custom_params() : void
    {
        $policy = CapTradeoffPolicy::balanced(
            maxStalenessSeconds: 5.0,
            writeQuorum        : 3,
            readQuorum         : 1,
        );

        $this->assertSame(5.0, $policy->maxStalenessSeconds);
        $this->assertSame(3, $policy->writeQuorum);
        $this->assertSame(1, $policy->readQuorum);
    }

    // ==================== Policy properties ====================

    public function test_default_policy() : void
    {
        $policy = new CapTradeoffPolicy();

        $this->assertSame(CapTradeoff::CP, $policy->tradeoff);
        $this->assertSame(30.0, $policy->maxStalenessSeconds);
        $this->assertSame(5000, $policy->consensusTimeoutMs);
        $this->assertFalse($policy->allowStaleReads);
        $this->assertSame(2, $policy->writeQuorum);
        $this->assertSame(2, $policy->readQuorum);
    }

    public function test_custom_policy() : void
    {
        $policy = new CapTradeoffPolicy(
            tradeoff           : CapTradeoff::AP,
            maxStalenessSeconds: 120.0,
            consensusTimeoutMs : 2000,
            allowStaleReads    : true,
            writeQuorum        : 1,
            readQuorum         : 1,
        );

        $this->assertSame(CapTradeoff::AP, $policy->tradeoff);
        $this->assertSame(120.0, $policy->maxStalenessSeconds);
        $this->assertSame(2000, $policy->consensusTimeoutMs);
        $this->assertTrue($policy->allowStaleReads);
        $this->assertSame(1, $policy->writeQuorum);
        $this->assertSame(1, $policy->readQuorum);
    }

    // ==================== requiresQuorum() ====================

    public function test_requires_quorum_when_write_quorum_gt_1() : void
    {
        $policy = new CapTradeoffPolicy(writeQuorum: 2, readQuorum: 1);

        $this->assertTrue($policy->requiresQuorum());
    }

    public function test_requires_quorum_when_read_quorum_gt_1() : void
    {
        $policy = new CapTradeoffPolicy(writeQuorum: 1, readQuorum: 3);

        $this->assertTrue($policy->requiresQuorum());
    }

    public function test_no_quorum_when_both_are_1() : void
    {
        $policy = new CapTradeoffPolicy(writeQuorum: 1, readQuorum: 1);

        $this->assertFalse($policy->requiresQuorum());
    }

    // ==================== summary() ====================

    public function test_policy_summary_contains_key_info() : void
    {
        $policy  = CapTradeoffPolicy::strongConsistency();
        $summary = $policy->summary();

        $this->assertStringContainsString('CAP Policy', $summary);
        $this->assertStringContainsString('CP', $summary);
        $this->assertStringContainsString('Max Staleness', $summary);
        $this->assertStringContainsString('Quorums', $summary);
        $this->assertStringContainsString('Consensus Timeout', $summary);
    }

    public function test_policy_summary_for_ap() : void
    {
        $policy  = CapTradeoffPolicy::highAvailability();
        $summary = $policy->summary();

        $this->assertStringContainsString('AP', $summary);
    }

    public function test_policy_summary_for_balanced() : void
    {
        $policy  = CapTradeoffPolicy::balanced();
        $summary = $policy->summary();

        $this->assertStringContainsString('BALANCED', $summary);
    }

    public function test_policy_summary_shows_stale_reads() : void
    {
        $policyYes = CapTradeoffPolicy::highAvailability();
        $policyNo  = CapTradeoffPolicy::strongConsistency();

        $this->assertStringContainsString('Yes', $policyYes->summary());
        $this->assertStringContainsString('No', $policyNo->summary());
    }

    // ==================== Consistency/availability level accessors ====================

    public function test_strong_consistency_has_max_consistency() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency();

        $this->assertSame(1.0, $policy->tradeoff->consistencyLevel());
    }

    public function test_high_availability_has_max_availability() : void
    {
        $policy = CapTradeoffPolicy::highAvailability();

        $this->assertSame(1.0, $policy->tradeoff->availabilityLevel());
    }

    public function test_balanced_has_moderate_levels() : void
    {
        $policy = CapTradeoffPolicy::balanced();

        $this->assertSame(0.7, $policy->tradeoff->consistencyLevel());
        $this->assertSame(0.8, $policy->tradeoff->availabilityLevel());
    }

    // ==================== Quorum configurations ====================

    public function test_strong_consistency_quorums() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency(writeQuorum: 3, readQuorum: 3);

        $this->assertSame(3, $policy->writeQuorum);
        $this->assertSame(3, $policy->readQuorum);
    }

    public function test_high_availability_minimal_quorums() : void
    {
        $policy = CapTradeoffPolicy::highAvailability();

        $this->assertSame(1, $policy->writeQuorum);
        $this->assertSame(1, $policy->readQuorum);
    }

    // ==================== Readonly value object ====================

    public function test_policy_is_readonly() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency();

        // All properties are public readonly - verify they can be read
        $this->assertInstanceOf(CapTradeoff::class, $policy->tradeoff);
        $this->assertIsFloat($policy->maxStalenessSeconds);
        $this->assertIsInt($policy->consensusTimeoutMs);
        $this->assertIsBool($policy->allowStaleReads);
        $this->assertIsInt($policy->writeQuorum);
        $this->assertIsInt($policy->readQuorum);
    }

    // ==================== Edge cases and additional scenarios ====================

    public function test_consistency_ordering() : void
    {
        $this->assertGreaterThan(CapTradeoff::BALANCED->consistencyLevel(), CapTradeoff::CP->consistencyLevel());
        $this->assertGreaterThan(CapTradeoff::AP->consistencyLevel(), CapTradeoff::BALANCED->consistencyLevel());
    }

    public function test_availability_ordering() : void
    {
        $this->assertGreaterThan(CapTradeoff::CP->availabilityLevel(), CapTradeoff::AP->availabilityLevel());
        $this->assertGreaterThan(CapTradeoff::CP->availabilityLevel(), CapTradeoff::BALANCED->availabilityLevel());
    }

    public function test_tradeoff_from_string() : void
    {
        $cp       = CapTradeoff::from('CP');
        $ap       = CapTradeoff::from('AP');
        $balanced = CapTradeoff::from('BALANCED');

        $this->assertSame(CapTradeoff::CP, $cp);
        $this->assertSame(CapTradeoff::AP, $ap);
        $this->assertSame(CapTradeoff::BALANCED, $balanced);
    }

    public function test_strong_consistency_does_not_allow_stale_reads() : void
    {
        $policy = CapTradeoffPolicy::strongConsistency();

        $this->assertFalse($policy->allowStaleReads);
    }

    public function test_high_availability_short_consensus_timeout() : void
    {
        $policy = CapTradeoffPolicy::highAvailability();

        $this->assertSame(1000, $policy->consensusTimeoutMs);
        $this->assertLessThan(CapTradeoffPolicy::strongConsistency()->consensusTimeoutMs, $policy->consensusTimeoutMs);
    }

    public function test_balanced_moderate_consensus_timeout() : void
    {
        $policy = CapTradeoffPolicy::balanced();

        $this->assertSame(3000, $policy->consensusTimeoutMs);
        $this->assertLessThan(CapTradeoffPolicy::strongConsistency()->consensusTimeoutMs, $policy->consensusTimeoutMs);
        $this->assertGreaterThan(CapTradeoffPolicy::highAvailability()->consensusTimeoutMs, $policy->consensusTimeoutMs);
    }

    // ==================== Summary format validation ====================

    public function test_summary_format_contains_consistency_level() : void
    {
        $policy  = CapTradeoffPolicy::strongConsistency();
        $summary = $policy->summary();

        $this->assertStringContainsString('1.0', $summary);
    }

    public function test_summary_format_contains_availability_level() : void
    {
        $policy  = CapTradeoffPolicy::highAvailability();
        $summary = $policy->summary();

        $this->assertStringContainsString('1.0', $summary);
    }
}
