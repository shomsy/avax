<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Cache;

use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\CacheReplication;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\PrimaryReplicaPolicy;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\PromotionResult;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\ReplicaCount;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\ReplicaSyncResult;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\ReplicationPolicy;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\ReplicationResult;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\ReplicaWriteResult;
use Avax\Components\Application\Cache\System\Capabilities\Distribution\ReplicateCachedValues\SyncResult;
use Avax\Components\Application\Cache\System\Capabilities\Lifecycle\CachedValues\CachedValueLifecycle;
use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStore;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasFound;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\CacheStoreRecordWasMissing;
use Avax\Components\Application\Cache\System\Capabilities\Storage\StoreCachedValues\StoredCacheRecord;
use Avax\Components\Application\Cache\System\Foundation\Time\Clock;
use Avax\Components\Application\Cache\System\Foundation\Time\FrozenClock;
use Avax\Components\Application\Cache\System\Foundation\Time\SystemClock;
use Avax\Components\Application\Cache\System\Foundation\Time\Timestamp;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * Simple array-based fake cache store for testing.
 */
final class FakeCacheStoreForReplication implements CacheStore
{
    /** @var array<string, StoredCacheRecord> */
    private array $records = [];

    private bool $shouldFail = false;

    private string $failMessage = 'Simulated failure';

    private Clock $clock;

    public function __construct(?Clock $clock = null)
    {
        $this->clock = $clock ?? new SystemClock;
    }

    public function read(CacheKey $key, Clock $clock) : CacheStoreRecordWasFound|CacheStoreRecordWasMissing
    {
        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return new CacheStoreRecordWasMissing(key: $key);
        }

        $record = $this->records[$fullKey];

        if ($record->lifecycle->isExpired(clock: $clock)) {
            unset($this->records[$fullKey]);

            return new CacheStoreRecordWasMissing(key: $key);
        }

        return new CacheStoreRecordWasFound(key: $key, record: $record, clock: $clock);
    }

    public function write(CacheKey $key, StoredCacheRecord $record) : void
    {
        if ($this->shouldFail) {
            throw new RuntimeException($this->failMessage);
        }

        $this->records[$key->fullKey()] = $record;
    }

    public function forget(CacheKey $key) : void
    {
        unset($this->records[$key->fullKey()]);
    }

    public function clear() : void
    {
        $this->records = [];
    }

    public function exists(CacheKey $key) : bool
    {
        $fullKey = $key->fullKey();

        if (! isset($this->records[$fullKey])) {
            return false;
        }

        return ! $this->records[$fullKey]->lifecycle->isExpired(clock: $this->clock);
    }

    public function setShouldFail(bool $fail, string $message = 'Simulated failure') : void
    {
        $this->shouldFail = $fail;
        $this->failMessage = $message;
    }

    public function getRecords() : array
    {
        return $this->records;
    }

    public function count() : int
    {
        return count($this->records);
    }
}

final class CacheReplicationTest extends TestCase
{
    public function test_replicate_writes_to_primary_and_replicas() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->replicate('test-key', 'test-value', 3600);

