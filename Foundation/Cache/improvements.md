# AvaxCache Improvements Roadmap

## Status

Current state: **V1 Complete** - Core runtime cache + compiled cache + named stores + public facade are
production-ready.

Target state: **V1.5** - Production-ready source protection, stale behavior, refresh policies.

## Masterpiece Rule

> No false features. No half-baked distributed. No stampede lock without failure model. No stale-while-revalidate
> without clear refresh model. No README claims without contract tests.

---

## Phase V1.5: Source Protection + Stale Behavior

### Why First

Stampede protection and stale-while-revalidate give the **biggest production value** with the least infrastructure
complexity. They protect the source (database/API) under load and provide the best user experience (fewer loading
spinners).

### Goals

1. Only **one request** refreshes a given cache key. All others wait or get stale.
2. Stale values can be served while a refresh is in progress.
3. Refresh can happen proactively (refresh-ahead) or reactively (refresh-on-read).
4. Lock-based and cooperative models both supported.
5. Full test coverage for all edge cases.

### Implementation Plan

#### 1. Stampede Protection

```
System/Capabilities/ProtectCacheSource/
├── CacheLockStore.php          # Interface
├── InMemoryLockStore.php       # Implementation
├── FileLockStore.php           # Implementation (optional)
├── AcquireCacheLock.php        # Flow
├── ReleaseCacheLock.php        # Flow
├── CacheLock.php               # Value object
├── CacheLockOwner.php          # Value object
├── CacheLockTimeout.php        # Value object
├── CacheLockWasNotAcquired.php # Exception
├── JitterCacheTtl.php          # Utility
└── how-this-works.md
```

**Behavior:**

- When cache miss occurs, try to acquire lock
- If lock acquired: call loader, store result, release lock
- If lock not acquired: wait for lock OR return stale value OR return miss
- Lock TTL prevents deadlocks

**Key Test:**

```php
test_only_one_loader_runs_when_many_requests_miss_same_key
test_lock_is_released_when_loader_fails
test_lock_timeout_prevents_deadlock
test_waiting_requests_get_stale_value_when_enabled
```

#### 2. Stale-While-Revalidate

```
System/Capabilities/ManageCacheLifecycle/StaleValuePolicies/
├── StaleValuePolicy.php       # Enum
├── DecideStaleValueCanBeServed.php
└── how-this-works.md
```

**Policies:**

- `DO_NOT_SERVE_STALE` - Never serve stale, always refresh on miss
- `SERVE_STALE_WHILE_REFRESHING` - Serve stale while refresh is in progress
- `SERVE_STALE_FOREVER` - Serve stale indefinitely (dangerous, needs explicit opt-in)

**Key Test:**

```php
test_stale_value_is_served_when_refresh_is_in_progress
test_stale_value_is_returned_on_source_failure
test_stale_is_never_served_when_policy_forbids_it
```

#### 3. Refresh Policies

```
System/Capabilities/ManageCacheLifecycle/RefreshPolicies/
├── RefreshPolicy.php           # Interface/Enum
├── RefreshOnRead.php            # Policy
├── RefreshAhead.php            # Policy
├── RefreshWhenStale.php         # Policy
└── how-this-works.md
```

**Policies:**

- `ON_READ` - Refresh happens when value is accessed (standard)
- `AHEAD` - Refresh happens proactively before expiry
- `WHEN_STALE` - Refresh happens only when value becomes stale

**Key Test:**

```php
test_refresh_ahead_fires_before_ttl_expires
test_refresh_when_stale_only_fires_after_expiry
```

#### 4. Flow Integration

```
System/Flows/RememberCachedValue/
└── RememberCachedValue.php     # Integrate stampede + stale + refresh
```

`AvaxCache::remember()` should use these policies.

### Acceptance Criteria

- [ ] Stampede lock prevents multiple loaders for same key
- [ ] Stale values can be served based on policy
- [ ] Refresh-ahead triggers before TTL expires
- [ ] Lock timeout prevents deadlocks
- [ ] All edge cases have contract tests
- [ ] No payload logging in metrics

