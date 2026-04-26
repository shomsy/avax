OBAVEZAN GOVERNANCE REFRACTORING: System kao system-design komponenta sa jasnim lifecycle, distribution i consistency
modelom - drzi se striktno pravila iz AI Prompts folera. I da, zelim da vidim mermaid-ove u dokumentaciji, svakom
dokumentu.

Da, sada si pogodio bolji pravac. **`InvalidationMethods`, `InvalidationStrategies`, `ReplacementPolicies` treba
grupisati pod jedan veći lifecycle capability**, jer cache nije samo “čuvanje vrednosti”. System je sistem koji upravlja
životom vrednosti: nastanak, čitanje, starenje, invalidacija, osvežavanje, izbacivanje, distribucija i oporavak.

To je bolja navigacija. Manje šuma. Jače “znam gde da tražim”. I uklapa se u tvoje pravilo da hijerarhija ima smisla
samo kad smanjuje mentalni napor, a ne kad glumi arhitekturu. Tvoj governance baš traži da struktura bude čitljiva kroz
flow, slice, unit i funkcije, i da dublji nivoi povećavaju preciznost, ne konfuziju.

Moj finalni stav: **pravimo System kao system-design grade komponentu, ali ne kao mini Redis.** Avax System treba da
bude pametan framework cache layer koji zna da radi sa lokalnim, fajl, Redis, multi-tier i distribuiranim store-ovima,
ali ne treba da izmišlja sopstveni distributed database.

## 1. Glavna ideja

```text
System = small public API + strong internal system for value lifecycle, storage, distribution, consistency, safety, and observability.
```

Drugim rečima:

```text
Developer API
-> System Flow
-> System Lifecycle Capability
-> Store / Tier / Distributed Routing
-> Observability / Failure Handling
-> Final cache effect
```

Glavna osa sistema:

```text
Cached Value Lifecycle
```

Sekundarne ose:

```text
Storage
Distribution
Consistency
Observability
Configuration
```

Ovo je bolja osa nego “driver”, “decorator”, “strategy”. Ti pojmovi mogu postojati interno kao patterni, ali ne smeju
voditi folder strukturu.

## 2. Repo shape

Pošto želiš komponentu bez `src/`, produkcioni system root je `System/`.

```text
AvaxCache/
├── System/
├── tests/
├── docs/
├── examples/
├── tooling/
├── composer.json
├── README.md
└── AGENTS.md
```

`System/` je system root. `tests/` i `docs/` su repo-level prateći sistemi.

## 3. Finalni production tree

Ovo je moj idealni, world-class, ali i dalje čitljiv oblik.

```text
System/
├── System.php
├── CacheStore.php
├── CacheFailure.php
├── CacheResult.php
├── CachedValue.php
├── MissingCachedValue.php
│
├── Flows/
│   ├── Operations/              # Read, Store, Forget, Clear
│   │   ├── ReadCachedValue/
│   │   ├── StoreCachedValue/
│   │   ├── ForgetCachedValue/
│   │   └── ClearCache/
│   │
│   ├── Lifecycle/              # Remember, Invalidate, Refresh, Evict, Warm
│   │   ├── RememberCachedValue/
│   │   ├── InvalidateCachedValue/
│   │   ├── RefreshCachedValue/
│   │   ├── EvictCachedValue/
│   │   └── WarmCache/
│   │
│   ├── Protection/            # Protect, Sync, Recover
│   │   ├── ProtectCacheSource/
│   │   ├── SyncCachedValue/
│   │   └── RecoverCache/
│   │
│   └── Compiled/              # Compile, Read, Clear, Warm
│       ├── CompileCache/
│       ├── ReadCompiledCache/
│       ├── ClearCompiledCache/
│       └── WarmCompiledCache/
│
├── Capabilities/
│   ├── Lifecycle/                    # Kako vrednost živi, stari, umire
│   │   ├── CachedValues/
│   │   ├── ExpireCachedValues/
│   │   ├── InvalidateCachedValues/
│   │   ├── RefreshCachedValues/
│   │   └── ReplaceCachedValues/
│   │
│   ├── Storage/                      # Gde se vrednost fizički čuva
│   │   ├── StoreCachedValues/
│   │   ├── SizeCachedValues/
│   │   └── ProtectCachedValues/
│   │
│   ├── Distribution/                 # Horizontalno skaliranje
│   │   ├── DistributeCachedValues/
│   │   ├── ReplicateCachedValues/
│   │   └── UseCacheTiers/
│   │
│   ├── Source/                       # Source sync i zaštita
│   │   ├── ProtectCacheSource/
│   │   ├── SyncWithSource/
│   │   └── ControlConsistency/
│   │
│   ├── Observability/                # Metrics i tracing
│   │   ├── ObserveCache/
│   │   └── IdentifyCachedValues/
│   │
│   └── CompiledCache/                # Framework artifact subsystem
│       └── ManageCompiledCache/
│
├── Configuration/
│   └── how-this-works.md
│
├── Foundation/
│   ├── Time/
│   ├── Serialization/
│   ├── Compression/
│   ├── Randomness/
│   └── how-this-works.md
│
└── how-this-works.md
```

