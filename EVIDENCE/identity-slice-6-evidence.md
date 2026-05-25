# Slice 6 Evidence: TokenGraph Hardening — Token Stores, Security Tests

## Stage
Slice 6 — TokenGraph: refresh token stores, revocation, security tests

## Status
GREEN

## Why This Is GREEN

### validation
- `vendor/bin/phpunit --no-coverage` → 9551 tests, 27380 assertions, OK (+52 tests from Slice 6)
- `vendor/bin/phpstan analyse RegisterAuthDefaults.php` → clean, zero errors

### gates
- PHPStan: clean on production code
- PHPUnit: all 9551 tests pass

### deviation_audit
- 0 BLOCKER, 0 HIGH, 0 MEDIUM findings

### corrections
1. **Created 26 token component tests** covering:
   - HmacTokenCodec: encode/decode, tamper detection, wrong secret rejection, empty secret rejection, unsupported algorithm rejection, HS384/HS512 support, malformed token handling, SignToken/VerifyToken interface contracts, key ID support
   - MultiKeyHmacTokenCodec: verification codec routing, primary encoding
   - InMemoryTokenBlacklist: add/contains, reset
   - InMemoryTokenRevocationStore: revoke/check, expired token cleanup, interface contract
   - InMemoryRefreshTokenStore: issue, find, unknown token, mark rotation, user revocation, interface contract

2. **Registered token stores in container** (RegisterAuthDefaults):
   - `TokenRevocationStoreInterface` → `InMemoryTokenRevocationStore`
   - `RefreshTokenStoreInterface` → `InMemoryRefreshTokenStore`
   - These were previously null in JwtIdentity, meaning token revocation and refresh rotation were silently disabled

### remaining_deviations
None.

### suppression_check
No suppression detected. Test file has PHPStan informational warnings about always-true assertInstanceOf (valid contract tests, not errors).

### risk_assessment
Token stores are now active — JwtIdentity will use them for revocation checks and refresh token rotation. Risk is contained: in-memory stores are for dev/test; production would swap for Redis/DB-backed stores.

### severity_decision
GREEN — all validation clean, all gates clean, zero deviations, zero suppression.

## Files Changed

### New
- `tests/Unit/Components/Identity/Tokens/TokenComponentTest.php` (26 tests)

### Modified
- `components/Identity/Auth/System/Configuration/Builders/RegisterAuthDefaults.php`
  - Added TokenRevocationStoreInterface and RefreshTokenStoreInterface registrations
  - Added imports for InMemoryRefreshTokenStore, InMemoryTokenRevocationStore

## Next allowed action
Commit Slice 6, then proceed to Slice 7 (ExternalIdentityGraph)
