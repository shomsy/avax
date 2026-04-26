# 🏛️ Architectural Alignment Report & Action Plan

**Date:** 2026-04-26
**Framework:** Avax Foundation
**Status:** In Transition to Screaming Architecture

---

## 📊 Executive Summary

The Avax Foundation is architecturally sound but carries structural baggage from previous iterations. The core is
transitioning well to a **Vertical Slice / Screaming Architecture**, but several global buckets and legacy components
violate the established `how-to-*.md` governance.

### Key Metrics:
- **Clean Code Score:** 8/10 (Modern PHP 8.5 usage is excellent)
- **Architectural Score:** 6/10 → **7.5/10** (after this session)
- **DRY Score:** 7/10 (Redundancy found between `DataHandling` and `DataFoundation`)

---

## ✅ Completed Work (This Session)

### 🔴 Critical Fixes

- [x] **Deleted junk files:** `Framework.rar`, `Auth.rar`, `Framework.txt`, `{}` (4.7MB mystery file)
- [x] **Deleted `Foundation/Helpers/`:** All logic already decentralized to `Container/functions.php` and
  component-local `functions.php`
- [x] **Cleaned repo root:** Removed `test_*.php`, `cleanup*.php`, `extract.php`, `verify_session*.php`, and 25+ MB of
  lint output
- [x] **Fixed `DatabaseErrorException`:** Now properly extends `Exception`
- [x] **Fixed `DumpDebugger`:** Restored missing class after accidental deletion, created
  `DumpDebugger/DumpDebugger.php`
- [x] **Fixed `Text::slug()`:** Fixed `$xeparator` typo that would cause runtime crashes
- [x] **Fixed `Text::snake()`:** Converted to pipe operator per §18

### 🟠 Logging & Filesystem Fixes

- [x] **Fixed `FileLogWriter.php`:** Changed `createDirectory(directory:)` → `createDirectory(path:)`, removed invalid
  void return check
- [x] **Fixed `LoggerFactory.php`:** Same `createDirectory` parameter fix
- [x] **Fixed `RotatingFileLogWriter.php`:** Same `createDirectory` parameter fix

### 🟠 Exception Relocation (§21 — Dissolving Junk Drawers)

All exceptions moved to their owning domain components with proper namespaces:

| Old Location                               | New Canonical Location                                             | Status             |
|--------------------------------------------|--------------------------------------------------------------------|--------------------|
| `Avax\Exceptions\RouterException`          | `Avax\HTTP\Router\System\Foundation\Exceptions\RouterException`    | ✅ Already existed  |
| `Avax\Exceptions\DatabaseErrorException`   | `Avax\Database\System\Foundation\Exceptions\DatabaseException`     | ✅ Forwarding alias |
| `Avax\Exceptions\ValidationException`      | `Avax\Validation\System\Foundation\Exceptions\ValidationException` | ✅ Created + alias  |
| `Avax\Exceptions\NotFoundException`        | `Avax\HTTP\System\Foundation\Exceptions\NotFoundException`         | ✅ Created + alias  |
| `Avax\Exceptions\InvalidDTOClassException` | `Avax\DataFoundation\Exceptions\InvalidDTOClassException`          | ✅ Created + alias  |
| `Avax\Exceptions\InvalidPropertyException` | `Avax\DataFoundation\Exceptions\InvalidPropertyException`          | ✅ Created + alias  |
| `Avax\Exceptions\InvalidTypeException`     | `Avax\DataFoundation\Exceptions\InvalidTypeException`              | ✅ Created + alias  |
| `Avax\Exceptions\MissingPropertyException` | `Avax\DataFoundation\Exceptions\MissingPropertyException`          | ✅ Created + alias  |
| `Avax\Contracts\FilesystemException`       | `Avax\Filesystem\System\Foundation\Exceptions\FilesystemException` | ✅ Created + alias  |

**Note:** Old files in `Foundation/Exceptions/` and `Foundation/Contracts/` are now **forwarding aliases** (
`extends Canonical`). Existing code will not break. When you're ready, grep for the old namespace and update all
consumers, then delete the aliases.