## 3.1 Lifecycle group detail

```text
Lifecycle/
├── CachedValues/
│   ├── CacheLifecyclePolicy.php
│   ├── CachedValueState.php
│   ├── CachedValueLifecycle.php
│   └── DecideCachedValueState.php
│
├── ExpireCachedValues/
│   ├── CacheTtl.php
│   ├── CacheExpiration.php
│   ├── NeverExpires.php
│   ├── ExpiresAt.php
│   ├── ExpiresAfter.php
│   ├── SlidingExpiration.php
│   ├── ImmediateExpiration.php
│   ├── CheckCachedValueIsExpired.php
│   └── StaleValuePolicy.php
│
├── InvalidateCachedValues/
│   ├── InvalidateByKey.php
│   ├── InvalidateByKeys.php
│   ├── InvalidateByTag.php
│   ├── InvalidateByTags.php
│   ├── InvalidateByNamespace.php
│   ├── InvalidateByPattern.php
│   ├── InvalidateByVersion.php
│   ├── SoftInvalidateCachedValue.php
│   ├── HardInvalidateCachedValue.php
│   ├── InvalidationReason.php
│   └── InvalidationStrategy.php
│
├── RefreshCachedValues/
│   ├── RefreshPolicy.php
│   └── ShouldRefreshCachedValue.php
│
└── ReplaceCachedValues/
    ├── ChooseCachedValueForReplacement.php
    ├── LeastRecentlyUsedReplacement.php
    ├── LeastFrequentlyUsedReplacement.php
    ├── FirstInFirstOutReplacement.php
    ├── NoReplacement.php
    └── TrackCachedValueAccess.php
```

## 3.2 Storage group detail

```text
Storage/
├── StoreCachedValues/
│   ├── StoredCacheRecord.php
│   ├── CacheStoreRecordWasFound.php
│   ├── CacheStoreRecordWasMissing.php
│   ├── InMemoryCacheStore.php
│   ├── FileCacheStore.php
│   ├── RedisCacheStore.php
│   ├── NullCacheStore.php
│   ├── ChainCacheStore.php
│   └── FallbackCacheStore.php
│
├── SizeCachedValues/
│   ├── CacheCapacity.php
│   ├── CacheEntryCount.php
│   ├── CacheValueSize.php
│   └── CacheCapacityWasExceeded.php
│
└── ProtectCachedValues/
    ├── EncryptedCache.php
    ├── EncryptCachedValue.php
    └── DecryptCachedValue.php
```

## 3.3 Distribution group detail

```text
Distribution/
├── DistributeCachedValues/
│   ├── CacheCluster.php
│   ├── CacheNode.php
│   ├── CacheNodeId.php
│   ├── ConsistentHashRing.php
│   ├── RebalanceCachePartitions.php
│   ├── DetectUnhealthyCacheNode.php
│   └── DistributedCacheStore.php
│
├── ReplicateCachedValues/
│   ├── ReplicationPolicy.php
│   ├── PrimaryReplica.php
│   ├── SecondaryReplica.php
│   └── ChooseReplicaForRead.php
│
└── UseCacheTiers/
    ├── CacheTier.php
    ├── CacheTierName.php
    ├── TieredCache.php
    ├── L1MemoryCache.php
    └── L2DistributedCache.php
```

## 3.4 Source group detail

