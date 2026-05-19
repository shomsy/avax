# Threat Analysis — TODO-001 Serialized Payload Boundary Hardening

## Threat Model: STRIDE Applied to Serialization Boundaries

### 1. PhpCacheSerializer.php — `unserialize($data, ['allowed_classes' => true])`

**Spoofing**: Attacker crafts serialized payload with malicious class names. If those classes exist and have `__wakeup()`/`__destruct()` gadgets, arbitrary code execution occurs.

**Tampering**: Any tampered payload with valid PHP serialization format executes through `unserialize()` with full class access.

**Repudiation**: No audit trail of what classes were deserialized.

**Information Disclosure**: Deserialized objects may contain sensitive data that propagates into application state.

**Denial of Service**: Malformed payloads that trigger deep object graphs or recursive `__wakeup()` can exhaust memory.

**Elevation of Privilege**: Gadget chain exploitation through `allowed_classes => true` can execute arbitrary behavior.

**Fix**: Use `allowed_classes => [list]` with explicit allowlist of known safe cache value classes. If the caller needs arbitrary objects, they must use `JsonCacheSerializer` instead.

### 2. SerializeClosureThroughLibrary.php — `setSecretKey(null)` + bare `unserialize()`

**Spoofing**: Any attacker with access to stored closure payload can deserialize it without a secret key check. The `SerializableClosure` library provides integrity protection through HMAC, but only when a non-null secret key is set.

**Tampering**: Without a secret key, `SerializableClosure` does not verify payload integrity. An attacker can modify serialized closure data and it will execute.

**Repudiation**: No evidence of who serialized what closure.

**Fix**: Require a non-null secret key for both serialization and deserialization. The key must be injected via constructor, not hardcoded or set to null.

### 3. RedisCacheStore.php (legacy) — `unserialize($value)` bare call

**Spoofing**: Any value read from Redis is blindly unserialized. If Redis is shared or compromised, attacker controls deserialized objects.

**Tampering**: Redis payload tampering leads directly to object instantiation.

**Fix**: Add `allowed_classes => false` restriction. Cache values stored by this legacy store should be simple data types, not objects. If objects are needed, use the new `RedisCacheStore` which delegates through `CacheSerializer`.

### 4. DecryptValue.php — `unserialize($plaintext, ['allowed_classes' => false])` fallback

**Assessment**: Already uses `allowed_classes => false`. The data was decrypted first, so trust is higher than raw cache data, but the fallback path exists for backward compatibility with non-JSON encrypted payloads.

**Risk**: LOW-YELLOW. The plaintext was already authenticated via encryption. The `allowed_classes => false` restriction prevents object injection. However, the silent fallback behavior (try JSON → try unserialize → return raw string) could hide unexpected data types.

**Fix**: Document the fallback behavior. Add test proving `allowed_classes => false` works. No code change required for the security aspect.

### 5. RedisCacheStore.php (new) — delegates through CacheSerializer

**Assessment**: The new RedisCacheStore at `System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php` delegates to `CacheSerializer::unserialize()` on line 99. Its security depends on which serializer is injected. The read path on line 72 decodes JSON first, then constructs `SerializedCachePayload` and passes to the serializer. This is safe IF the injected serializer uses restricted `allowed_classes`.

**Risk**: INDIRECT. Mitigated by hardening `PhpCacheSerializer`.

## Threat Classification Summary

| Threat | File | Original Risk | Post-Fix Risk |
|---|---|---|---|
| Object injection via `allowed_classes => true` | PhpCacheSerializer.php | BLOCKER | LOW (explicit allowlist) |
| Closure payload tampering (no secret key) | SerializeClosureThroughLibrary.php | BLOCKER | LOW (required key) |
| Bare unserialize of Redis values | RedisCacheStore.php (legacy) | BLOCKER | LOW (allowed_classes => false) |
| Fallback unserialize on decrypted data | DecryptValue.php | YELLOW | ACCEPTED (already restricted) |
| Indirect serializer delegate risk | RedisCacheStore.php (new) | MEDIUM | LOW (via PhpCacheSerializer fix) |