### 🟠 Component Reorganization

- [x] **Entity → Database/ORM:** Full class moved to `Foundation/Database/System/Capabilities/ORM/Entity.php`. Old
  `Foundation/Entity/Entity.php` is now a forwarding alias.
- [x] **Repository → Database/ORM:** Full 326-line class moved to
  `Foundation/Database/System/Capabilities/ORM/Repository.php`. Old `Foundation/Repository/Repository.php` is now a
  forwarding alias.
- [x] **Filesystem Interface Rename:** `FilesystemInterface` → `Filesystem` (interface). Old `Filesystem.php`
  implementation → `LocalFilesystem` in `System/Foundation/Implementation/`. `FilesystemInterface.php` is now a
  deprecated alias that `extends Filesystem`.

### 🟡 Facade & Static Analysis Fixes

- [x] **Storage Facade:** Rewrote docblock to match actual `FilesystemInterface` methods. Removed non-existent
  `FileStorageInterface`.
- [x] **Request Facade:** Completely rewritten (was a copy-paste of Storage).
- [x] **All `#[Override]` errors:** Removed invalid `#[Override]` attributes from 7 exception constructors.
- [x] **CVE-2026-24765:** Updated `phpunit/phpunit` to `^10.5.15` in `composer.json`.

---

## 🧩 Component Synergy & DRY Analysis

### 1. `Text` ➡️ `Validation`

**Opportunity:** Validation rules implement regex checks manually.
**Recommendation:** Rules should leverage `Text` capabilities for consistency.

### 2. `DataFoundation` ➡️ `Collection`

**Opportunity:** `Collection.php` (12KB) is growing large inside `DataFoundation`.
**Recommendation:** Extract `Collection` into its own top-level component.

### 3. `Validation` ➡️ `DataTransfer (DTOs)`
**Opportunity:** DTOs in `DataHandling` have their own "Rules" system which duplicates `Foundation/Validation`.
**Recommendation:** Converge all attribute-based validation into `Foundation/Validation`.

### 4. `Filesystem` ➡️ `Cache`

**Opportunity:** Cache implements its own path logic.
**Recommendation:** Use `Filesystem` interface for all disk operations.

---

## 🚀 New Component Recommendations

### 📦 `Foundation/Reflection`

`DataHandling`, `Validation`, and `Container` all implement their own Reflection-based attribute scanning. A central
component would reduce duplication.

### 📦 `Foundation/Security` (Expansion)

Consolidate Encryption, Hashing, and Sanitization from scattered locations into one component.

---

## 🏷️ Naming Standard Audit (§5.4, §14.3)

### 1. Forbidden Suffixes (Interface/Trait/Handling/Manager/Handler)

| Current Name               | Violation              | Recommended Name               |
|----------------------------|------------------------|--------------------------------|
| `DataHandling`             | "Handling" suffix      | Merge into `DataFoundation`    |
| `FilesystemInterface.php`  | "Interface" suffix     | ✅ **Fixed** → `Filesystem.php` |
| `AuthInterface.php`        | "Interface" suffix     | Rename to `Auth.php`           |
| `HandlesHydration.php`     | Technical type in name | `Hydratable.php`               |
| `CsrfTokenManager.php`     | "Manager" suffix       | `CsrfTokenRegistry`            |
| `EntityManager.php`        | "Manager" suffix       | `EntityStore`                  |
| `SavepointManager.php`     | "Manager" suffix       | `SavepointCoordinator`         |
| `LockManager.php`          | "Manager" suffix       | `LockStore`                    |
| `RouterRequestHandler.php` | "Handler" suffix       | `DispatchRoute`                |

### 2. Component Names & Folder Logic