```text
Source/
├── ProtectCacheSource/
│   ├── CacheLock.php
│   ├── CacheLockStore.php
│   ├── InMemoryLockStore.php
│   ├── AcquireCacheStampedeLock.php
│   └── JitterCacheTtl.php
│
├── SyncWithSource/
│   ├── CacheSource.php
│   ├── LoadValueFromSource.php
│   ├── WriteValueToSource.php
│   ├── DeleteValueFromSource.php
│   └── SourceSyncPolicy.php
│
└── ControlConsistency/
    ├── DecideCacheReadConsistency.php
    └── CacheConsistencyLevel.php
```

## 3.5 Observability group detail

```text
Observability/
├── ObserveCache/
│   ├── CacheMetrics.php
│   ├── CacheTrace.php
│   ├── CacheOperation.php
│   └── MetricsBackends/
│       ├── MetricsBackend.php
│       ├── PrometheusBackend.php
│       ├── StatsDBackend.php
│       └── MetricsSink.php
│
└── IdentifyCachedValues/
    ├── CacheKey.php
    ├── CacheKeyPrefix.php
    ├── CacheNamespace.php
    ├── CacheTag.php
    └── CacheTags.php
```

## 4. Zašto je ovaj oblik bolji

Ovo nije samo “veći tree”. Ovo ima jasnu mapu za mozak:

```text
Flows
-> šta cache radi

Capabilities
-> koje sposobnosti koristi da to izvede

ManageCacheLifecycle
-> kako vrednost živi, stari, nestaje, osvežava se i izbacuje

StoreCachedValues
-> gde se stvarno čuva

UseCacheTiers
-> kako se koristi L1/L2 cache

DistributeCachedValues
-> kako key ide na node/partition

ReplicateCachedValues
-> kako se vrednost duplira radi availability-ja

ControlConsistency
-> koliko sveža vrednost mora biti

ProtectCacheSource
-> kako se štiti baza/API od stampede-a

ObserveCache
-> kako se meri ponašanje u produkciji
```

To je tačno ono što hoćeš: **lako se snalaziš jer folder govori problem, ne pattern**.

## 5. `ManageCacheLifecycle` kao veliki capability

Ovo je prava odluka.

```text
ManageCacheLifecycle/
├── ExpirationMethods/
├── InvalidationMethods/
├── InvalidationStrategies/
├── ReplacementPolicies/
├── RefreshPolicies/
└── StaleValuePolicies/
```

Ovim pokrivaš:

```text
ExpirationMethods
-> TTL, absolute expiration, sliding expiration, immediate expiration

InvalidationMethods
-> by key, by tag, by namespace, by pattern, by version, soft, hard

InvalidationStrategies
-> TTL-only, manual, event-driven, write-through, cache-aside, versioned keys

ReplacementPolicies
-> LRU, LFU, FIFO, random, size-aware, time-aware, no replacement

RefreshPolicies
-> refresh on read, refresh ahead, refresh after write, refresh when stale

StaleValuePolicies
-> never serve stale, serve stale while refreshing, serve stale when source fails
```

Ovo je čistije nego da sve to bude rasuto po `ExpireValues`, `InvalidateValues`, `EvictValues`, `ServeStaleValues`.
Prethodni oblik je bio dobar. Ovaj je pregledniji za “system design mental model”.

## 6. Flow sloj koji ima smisla

Ne bih pravio flow za svaku mikro-operaciju. Flow treba da postoji kad postoji realna priča.

Glavni flow-ovi:

```text
ReadCachedValue
StoreCachedValue
ForgetCachedValue
ClearCache
RememberCachedValue
InvalidateCachedValue
RefreshCachedValue
EvictCachedValue
WarmCache
ProtectCacheSource
SyncCachedValue
RecoverCache
```

To pokriva realne operativne priče.

Primer:

```text
RememberCachedValue
1. pokušaj da pročitaš iz cache-a
2. ako postoji validna vrednost, vrati je
3. ako je missing/stale/expired, zaštiti source od stampede-a
4. učitaj iz source-a
5. upiši u cache
6. vrati vrednost
7. zabeleži hit/miss/latency
```

To je pravi flow. Ne “service”. Ne “manager”. Ne “strategy”. Flow.

## 7. Distributed i scaling moći

Ovo je deo koji pravi razliku između običnog cache wrapper-a i ozbiljnog system-design cache-a.

### Multi-tier cache

```text
UseCacheTiers/
```

Omogućava:

```text
L1 in-memory cache
L2 Redis/distributed cache
fallback store
promotion from L2 to L1
write-through to all tiers
tier-aware invalidation
```

Tipičan flow:

