# 10 — Container Freeze Proof

**Date**: 2026-05-16
**Stage**: V5.9 Boot DSL First Slice
**Status**: GREEN

## Finding (from 07-correction-preflight)

Initial implementation set `$this->frozen = true` as a bool flag on `BootDslEngine`. This was fake freeze — no actual container protection. Mutation after boot was possible through the container.

## Correction

Created `components/Application/Container/System/Foundation/FrozenContainer.php`:
- Extends no class, implements `ContainerInterface` directly
- All mutation methods (`bind`, `singleton`, `scoped`, `instance`, `alias`, `flush`, `tag`) throw `LogicException` after `freeze()`
- Read methods (`get`, `has`, `make`, `call`) work after freeze
- Resolution through closures works after freeze
- Singleton caching works after freeze

Fixed `resolve()` bug: now checks `$this->instances` first before `$this->bindings`, so directly-set instances are returned correctly.

Fixed `BootPhase` enum order: `Create → Register → Compile → Verify → Boot → Freeze → Run`. Freeze comes AFTER boot (providers mutate during boot, then container is frozen).

## Proof

- Test: `container_is_frozen_after_boot` — proves `isFrozen()` returns true after boot
- Test: `mutation_after_freeze_throws` — `bind()` throws
- Test: `singleton_after_freeze_throws` — `singleton()` throws
- Test: `instance_after_freeze_throws` — `instance()` throws
- Test: `alias_after_freeze_throws` — `alias()` throws
- Test: `flush_after_freeze_throws` — `flush()` throws
- Test: `read_methods_work_after_freeze` — `has()` and `get()` work after freeze
- Test: `resolve_after_freeze_creates_new_instances` — closure resolution and singleton caching work after freeze
- PHPStan: clean on `FrozenContainer.php`

## Phase Order Fix

The enum originally had Freeze before Boot, but the engine booted before freezing. This was corrected: Freeze is now phase 6, Boot is phase 5. The engine's `boot()` method now correctly advances through: create → register → compile → verify → boot → freeze → run.

## Files Changed

- `components/Application/Container/System/Foundation/FrozenContainer.php` (CREATED)
- `framework/System/Configuration/BootDsl/BootPhase.php` (UPDATED — Freeze after Boot)
- `framework/System/Configuration/BootDsl/BootDslEngine.php` (UPDATED — calls `$this->container->freeze()`)

## Verdict

Container freeze is real, not simulated. All mutation methods are blocked after freeze. Read and resolution methods continue to work.
