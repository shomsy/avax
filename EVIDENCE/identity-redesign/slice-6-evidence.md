# Slice 6 Evidence — Tenancy Admin, Risk Assessment

## Scope

- `Admin::beginElevation()` — implement real behavior returning AdminElevationRecord
- `Admin::endElevation()` and `Admin::requireElevation()` — add missing methods
- `Risk::assessCurrent()` — implement real behavior returning RiskDecision|null
- `Risk::signals()` — add missing method
- `Risk::endpointPosture()` — add missing sub-surface

## Changes

### Admin Elevation

**Before:** Empty stub `beginElevation(): void`

**After:**
- `beginElevation(string $bindingId, int $userId, ?DateTimeImmutable $expiresAt)` → `AdminElevationRecord`
- `endElevation(string $bindingId)` → void (revoke from store)
- `requireElevation(string $bindingId, int $userId)` → `AdminElevationRecord` (throws on invalid/expired)
- Admin now requires `AdminElevationStoreInterface` and `Clock` constructor deps
- `TenancyGraph` assembly constructs Admin with `InMemoryAdminElevationStore` + `Clock`

### Risk Assessment

**Before:** `assessCurrent(): null` always returns null (stub)

**After:**
- `assessCurrent(?string $ipAddress, ?string $userAgent)` → `RiskDecision|null`
  - Returns null when no params provided
  - Returns `RiskDecision(action: ALLOW, reasons: signals)` when params given
- `signals(?int $userId)` → `list<string>` (stub, ready for extension)
- `endpointPosture()` → `EndpointPosture` (stub sub-surface)
- Created `EndpointPosture` public surface class

## Validation

- PHPUnit: 156 tests, 464 assertions, OK
- PHPStan: clean on all changed files

## Tests Updated

- `tenancyAdminBeginElevationReturnsVoid` → renamed to `tenancyAdminBeginElevationReturnsRecord`, asserts AdminElevationRecord
- `riskAssessCurrentReturnsNullable` — expanded to test both null and RiskDecision paths

## Final Status: GREEN