```text
read key
-> check L1
-> if miss, check L2
-> if found in L2, promote to L1
-> if miss, load source
-> store in L1 and L2
```

Ovo je ozbiljan performance feature.

### Distributed partitioning

```text
DistributeCachedValues/
```

Omogućava:

```text
consistent hashing
node routing
partition keys
cache nodes
cluster awareness
rebalance
node health detection
```

Oprez: ovo ne znači da pišemo Redis Cluster. Znači da Avax System može da zna kako da rutira i modeluje distributed
store ako ga koristi.

### Replication

```text
ReplicateCachedValues/
```

Omogućava:

```text
primary replica
secondary replicas
replica count
read from replica
write to replicas
repair divergence
```

Ovo je V2/V3 nivo. Korisno za dizajn, ali ne mora sve odmah u implementaciju.

### Consistency

```text
ControlConsistency/
```

Ovo je vrlo bitno. Distributed cache bez consistency modela je magla.

Pokriva:

```text
eventual consistency
strong local consistency
read-your-writes consistency
consistency window
read policy
write policy
```

Ne treba obećati “strong consistency” globalno ako je store to ne garantuje. Avax treba da bude iskren: lokalno može
jako, distribuirano često eventual.

### Stampede protection

```text
ProtectCacheSource/
```

Ovo je production-grade must-have.

Pokriva:

```text
cache locks
request coalescing
lock timeout
jittered TTL
serve stale while refreshing
fallback when source is overloaded
```

Bez ovoga cache pod opterećenjem može sam da sruši bazu.

### Observability

```text
ObserveCache/
```

Minimum metrika:

```text
cache.hit
cache.miss
cache.write
cache.delete
cache.invalidation
cache.eviction
cache.stale_served
cache.refresh
cache.lock_wait
cache.source_failure
cache.store_failure
cache.latency
cache.size
cache.capacity
```

Ovo mora biti first-class. Ne dodatak na kraju.

## 8. Šta je V1, šta je V2, šta je V3

Da se ne udavimo u sopstvenoj ambiciji. Tvoj clean-code governance posebno upozorava protiv spekulativnih frameworka,
vague naming-a, god class struktura i “smart” koda koji skriva ponašanje.

### V1, core koji mora biti perfektan

```text
System.php
CacheStore.php
CacheResult.php
CachedValue.php
MissingCachedValue.php

Flows/
ReadCachedValue
StoreCachedValue
ForgetCachedValue
ClearCache
RememberCachedValue

Capabilities/
IdentifyCachedValues
ManageCacheLifecycle/ExpirationMethods
StoreCachedValues/InMemoryCacheStore
StoreCachedValues/FileCacheStore
StoreCachedValues/RedisCacheStore
ObserveCache/basic metrics

Foundation/
Time
Serialization
```

V1 cilj:

```text
get / set / remember / delete / clear
TTL
stored null is not confused with cache miss
InMemory, File, Redis
basic metrics
contract tests
safe serialization
deterministic Clock
```

### V1.5, production hardening

```text
UseCacheTiers
ProtectCacheSource
ManageCacheLifecycle/StaleValuePolicies
ManageCacheLifecycle/RefreshPolicies
ManageCacheLifecycle/InvalidationMethods
```

V1.5 cilj:

```text
L1 + L2 cache
stampede protection
stale-while-revalidate
manual invalidation
tag invalidation
namespace invalidation
refresh-on-read
refresh-ahead
```

### V2, system design strength

```text
ManageCacheLifecycle/InvalidationStrategies
ManageCacheLifecycle/ReplacementPolicies
SizeCachedValues
SyncWithSource
RecoverCache
```

V2 cilj:

```text
LRU
FIFO
LFU optional
size-aware eviction
write-through
write-around
source failure recovery
cache rebuild
cache warming
```

### V3, distributed/cache cluster intelligence

```text
DistributeCachedValues
ReplicateCachedValues
ControlConsistency
DistributedCacheStore
RecoverCache cluster-aware flows
```

V3 cilj:

```text
consistent hashing
node routing
partitioning
replication policy
failover
read replica choice
rebalance
consistency levels
```

## 9. Public API, mali i stabilan

Ne bih javno izložio sve ovo. Javni API mora ostati kratak.

```php
interface System
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;

    public function remember(string $key, null|int|\DateInterval $ttl, callable $loader): mixed;

    public function delete(string $key): bool;

    public function clear(): bool;

    public function has(string $key): bool;
}
```