---

## Phase V2: Capacity + Replacement Policies

### Why Second

Capacity control and replacement policies are essential for production but don't require external infrastructure. They
prevent memory unbounded growth.

### Goals

1. Cache stores have configurable capacity (max entries or max bytes)
2. Replacement policies decide which entry to evict
3. FIFO and LRU implemented, LFU planned for later
4. Expired entries evicted before active ones

### Implementation Plan

```
System/Capabilities/StoreCachedValues/
├── InMemoryCacheStore.php     # Add capacity + eviction
├── FileCacheStore.php         # Add capacity
└── CacheStore.php             # Add capacity interface

System/Capabilities/ManageCacheLifecycle/ReplacementPolicies/
├── ReplacementPolicy.php       # Interface
├── NoReplacement.php          # Throw on capacity exceeded
├── FirstInFirstOutReplacement.php
├── LeastRecentlyUsedReplacement.php
└── how-this-works.md
```

**Key Test:**

```php
test_in_memory_store_evicts_fifo_when_capacity_is_reached
test_in_memory_store_evicts_lru_when_capacity_is_reached
test_reading_value_updates_lru_order
test_expired_values_are_removed_before_active_values
test_no_replacement_policy_fails_when_capacity_is_exceeded
```

### Acceptance Criteria

- [ ] InMemoryCacheStore respects max entries
- [ ] FIFO ordering correct
- [ ] LRU ordering correct (read updates position)
- [ ] Expired entries evicted first
- [ ] Capacity exceeded triggers configured policy

---

## Phase V3: Real Distributed Cache Foundation

### Why Third

Distributed cache requires stable node stores and consistent routing. The current skeleton is incomplete because node
stores are not persistent.

### Goals

1. Each cache node has a stable store (Redis, Memcached, etc.)
2. Consistent hash ring routes keys to correct nodes
3. Node health detection excludes unhealthy nodes
4. Read/write routing uses the same resolver

### Implementation Plan

```
System/Capabilities/DistributeCachedValues/
├── CacheNodeStoreResolver.php
├── CacheNodeStoreMap.php
├── CacheNodeStoreWasMissing.php
├── DistributedCacheStore.php
├── RouteCacheRead.php
├── RouteCacheWrite.php
├── ConsistentHashRing.php
├── DetectUnhealthyCacheNode.php
└── how-this-works.md
```

**Critical Rule:**

```php
// CORRECT: Each node has a stable persistent store
$nodeA = new CacheNode('a', new RedisStore($redisA));
$nodeB = new CacheNode('b', new RedisStore($redisB));

// WRONG: Creating new store each time (current skeleton)
$nodeStore = new InMemoryCacheStore(); // This is wrong!
```

**Key Test:**

```php
test_same_key_routes_to_same_node_store
test_write_then_read_uses_same_resolved_node_store
test_distributed_store_fails_when_node_store_is_missing
test_unhealthy_node_is_not_selected_for_new_reads
test_node_addition_moves_only_expected_key_range
```

### Acceptance Criteria

- [ ] Nodes have stable persistent stores
- [ ] Consistent hash ring routes correctly
- [ ] Node health affects routing
- [ ] All edge cases have contract tests
- [ ] Marked as **experimental** until promoted

---

## Phase V4: Observability

### Why Fourth

Observability makes the difference between a black box and a production-ready system. Metrics and tracing help diagnose
issues before they become incidents.

### Goals

1. All operations emit metrics
2. Latency is tracked
3. Lock wait time is tracked
4. Stale value serving is tracked
5. No payload logging (security)

### Implementation Plan

```
System/Capabilities/ObserveCache/
├── CacheMetrics.php           # Metrics interface
├── CacheTrace.php             # Trace interface
├── RecordCacheHit.php
├── RecordCacheMiss.php
├── RecordCacheWrite.php
├── RecordCacheDelete.php
├── RecordCacheEviction.php
├── RecordCacheStaleServed.php
├── RecordCacheLockWait.php
└── how-this-works.md
```

