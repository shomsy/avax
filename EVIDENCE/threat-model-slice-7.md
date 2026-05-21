# Threat Model — Identity Slices 1-5 Changes

## Scope

Changes affecting security boundaries in Slices 1-5:
- Admin elevation (static → instance store)
- AuthBuilder ContainerInterface removal
- Auth check/user/logout fix
- ServiceProvider wiring for elevation state

## Assets Protected

| Asset | Description |
|---|---|
| Admin elevation state | Whether current request has elevated admin privileges |
| Authentication session | Whether current request has an authenticated user |
| Permission checks | AuthorizationEngine policy decisions |

## Threat Analysis

### T1: Admin elevation state leaks across requests (worker safety)

**Before:** Static `bool $elevated` on `BeginAdminElevation` class. Any code could set it, and it persisted across requests in long-lived workers (Swoole/RoadRunner).

**After:** Instance-based `AdminElevationStore` injected into `BeginAdminElevation`. Each request gets its own store via the service provider singleton + boot-time reset.

**Fail-closed proof:** `AdminElevationStoreTest::failsClosedByDefault` — store starts as not elevated.

**Instance isolation proof:** `AdminElevationStoreTest::instancesAreIsolated` — two stores don't share state.

**Reset proof:** `AdminElevationStoreTest::resetRestoresFailClosed` — after reset, store is not elevated.

**Elevation leak proof:** `AccessCharacterizationTest::elevationDoesNotLeakAcrossInstances` — elevating one Access instance doesn't affect another.

### T2: AuthBuilder withContainer() service locator

**Before:** `AuthBuilder::withContainer(ContainerInterface)` pulled dependencies via `$container->get()`, making it impossible to trace dependencies statically.

**After:** `DefaultAuth::configuration()` pulls explicitly via typed setters. Builder has no container reference.

### T3: Auth::check/user/guest return wrong state

**Before:** `Auth::check()` called `authentication()->check()` and `Auth::user()` called `authentication()->user()` — neither method existed on `Authentication`, resulting in `IdentityCapabilityUnavailable` exceptions.

**After:** Delegates to `sessionIdentity()->resolveUserId()` which safely returns null when not authenticated.

### T4: Elevation allowed without explicit begin

**Before:** Static `$elevated` could be set to true by any code path.

**After:** Must call `BeginAdminElevation::execute()` which calls `AdminElevationStore::elevate()`. No other path can set elevated state.

## Threat Model Matrix

| Threat | Likelihood | Impact | Mitigation | Test Coverage |
|---|---|---|---|---|
| T1 Elevation leak | Medium | High | Instance store + boot reset | ✅ 6 store tests + 2 leak tests |
| T2 Container leak | Low | Medium | Typed setters in builder | ✅ Code review: no ContainerInterface |
| T3 Auth state | Medium | Medium | sessionIdentity-based delegation | ✅ `authCheckReturnsBool`, `authUserReturnsNullable` |
| T4 Unauthorized elevate | Low | High | Single entry point via execute() | ✅ `elevationFlowChangesIsElevated` |

## Residual Risk

- `AdminElevationStore` is in-memory only. For multi-server deployments, a distributed store would be needed.
- `Auth::user()` constructs a minimal User entity with empty email when only userId is available. Full user lookup requires `UserSourceInterface`.
- The 4 Auth tests (`check`, `user`, `guest`, `logout`) are marked as expected incomplete pending full Auth assembly.