Ali interno ne smeš koristiti samo `mixed|null`, jer moraš razlikovati:

```text
missing key
stored null
expired value
stale value
source failure
store failure
```

Zato interno treba:

```php
interface CacheStore
{
    public function read(CacheKey $key): CacheStoreRecordWasFound|CacheStoreRecordWasMissing;

    public function write(CacheKey $key, StoredCacheRecord $record): void;

    public function forget(CacheKey $key): void;

    public function clear(): void;
}
```

I eventualno napredni API:

```php
interface TaggedCache
{
    public function tags(string ...$tags): System;
}

interface AtomicCache
{
    public function increment(string $key, int $by = 1): int;

    public function decrement(string $key, int $by = 1): int;

    public function add(string $key, mixed $value, null|int|\DateInterval $ttl = null): bool;
}
```

Ali `AtomicCache` samo ako store stvarno može da garantuje atomic behavior. Ne sme se lažno obećavati atomicity.

## 10. Važne system-design odluke

### System miss nije isto što i stored null

Ovo je non-negotiable.

```text
cache.get('x') === null
```

ne sme interno značiti “missing”. Možda je korisnik stvarno cache-irao `null`.

### Expired nije isto što i stale

```text
expired = nevažeće po vremenu
stale = staro, ali možda dozvoljeno za serviranje
missing = ne postoji
invalidated = namerno proglašeno nevažećim
evicted = izbačeno zbog kapaciteta
```

Ovo mora biti modelovano.

### Write-behind nije V1

Write-behind traži:

```text
durable queue
retry policy
dead-letter queue
worker
crash recovery
flush command
observability
idempotency
```

Bez toga, write-behind je opasan. To bih ostavio za posebnu komponentu koja sarađuje sa Queue/Saga/Outbox sistemom.

### Distributed cache ne sme obećati nemoguće

Ako koristimo Redis Cluster, Memcached pool ili neku mrežnu infrastrukturu, Avax može da modeluje routing, failure i
consistency policy. Ali ne treba da obećava global strong consistency ako underlying store to ne daje.

## 11. Test plan

Tvoj testing standard traži da testovi budu behavior specifications, da štite ponašanje spolja i da pokrivaju happy,
failure, edge, regression i security slučajeve kada ima smisla.

Struktura:

```text
tests/
├── Unit/
│   └── System/
│       ├── Flows/
│       ├── Capabilities/
│       ├── Configuration/
│       └── Foundation/
│
├── Contract/
│   └── System/
│       ├── CacheStoreContractTest.php
│       ├── InMemoryCacheStoreContractTest.php
│       ├── FileCacheStoreContractTest.php
│       ├── RedisCacheStoreContractTest.php
│       └── TieredCacheStoreContractTest.php
│
├── Integration/
│   └── System/
│       ├── FileCacheStoreTest.php
│       ├── RedisCacheStoreTest.php
│       ├── TieredCacheTest.php
│       ├── StampedeProtectionTest.php
│       └── DistributedCacheRoutingTest.php
│
└── Support/
    └── System/
        ├── FakeClock.php
        ├── FakeCacheSource.php
        ├── FakeCacheStore.php
        ├── FailingCacheStore.php
        └── CacheStoreContract.php
```

Najvažniji contract testovi:

```text
test_it_returns_missing_when_key_does_not_exist
test_it_returns_stored_value_when_key_exists
test_it_returns_stored_null_when_null_was_stored
test_it_expires_value_when_ttl_has_passed
test_it_keeps_value_when_ttl_has_not_passed
test_it_forgets_value_when_key_exists
test_it_does_not_fail_when_forgetting_missing_key
test_it_clears_all_values
test_it_stores_multiple_values
test_it_forgets_multiple_values
test_it_rejects_invalid_key
test_it_preserves_serialized_payload
test_it_fails_safely_when_store_is_unavailable
```

Lifecycle tests:

```text
test_it_invalidates_value_by_key
test_it_invalidates_values_by_tag
test_it_invalidates_namespace_by_version
test_it_serves_stale_value_when_policy_allows_it
test_it_refuses_stale_value_when_policy_forbids_it
test_it_refreshes_value_when_refresh_ahead_window_is_reached
test_it_evicts_least_recently_used_value_when_capacity_is_reached
test_it_does_not_evict_value_when_capacity_is_safe
```