        $this->assertTrue($result->primarySuccess);
        $this->assertCount(2, $result->replicaResults);
        $this->assertTrue($result->replicaResults[0]->success);
        $this->assertTrue($result->replicaResults[1]->success);
    }

    // --- replicate(key, value, ttl) ---

    private function createReplication(
        bool               $sync = true,
        bool               $readFromReplicaOnMiss = false,
        ?ReplicationPolicy $policy = null,
    ) : CacheReplication
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));

        $primary = new FakeCacheStoreForReplication($clock);
        $replica1 = new FakeCacheStoreForReplication($clock);
        $replica2 = new FakeCacheStoreForReplication($clock);

        $replicationPolicy = $policy ?? ($sync ? ReplicationPolicy::SYNCHRONOUS : ReplicationPolicy::ASYNCHRONOUS);

        $prp = new PrimaryReplicaPolicy(
            readFromReplicaOnMiss: $readFromReplicaOnMiss,
            asyncReplication     : ! $sync,
            syncReplication      : $sync,
            failover             : false,
            replicationPolicy    : $replicationPolicy,
        );

        return CacheReplication::create(
            primary : $primary,
            replicas: [$replica1, $replica2],
            policy  : $prp,
        );
    }

    public function test_replicate_returns_failure_when_primary_write_fails() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary = new FakeCacheStoreForReplication($clock);
        $primary->setShouldFail(true);

        $replica = new FakeCacheStoreForReplication($clock);

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [$replica],
            policy  : PrimaryReplicaPolicy::default(),
        );

        $result = $replication->replicate('fail-key', 'value');

        $this->assertFalse($result->primarySuccess);
        $this->assertCount(0, $result->replicaResults);
    }

    public function test_replicate_handles_replica_failure_gracefully() : void
    {
        $clock      = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary    = new FakeCacheStoreForReplication($clock);
        $goodReplica = new FakeCacheStoreForReplication($clock);
        $badReplica = new FakeCacheStoreForReplication($clock);
        $badReplica->setShouldFail(true, 'Replica connection lost');

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [$goodReplica, $badReplica],
            policy  : PrimaryReplicaPolicy::default(),
        );

        $result = $replication->replicate('partial-key', 'value');

        $this->assertTrue($result->primarySuccess);
        $this->assertCount(2, $result->replicaResults);
        $this->assertTrue($result->replicaResults[0]->success);
        $this->assertFalse($result->replicaResults[1]->success);
        $this->assertSame('Replica connection lost', $result->replicaResults[1]->error);
    }

    public function test_replicate_with_null_ttl() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->replicate('no-ttl-key', 'value', null);

        $this->assertTrue($result->primarySuccess);
    }

    public function test_replicate_key_is_stored_in_result() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->replicate('specific-key', 'value');

        $this->assertSame('specific-key', $result->key);
    }

    // --- read(key) ---

    public function test_read_from_primary_only() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary = new FakeCacheStoreForReplication($clock);

        // Write directly to primary with very long TTL so it won't expire
        // when read back with SystemClock
        $now    = $clock->now();
        $expiresAt = Timestamp::fromUnixTime(PHP_INT_MAX);
        $lifecycle = CachedValueLifecycle::create($now, $expiresAt, $clock);
        $record = new StoredCacheRecord(value: 'primary-value', lifecycle: $lifecycle);

        $primary->write(
            new CacheKey('read-key'),
            $record,
        );

        $replica = new FakeCacheStoreForReplication($clock);
        $policy = new PrimaryReplicaPolicy(
            readFromReplicaOnMiss: false,
            syncReplication      : true,
            replicationPolicy    : ReplicationPolicy::SYNCHRONOUS,
        );

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [$replica],
            policy  : $policy,
        );

        $result = $replication->read('read-key');

        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
    }

    public function test_read_fallback_to_primary_on_miss() : void
    {
        $replication = $this->createReplication(
            sync                 : true,
            readFromReplicaOnMiss: true,
        );

        // Nothing written anywhere
        $result = $replication->read('missing-key');

        $this->assertInstanceOf(CacheStoreRecordWasMissing::class, $result);
    }

    public function test_read_from_replica_on_primary_miss() : void
    {
        $clock   = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary = new FakeCacheStoreForReplication($clock);
        $replica1 = new FakeCacheStoreForReplication($clock);
        $replica2 = new FakeCacheStoreForReplication($clock);

        // Write only to replica with very long TTL
        $now    = $clock->now();
        $expiresAt = Timestamp::fromUnixTime(PHP_INT_MAX);
        $lifecycle = CachedValueLifecycle::create($now, $expiresAt, $clock);
        $record = new StoredCacheRecord(value: 'replica-value', lifecycle: $lifecycle);

        $replica1->write(
            new CacheKey('replica-only-key'),
            $record,
        );

        $policy = new PrimaryReplicaPolicy(
            readFromReplicaOnMiss: true,
            syncReplication      : true,
            replicationPolicy    : ReplicationPolicy::SYNCHRONOUS,
        );

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [$replica1, $replica2],
            policy  : $policy,
        );

        $result = $replication->read('replica-only-key');

        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
    }

    // --- sync(key) ---

    public function test_sync_returns_found_on_primary_false_when_key_not_on_primary() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->sync('nonexistent-key');

        $this->assertFalse($result->foundOnPrimary);
        $this->assertCount(0, $result->replicaSyncResults);
    }

    public function test_sync_returns_found_on_primary_true_when_key_exists() : void
    {
        $replication = $this->createReplication(sync: true);

        // Write to primary first
        $replication->replicate('sync-key', 'sync-value');

        $result = $replication->sync('sync-key');

        $this->assertTrue($result->foundOnPrimary);
        $this->assertCount(2, $result->replicaSyncResults);
    }

    public function test_sync_result_contains_replica_statuses() : void
    {
        $replication = $this->createReplication(sync: true);

        $replication->replicate('sync-status-key', 'value');

        $result = $replication->sync('sync-status-key');

        foreach ($result->replicaSyncResults as $replicaResult) {
            $this->assertTrue($replicaResult->syncSuccess);
        }
    }

    // --- promoteReplica() ---

    public function test_promote_replica_returns_promotion_result() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->promoteReplica(0);

        $this->assertInstanceOf(PromotionResult::class, $result);
        $this->assertTrue($result->success);
        $this->assertSame(0, $result->promotedReplicaIndex);
    }

    public function test_promote_replica_with_specific_index() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->promoteReplica(1);

        $this->assertSame(1, $result->promotedReplicaIndex);
    }

    public function test_promote_replica_with_invalid_index_throws() : void
    {
        $replication = $this->createReplication(sync: true);

        $this->expectException(InvalidArgumentException::class);

        $replication->promoteReplica(5);
    }

    public function test_promote_replica_with_negative_index_throws() : void
    {
        $replication = $this->createReplication(sync: true);

        $this->expectException(InvalidArgumentException::class);

        $replication->promoteReplica(-1);
    }

    public function test_promote_replica_marks_as_promoted() : void
    {
        $replication = $this->createReplication(sync: true);

        $this->assertFalse($replication->isPromoted());

        $replication->promoteReplica(0);

        $this->assertTrue($replication->isPromoted());
    }

    // --- Sync vs async replication mode ---

    public function test_synchronous_replication_writes_to_all_replicas() : void
    {
        $replication = $this->createReplication(sync: true);

        $result = $replication->replicate('sync-key', 'value');

        $this->assertTrue($result->primarySuccess);
        $this->assertCount(2, $result->replicaResults);
        $this->assertTrue($result->replicaResults[0]->success);
        $this->assertTrue($result->replicaResults[1]->success);
    }

    public function test_write_uses_replication_policy() : void
    {
        $replication = $this->createReplication(sync: true);

        $replication->write('write-key', 'written-value');

        // Primary should have the value
        $result = $replication->read('write-key');
        $this->assertInstanceOf(CacheStoreRecordWasFound::class, $result);
    }

    // --- ReplicationResult properties ---

    public function test_replication_result_is_fully_successful() : void
    {
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: true,
            replicaResults: [
                                new ReplicaWriteResult(replicaIndex: 0, key: 'test-key', success: true, error: null),
                                new ReplicaWriteResult(replicaIndex: 1, key: 'test-key', success: true, error: null),
                            ],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertTrue($result->isFullySuccessful());
    }

    public function test_replication_result_is_fully_successful_when_replica_fails() : void
    {
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: true,
            replicaResults: [
                                new ReplicaWriteResult(replicaIndex: 0, key: 'test-key', success: true, error: null),
                                new ReplicaWriteResult(replicaIndex: 1, key: 'test-key', success: false, error: 'Failed'),
                            ],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertFalse($result->isFullySuccessful());
    }

    public function test_replication_result_is_fully_successful_when_primary_fails() : void
    {
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: false,
            replicaResults: [],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertFalse($result->isFullySuccessful());
    }

    public function test_replication_result_meets_quorum() : void
    {
        // 1 primary + 2 replicas = 3 total, quorum = 2
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: true,
            replicaResults: [
                                new ReplicaWriteResult(replicaIndex: 0, key: 'test-key', success: true, error: null),
                                new ReplicaWriteResult(replicaIndex: 1, key: 'test-key', success: false, error: 'Failed'),
                            ],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertTrue($result->meetsQuorum());
    }

    public function test_replication_result_meets_quorum_when_not_met() : void
    {
        // 1 primary + 4 replicas = 5 total, quorum = 3
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: true,
            replicaResults: [
                                new ReplicaWriteResult(replicaIndex: 0, key: 'test-key', success: true, error: null),
                                new ReplicaWriteResult(replicaIndex: 1, key: 'test-key', success: false, error: 'Failed'),
                                new ReplicaWriteResult(replicaIndex: 2, key: 'test-key', success: false, error: 'Failed'),
                                new ReplicaWriteResult(replicaIndex: 3, key: 'test-key', success: false, error: 'Failed'),
                            ],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertFalse($result->meetsQuorum());
    }

    public function test_replication_result_get_successful_replica_count() : void
    {
        $result = new ReplicationResult(
            key           : 'test-key',
            primarySuccess: true,
            replicaResults: [
                                new ReplicaWriteResult(replicaIndex: 0, key: 'test-key', success: true, error: null),
                                new ReplicaWriteResult(replicaIndex: 1, key: 'test-key', success: false, error: 'Failed'),
                                new ReplicaWriteResult(replicaIndex: 2, key: 'test-key', success: true, error: null),
                            ],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertSame(2, $result->getSuccessfulReplicaCount());
    }

    // --- SyncResult properties ---

    public function test_sync_result_all_replicas_in_sync() : void
    {
        $result = new SyncResult(
            key               : 'test-key',
            foundOnPrimary    : true,
            replicaSyncResults: [
                                    new ReplicaSyncResult(replicaIndex: 0, key: 'test-key', wasInSync: true, syncSuccess: true),
                                    new ReplicaSyncResult(replicaIndex: 1, key: 'test-key', wasInSync: true, syncSuccess: true),
                                ],
        );

        $this->assertTrue($result->allReplicasInSync());
    }

    public function test_sync_result_all_replicas_in_sync_when_not_all_in_sync() : void
    {
        $result = new SyncResult(
            key               : 'test-key',
            foundOnPrimary    : true,
            replicaSyncResults: [
                                    new ReplicaSyncResult(replicaIndex: 0, key: 'test-key', wasInSync: true, syncSuccess: true),
                                    new ReplicaSyncResult(replicaIndex: 1, key: 'test-key', wasInSync: false, syncSuccess: true),
                                ],
        );

        $this->assertFalse($result->allReplicasInSync());
    }

    public function test_sync_result_all_replicas_in_sync_with_empty_results() : void
    {
        $result = new SyncResult(
            key               : 'test-key',
            foundOnPrimary    : false,
            replicaSyncResults: [],
        );

        $this->assertTrue($result->allReplicasInSync());
    }

    // --- PromotionResult properties ---

    public function test_promotion_result_properties() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $oldPrimary = new FakeCacheStoreForReplication($clock);
        $newPrimary = new FakeCacheStoreForReplication($clock);

        $result = new PromotionResult(
            oldPrimary          : $oldPrimary,
            newPrimary          : $newPrimary,
            promotedReplicaIndex: 1,
            success             : true,
        );

        $this->assertSame($oldPrimary, $result->oldPrimary);
        $this->assertSame($newPrimary, $result->newPrimary);
        $this->assertSame(1, $result->promotedReplicaIndex);
        $this->assertTrue($result->success);
    }

    // --- PrimaryReplicaPolicy factory methods ---

    public function test_policy_read_heavy() : void
    {
        $policy = PrimaryReplicaPolicy::readHeavy();

        $this->assertTrue($policy->readFromReplicaOnMiss);
        $this->assertTrue($policy->asyncReplication);
        $this->assertFalse($policy->syncReplication);
        $this->assertTrue($policy->failover);
        $this->assertSame(ReplicationPolicy::ASYNCHRONOUS, $policy->replicationPolicy);
    }

    public function test_policy_write_heavy() : void
    {
        $policy = PrimaryReplicaPolicy::writeHeavy();

        $this->assertFalse($policy->readFromReplicaOnMiss);
        $this->assertFalse($policy->asyncReplication);
        $this->assertTrue($policy->syncReplication);
        $this->assertFalse($policy->failover);
        $this->assertSame(ReplicationPolicy::SYNCHRONOUS, $policy->replicationPolicy);
    }

    public function test_policy_high_availability() : void
    {
        $policy = PrimaryReplicaPolicy::highAvailability();

        $this->assertTrue($policy->readFromReplicaOnMiss);
        $this->assertFalse($policy->asyncReplication);
        $this->assertTrue($policy->syncReplication);
        $this->assertTrue($policy->failover);
        $this->assertSame(ReplicationPolicy::QUORUM, $policy->replicationPolicy);
        $this->assertSame(15, $policy->failoverTimeoutSeconds);
        $this->assertSame(5, $policy->maxReplicationRetries);
    }

    public function test_policy_eventual_consistency() : void
    {
        $policy = PrimaryReplicaPolicy::eventualConsistency();

        $this->assertTrue($policy->readFromReplicaOnMiss);
        $this->assertTrue($policy->asyncReplication);
        $this->assertFalse($policy->syncReplication);
        $this->assertFalse($policy->failover);
        $this->assertSame(ReplicationPolicy::ASYNCHRONOUS, $policy->replicationPolicy);
    }

    public function test_policy_strict_consistency() : void
    {
        $policy = PrimaryReplicaPolicy::strictConsistency();

        $this->assertFalse($policy->readFromReplicaOnMiss);
        $this->assertFalse($policy->asyncReplication);
        $this->assertTrue($policy->syncReplication);
        $this->assertFalse($policy->failover);
        $this->assertSame(ReplicationPolicy::SYNCHRONOUS, $policy->replicationPolicy);
    }

    public function test_policy_default() : void
    {
        $policy = PrimaryReplicaPolicy::default();

        $this->assertFalse($policy->readFromReplicaOnMiss);
        $this->assertFalse($policy->asyncReplication);
        $this->assertTrue($policy->syncReplication);
        $this->assertFalse($policy->failover);
        $this->assertSame(ReplicationPolicy::SYNCHRONOUS, $policy->replicationPolicy);
        $this->assertSame(30, $policy->failoverTimeoutSeconds);
        $this->assertSame(3, $policy->maxReplicationRetries);
    }

    public function test_policy_to_array() : void
    {
        $policy = PrimaryReplicaPolicy::highAvailability();

        $array = $policy->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('readFromReplicaOnMiss', $array);
        $this->assertArrayHasKey('asyncReplication', $array);
        $this->assertArrayHasKey('syncReplication', $array);
        $this->assertArrayHasKey('failover', $array);
        $this->assertArrayHasKey('replicationPolicy', $array);
        $this->assertArrayHasKey('failoverTimeoutSeconds', $array);
        $this->assertArrayHasKey('maxReplicationRetries', $array);
        $this->assertSame('quorum', $array['replicationPolicy']);
    }

    // --- ReplicaWriteResult ---

    public function test_replica_write_result_properties() : void
    {
        $result = new ReplicaWriteResult(
            replicaIndex: 2,
            key         : 'my-key',
            success     : false,
            error       : 'Connection timeout',
        );

        $this->assertSame(2, $result->replicaIndex);
        $this->assertSame('my-key', $result->key);
        $this->assertFalse($result->success);
        $this->assertSame('Connection timeout', $result->error);
    }

    public function test_replica_write_result_successful() : void
    {
        $result = new ReplicaWriteResult(
            replicaIndex: 0,
            key         : 'key',
            success     : true,
            error       : null,
        );

        $this->assertTrue($result->success);
        $this->assertNull($result->error);
    }

    // --- ReplicaSyncResult ---

    public function test_replica_sync_result_properties() : void
    {
        $result = new ReplicaSyncResult(
            replicaIndex: 1,
            key         : 'sync-key',
            wasInSync   : false,
            syncSuccess : true,
        );

        $this->assertSame(1, $result->replicaIndex);
        $this->assertSame('sync-key', $result->key);
        $this->assertFalse($result->wasInSync);
        $this->assertTrue($result->syncSuccess);
    }

    // --- ReplicaCount ---

    public function test_replica_count_single() : void
    {
        $count = ReplicaCount::single();

        $this->assertSame(1, $count->primary);
        $this->assertSame(0, $count->secondaries);
        $this->assertSame(1, $count->totalReplicas());
        $this->assertSame(1, $count->quorumSize());
    }

    public function test_replica_count_with_secondaries() : void
    {
        $count = ReplicaCount::withSecondaries(3);

        $this->assertSame(1, $count->primary);
        $this->assertSame(3, $count->secondaries);
        $this->assertSame(4, $count->totalReplicas());
        $this->assertSame(3, $count->quorumSize());
    }

    public function test_replica_count_quorum() : void
    {
        $count = ReplicaCount::quorum();

        $this->assertSame(1, $count->primary);
        $this->assertSame(2, $count->secondaries);
        $this->assertSame(3, $count->totalReplicas());
        $this->assertSame(2, $count->quorumSize());
    }

    // --- Additional CacheReplication methods ---

    public function test_get_primary_returns_primary_store() : void
    {
        $replication = $this->createReplication(sync: true);

        $this->assertInstanceOf(CacheStore::class, $replication->getPrimary());
    }

    public function test_get_replicas_returns_replica_stores() : void
    {
        $replication = $this->createReplication(sync: true);

        $replicas = $replication->getReplicas();

        $this->assertCount(2, $replicas);
        foreach ($replicas as $replica) {
            $this->assertInstanceOf(CacheStore::class, $replica);
        }
    }

    public function test_get_replica_count() : void
    {
        $replication = $this->createReplication(sync: true);

        $this->assertSame(2, $replication->getReplicaCount());
    }

    public function test_get_policy() : void
    {
        $policy = PrimaryReplicaPolicy::readHeavy();
        $clock  = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary = new FakeCacheStoreForReplication($clock);

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [],
            policy  : $policy,
        );

        $this->assertSame($policy, $replication->getPolicy());
    }

    public function test_replicate_with_no_replicas() : void
    {
        $clock = new FrozenClock(Timestamp::fromUnixTime(1000000));
        $primary = new FakeCacheStoreForReplication($clock);

        $replication = CacheReplication::create(
            primary : $primary,
            replicas: [],
            policy  : PrimaryReplicaPolicy::default(),
        );

        $result = $replication->replicate('no-replica-key', 'value');

        $this->assertTrue($result->primarySuccess);
        $this->assertCount(0, $result->replicaResults);
        $this->assertTrue($result->isFullySuccessful());
    }

    public function test_readonly_properties_of_result_classes() : void
    {
        $result = new ReplicationResult(
            key           : 'test',
            primarySuccess: true,
            replicaResults: [],
            policy        : PrimaryReplicaPolicy::default(),
        );

        $this->assertSame('test', $result->key);
        $this->assertTrue($result->primarySuccess);
        $this->assertSame([], $result->replicaResults);
    }
}