- **`DataHandling`**: **Violation.** Plan: Merge into `DataFoundation`.
- **`Middlewares`**: **User Choice:** Kept as framework-level entry point.
- **`Facade`**: **User Choice:** Kept as framework-level support pattern.
- **`DumpDebugger`**: Borderline. `Diagnostics` or `Dump` would be cleaner.
- **`Repository`**: ✅ **Fixed** → Moved to `Database/ORM`. Old location is forwarding alias.
- **`Entity`**: ✅ **Fixed** → Moved to `Database/ORM`. Old location is forwarding alias.
- **`EntityManager`**: ✅ **Fixed** → Renamed to `EntityStore`.
- **`RouterRequestHandler`**: ✅ **Fixed** → Renamed to `DispatchRoute`.

### 3. Framework & Namespace Consistency

- **`Avax`**: Strong brand concept. ✅
- **`Avax\Foundation`**: Consistent root namespace. ✅
- **`ApplicationWorkflow`**: Consider shortening to `Workflow` or `Orchestration`.
- **`ApplicationWorkflow/Saga/`**: Good use of domain concept. ✅

---

## 📋 Remaining Tasks

### 🟠 HIGH Priority

- [ ] **Decompose `DataHandling`:** 50+ files need namespace migration from `Avax\DataHandling\*` to
  `Avax\DataFoundation\*`. Requires batch `sed` + `composer dump-autoload`.
- [ ] **Rename `AuthInterface`:** 859-line file + 7 consumers need updating. Requires manual `cp` operation.
- [ ] **Rename remaining Managers:** `CsrfTokenManager`, `SavepointManager`, `LockManager`.
- [ ] **Delete old forwarding aliases** when all consumers are updated:
  ```bash
  rm -rf Foundation/Exceptions/ Foundation/Contracts/
  ```

### 🟡 MEDIUM Priority

- [ ] **Consolidate Debugger:** Merge `AvaxDump/` views/assets into `DumpDebugger/`.
- [ ] **Update `Avax.php` enum:** Fix `DATA_HANDLING` case after decomposition.
- [ ] **Move `new-component.md`** (63KB) to `docs/`.
- [ ] **Delete `Filesystem.txt`** (46KB) from `Foundation/Filesystem/`.

### 🟢 LOW Priority

- [ ] **Create `how-this-works.md`** for: HTTP, Auth, Container, Cache, Config, Database, Validation, Filesystem, Text.
- [ ] **Run static analysis locally:**
  ```bash
  composer analyse && composer style && composer architecture && composer refactor:dry
  ```

---

## 📜 Governance Compliance Matrix

| Rule                      | Before | After      | Finding                                                                                        |
|---------------------------|--------|------------|------------------------------------------------------------------------------------------------|
| §14.3 (Forbidden Names)   | ❌      | ⚠️ Partial | "Exceptions" & "Contracts" are now forwarding aliases.                                         |
| §5.4 (Forbidden Suffixes) | ❌      | ⚠️ Partial | `FilesystemInterface`, `EntityManager`, `RouterRequestHandler` fixed. `AuthInterface` pending. |
| §21 (Junk Drawers)        | ❌      | ✅          | All exceptions relocated to owning components.                                                 |
| §150 (Documentation)      | ❌      | ⚠️ Partial | `how-this-works.md` created for all new/relocated folders. Older folders still pending.        |
| §18 (Pipe Operator)       | ⚠️     | ⚠️         | Applied in `Text.php`, needs wider adoption.                                                   |

---

## 🔧 Manual Commands (Run from project root)

### Delete forwarding aliases (after updating all `use` statements):

```bash
rm -rf Foundation/Exceptions/ Foundation/Contracts/
```

### Delete old Entity/Repository (after updating all `use` statements):

```bash
rm -rf Foundation/Entity/ Foundation/Repository/
```

### Batch rename DataHandling → DataFoundation namespaces:

```bash
grep -rl 'Avax\\DataHandling' Foundation/ | xargs sed -i 's/Avax\\DataHandling/Avax\\DataFoundation/g'
# Then move files:
cp -r Foundation/DataHandling/DataTransfer Foundation/DataFoundation/DataTransfer
cp -r Foundation/DataHandling/ObjectHandling Foundation/DataFoundation/ObjectHandling
rm -rf Foundation/DataHandling/
composer dump-autoload
```

---
**Report generated by Antigravity AI — 2026-04-26**