Distributed tests:

```text
test_it_routes_same_key_to_same_node
test_it_routes_different_keys_across_partitions
test_it_fails_over_when_primary_node_is_unhealthy
test_it_reads_from_replica_when_policy_allows_it
test_it_does_not_claim_strong_consistency_when_store_is_eventual
test_it_rebalances_partitions_when_node_is_added
```

Stampede tests:

```text
test_it_allows_only_one_loader_when_many_requests_miss_same_key
test_it_serves_stale_value_while_refresh_is_locked
test_it_returns_controlled_failure_when_lock_timeout_is_reached
test_it_applies_ttl_jitter_to_reduce_simultaneous_expiration
```

## 12. Docs plan

`docs/` mora mirrorovati `System/`.

```text
docs/
└── System/
    ├── how-this-works.md
    ├── public-api.md
    ├── system-design.md
    ├── failure-modes.md
    ├── performance-model.md
    ├── distributed-cache-model.md
    ├── invalidation-model.md
    ├── replacement-policy-model.md
    ├── consistency-model.md
    ├── Flows/
    ├── Capabilities/
    ├── Configuration/
    └── Foundation/
```

Obavezni sistemski docs:

```text
system-design.md
-> big picture, tiers, stores, lifecycle

invalidation-model.md
-> methods vs strategies vs expiration vs refresh

replacement-policy-model.md
-> LRU, LFU, FIFO, size-aware, limits

distributed-cache-model.md
-> partitions, nodes, replicas, consistency

failure-modes.md
-> source failure, store failure, lock timeout, stale fallback

performance-model.md
-> hit rate, miss rate, latency, size, contention

security-model.md
-> encryption, payload safety, logs, key validation
```

Tvoj architecture standard traži da dokumentacija objašnjava šta slice owns, zašto postoji, šta ulazi i izlazi, šta
zavisi od čega i šta ne sme da procure preko granice.

## 13. Acceptance criteria

Ovo bih stavio kao finalnu definiciju “perfektnog” cache sistema:

```text
- Production root je System/
- Nema src/
- Nema Services, Managers, Helpers, Utils, Common, Misc
- Nema Drivers/Decorators/Strategies kao glavnih foldera
- Public API je mali, stabilan i težak za pogrešnu upotrebu
- Interni model razlikuje missing, null, expired, stale, invalidated, evicted
- Clock je obavezan, nema direktnog time() po core logici
- Svaki store prolazi isti contract test suite
- File store ima safe path mapping, locking i atomic write
- Redis store ne pušta Redis detalje u public API
- Lifecycle capability jasno pokriva expiration, invalidation, refresh i replacement
- Stampede protection postoji pre write-behind-a
- Distributed folderi ne obećavaju više nego što store može da garantuje
- Observability ne loguje payload
- Encryption ne čuva plaintext
- Tests pokrivaju happy, failure, edge, regression, abuse slučajeve
- docs/ mirroruje System/
- Svaki ownership folder ima how-this-works.md
```

## 14. Moj finalni sud

Da, napravio bih baš ovako: **jedan veliki `ManageCacheLifecycle` capability
sa `InvalidationMethods`, `InvalidationStrategies`, `ReplacementPolicies` i srodnim policy grupama**.

To je bolja navigacija nego da imaš pet ravnopravnih capability foldera koji svi pričaju delove iste priče.

Finalna filozofija:

```text
System/
  Flows/                 šta korisnik/sistem radi
    Operations/          osnovne operacije (Read, Store, Forget, Clear)
    Lifecycle/           upravljanje životom (Remember, Invalidate, Refresh, Evict, Warm)
    Protection/         zaštita i oporavak (Protect, Sync, Recover)
    Compiled/           compiled artifacts (Compile, Read, Clear, Warm)
  Capabilities/
    Lifecycle/           kako vrednost živi i nestaje
    Storage/             gde vrednost fizički stoji
    Distribution/        kako se čita kroz nivoe i skalira horizontalno
    Source/             source sync i zaštita od stampede-a
    Observability/       kako se vidi ponašanje u produkciji
    CompiledCache/      framework artifact subsystem
```

Ovo je već ozbiljna komponenta. Ne izgleda kao Laravel helper. Ne izgleda kao Redis klon. Izgleda kao **framework-grade
caching subsystem** koji može da raste bez raspada.