**Metrics:**

```
cache.hit
cache.miss
cache.write
cache.delete
cache.eviction
cache.stale_served
cache.refresh
cache.lock_wait
cache.source_failure
cache.store_failure
cache.latency_microseconds
```

### Acceptance Criteria

- [ ] All operations emit metrics
- [ ] Latency tracked accurately
- [ ] Lock wait time measurable
- [ ] No payload values in logs/metrics
- [ ] Metrics interface is injectable for custom backends

---

## Implementation Priority

| Phase | Feature                | Priority     | Production Value         |
|-------|------------------------|--------------|--------------------------|
| V1.5  | Stampede Protection    | **Critical** | Prevents thundering herd |
| V1.5  | Stale-While-Revalidate | **Critical** | Better UX under load     |
| V1.5  | Refresh-Ahead          | High         | Proactive freshness      |
| V2    | Replacement Policies   | High         | Memory safety            |
| V2    | Capacity Control       | High         | Predictable memory       |
| V3    | Distributed Cache      | Medium       | Horizontal scaling       |
| V4    | Observability          | Medium       | Debugging in prod        |

---

## Anti-Patterns to Avoid

1. **No "clever" LFU** - LFU without frequency decay is a lie. Frequency changes over time.
2. **No payload logging** - Never log cache values. Log keys, latencies, outcomes.
3. **No experimental in production** - Mark distributed as experimental until promoted.
4. **No half-baked locks** - Lock must have timeout, owner tracking, and deadlock prevention.
5. **No stale without refresh** - Stale values without a refresh path are just stale.

---

## Testing Standards

Every feature requires:

1. **Contract tests** - Store implementations must pass `CacheStoreContractTest`
2. **Integration tests** - Flow interactions tested end-to-end
3. **Edge case tests** - Timeouts, failures, race conditions
4. **No tests that pass by coincidence** - Each test must verify specific behavior

---

## Documentation Standards

Every subsystem requires:

1. **what-this-works.md** - Explains ownership, triggers, flow, debug guidance
2. **README updates** - Feature moves from "Planned" to "Implemented" or "Experimental"
3. **No false claims** - Don't document features that don't exist

---

## Current V1 Feature Status

| Feature                | Status              |
|------------------------|---------------------|
| Runtime Cache Facade   | ✅ Implemented       |
| Compiled Cache         | ✅ Implemented       |
| Named Stores           | ✅ Implemented       |
| InMemory Store         | ✅ Implemented       |
| File Store             | ✅ Implemented       |
| Redis Store            | ✅ Implemented       |
| Contract Tests         | ✅ Implemented       |
| Clock Dependency       | ✅ Implemented       |
| Stampede Protection    | ❌ Planned (V1.5)    |
| Stale-While-Revalidate | ❌ Planned (V1.5)    |
| Refresh-Ahead          | ❌ Planned (V1.5)    |
| Replacement Policies   | ❌ Planned (V2)      |
| Capacity Control       | ❌ Planned (V2)      |
| Distributed Cache      | ❌ Experimental (V3) |
| Observability          | ❌ Planned (V4)      |

---

## Notes for AI Agent

When implementing each phase:

1. **Start with the contract/test** - Define what success looks like before writing code
2. **Follow existing patterns** - Match the style of existing AvaxCache code
3. **No new public concepts** - Extend existing capabilities, don't invent new ones
4. **Clock dependency** - All time operations must use injected Clock
5. **No direct time()** - The entire System/ folder is covered by `NoTimeFunctionInSystemTest`
6. **Run tests after each change** - `./vendor/bin/phpunit`
7. **Update AGENTS.md and README.md** - Move features from "Planned" to "Implemented"

This roadmap is the source of truth. Changes to scope must be discussed and documented.