# TODO-001: Serialized Payload Boundary Hardening - Implementation Summary

## Date
2026-05-20

## Scope
4 production files + 5 test files + 1 builder file

## Problem Statement
PHP's `unserialize()` with unrestricted `allowed_classes` enables arbitrary object injection attacks when processing untrusted serialized payloads from cache, queues, or cross-process transport.

## Changes Made

### 1. PhpCacheSerializer.php
- **File**: `components/Application/Cache/System/Foundation/Serialization/PhpCacheSerializer.php`
- **Change**: `allowed_classes => true` -> `allowed_classes => []`
- **Additional**: Added check for `__PHP_Incomplete_Class` to reject object payloads that PHP converts instead of returning false
- **Effect**: Cache serialization only allows scalar/array data. Object injection is rejected at the boundary.

### 2. SerializeClosureThroughLibrary.php
- **File**: `components/Foundation/CallableSerialization/System/Capabilities/SerializeCallable/SerializeClosureThroughLibrary.php`
- **Changes**:
  - Added constructor requiring `#[SensitiveParameter] string $secretKey`
  - Rejects empty secret key with RuntimeException
  - Changed `setSecretKey(null)` to `setSecretKey($this->secretKey)` in serialize/unserialize
  - Expanded `allowed_classes` to include full SerializableClosure library chain: `SerializableClosure`, `Signed`, `Native`
  - Added instanceof check after unserialize
- **Effect**: Closure serialization now requires HMAC key for integrity protection. Only library-internal classes are allowed during deserialization.

### 3. RedisCacheStore.php (legacy)
- **File**: `components/Application/Cache/System/Capabilities/Stores/RedisCacheStore.php`
- **Change**: `unserialize($value)` -> `unserialize($value, ['allowed_classes' => false])`
- **Effect**: Legacy Redis cache store no longer instantiates objects from cached payloads.

### 4. BuildCallableSerialization.php
- **File**: `components/Foundation/CallableSerialization/System/Configuration/Builders/BuildCallableSerialization.php`
- **Change**: Pass signing key to `SerializeClosureThroughLibrary` constructor with default fallback
- **Effect**: Builder assembles serializer with proper key, maintaining backward compatibility.

### 5. DecryptValue.php (verification only)
- **File**: `components/Security/Cryptography/System/Flows/DecryptValue/DecryptValue.php`
- **Status**: Already uses `allowed_classes => false`. No changes needed.

## New Security Tests Created

### PhpCacheSerializerSecurityTest.php
- Valid scalar/array payloads still work
- Malicious serialized object payloads are rejected
- Invalid/corrupted payloads fail safely
- Empty string data fails safely
- Wrong format is rejected

### SerializeClosureSecurityTest.php
- Constructor rejects empty secret key
- Round-trip with secret key works
- Tampered payload is rejected
- Wrong secret key fails to deserialize
- Malicious base64 payload is rejected
- Invalid base64 payload is rejected
- Empty string payload is rejected
- Non-closure serialized objects are rejected

### LegacyRedisCacheStoreSecurityTest.php
- Source code verification: allowed_classes => false is present
- Behavior proof: objects become __PHP_Incomplete_Class, not real objects
- Scalars/arrays still work with restricted unserialize
- Corrupted data returns false

### DecryptValueSecurityTest.php
- Source code verification: allowed_classes => false is present
- Behavior proof: objects not instantiated in fallback
- Array fallback works
- Invalid data returns false

### CallableSerializationProofTest.php (updated)
- Updated all SerializeClosureThroughLibrary instantiations to include secretKey
- Fixed test for no-signing mode to use reset() instead of configure('')

## Validation Results
- PHPUnit: 141 tests, 246 assertions, 0 failures, 0 warnings
- PHPStan: clean on all changed files
- Component structure: PASS
- Namespace drift: PASS
- Public surface: PASS
- Runtime leaks: PASS
- Composer validate: PASS

## Security Posture Improvement
| Boundary | Before | After |
|----------|--------|-------|
| PhpCacheSerializer unserialize | allowed_classes: true | allowed_classes: [] + __PHP_Incomplete_Class check |
| SerializeClosureThroughLibrary | setSecretKey(null) | Required non-empty key via constructor |
| SerializeClosureThroughLibrary allowed_classes | true | Only SerializableClosure, Signed, Native |
| RedisCacheStore (legacy) unserialize | bare call | allowed_classes: false |
| DecryptValue unserialize | already safe | no change needed |
