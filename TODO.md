# TODO: Fix Cache System PHPStan Errors

## Task Summary

Fix 500+ PHPStan errors in the Cache system where method calls use incorrect named arguments that don't match interface
parameter names.

## Root Cause

Interface methods define parameters like:

- `CacheKey $cacheKey` (called as `key:` ❌)
- `StoredCacheRecord $storedCacheRecord` (called as `record:` ❌)
- `CacheStore $cacheStore` (called as `store:` ❌)

## Error Patterns to Fix

### Pattern 1: Wrong Named Arguments
```php
// WRONG ❌
$store->read(clock: $clock, key: $cacheKey)
$store->write(key: $cacheKey, record: $storedCacheRecord)

// RIGHT ✅
$store->read(cacheKey: $cacheKey, clock: $clock)
$store->write(cacheKey: $cacheKey, storedCacheRecord: $storedCacheRecord)
```

### Pattern 2: Missing Required Parameters

Many constructors and methods missing required named parameters.

### Pattern 3: Undefined Properties

- Access to `$record` property that doesn't exist on `CacheStoreRecordWasFound`

## Files to Fix (Priority Order)

### High Priority - Core Storage Files

- [ ] ChainCacheStore.php
- [ ] FallbackCacheStore.php
- [ ] FileCacheStore.php
- [ ] InMemoryCacheStore.php
- [ ] NullCacheStore.php
- [ ] RedisCacheStore.php

### High Priority - Tiered Cache Files

- [ ] TieredCache.php
- [ ] L1MemoryCache.php
- [ ] L2DistributedCache.php
- [ ] ReadFromFastestAvailableTier.php
- [ ] WriteCachedValueToAllTiers.php
- [ ] PromoteCachedValueToFasterTier.php

### Medium Priority - Distribution Files

- [ ] WriteCachedValueToReplicas.php
- [ ] ForgetCachedValueFromAllTiers.php

### Medium Priority - Flow Files

- [ ] ReadCachedValue.php
- [ ] StoreCachedValue.php
- [ ] RememberCachedValue.php
- [ ] RefreshCachedValue.php
- [ ] WarmCache.php
- [ ] ForgetCachedValue.php

### Lower Priority - Test Files

- [ ] Many test files with similar issues

### Lower Priority - Example Files

- [ ] basic-usage.php
- [ ] multi-tier.php

## Progress

- [ ] TODO created
- [ ] Pattern analysis complete
- [ ] (in progress) Fixing files...

## Notes

- The core issue is consistent: interface uses full names (cacheKey, storedCacheRecord), code uses short names (key,
  record)
- This requires changing both the method calls AND understanding the correct parameter order from interfaces
