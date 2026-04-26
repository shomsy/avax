# 🏛️ Architectural Alignment Report & Action Plan

**Date:** 2026-04-26
**Framework:** Avax Foundation
**Status:** In Transition to Screaming Architecture

---

## 📊 Executive Summary

The Avax Foundation is architecturally sound but currently carries significant "structural baggage" from previous
iterations. The core is transitioning well to a **Vertical Slice / Screaming Architecture**, but several global buckets
and legacy components violate the established `how-to-*.md` governance.

### Key Metrics:

- **Clean Code Score:** 8/10 (Modern PHP 8.5 usage is excellent)
- **Architectural Score:** 6/10 (Global "Exceptions", "Contracts", and "Helpers" violate §14.3)
- **DRY Score:** 7/10 (Redundancy found between `DataHandling` and `DataFoundation`)

---

## 🧩 Component Synergy & DRY Analysis

### 1. `Text` ➡️ `Validation`

**Opportunity:** The `Validation` component currently implements many regex-based checks (Email, Slug, etc.) manually.
**Recommendation:** `Validation` rules should leverage `Foundation/Text` capabilities. For example, `ValidateSlug`
should use `Text::slug()` logic to ensure consistency between "what is generated" and "what is validated".

### 2. `DataFoundation` ➡️ `Collection`

**Opportunity:** `Collection.php` is growing large inside `DataFoundation`.
**Recommendation:** Extract `Collection` into its own top-level component. Many other components (Database, HTTP, Cache)
need a robust collection implementation. Making it a standalone component ensures it doesn't leak `DataFoundation`
specifics into the `Database` layer.

### 3. `Validation` ➡️ `DataTransfer (DTOs)`

**Opportunity:** DTOs in `DataHandling` have their own "Rules" system which duplicates `Foundation/Validation`.
**Recommendation:** Converge all attribute-based validation into the `Foundation/Validation` component. DTOs should
simply be *consumers* of validation attributes, not owners of validation logic.

### 4. `Filesystem` ➡️ `Cache`

**Opportunity:** `Cache` implements its own path logic for file storage.
**Recommendation:** The `Cache` component must strictly use `FilesystemInterface` for all disk operations to allow for
easy swapping to Cloud/Redis storage without touching Cache logic.

---

## 🚀 New Component Recommendations

### 📦 `Foundation/Reflection`

**Purpose:** Currently, `DataHandling`, `Validation`, and `Container` all implement their own Reflection-based attribute
scanning.
**Benefit:** A central Reflection component following §5.4 (Clean Code) would provide a unified, cached API for reading
attributes and property types, significantly reducing overhead and complexity in DTO/DI logic.

### 📦 `Foundation/Security` (Expansion)

**Purpose:** Currently, `Auth` handles password hashing and `SensitiveParameter` logic is scattered.
**Benefit:** Consolidate all security-sensitive operations (Encryption, Hashing, Sanitization) into the `Security`
component.

---

## 🛠️ Actionable Tasks

### 🔴 CRITICAL: Deletion & Fixes (Must be done manually)

- [ ] **Delete Junk Files:** `rm Foundation/Framework.rar`, `Foundation/Auth.rar`, `Foundation/Framework.txt`.
- [ ] **Delete Mystery File:** `rm "Foundation/{}"` (4.7MB file).
- [ ] **Delete Deprecated Dirs:** `rm -rf Foundation/Helpers/` (All helpers are now in `Container` or local
  `functions.php`).
- [ ] **Clean Repo Root:** Delete all `test_*.php`, `cleanup.php`, and giant `*.txt` lint results.
- [ ] **Fix Database Errors:** `DatabaseErrorException` now extends `Exception`, but it must be moved to
  `Foundation/Database/`.

### 🟠 HIGH: Relocation & Ownership (§13, §21)

- [ ] **Dissolve `Foundation/Exceptions/`:** Move all 8 classes to their respective owners (HTTP, Database, Validation).
- [ ] **Dissolve `Foundation/Contracts/`:** Move `FilesystemException` to `Foundation/Filesystem/`.
- [ ] **Dissolve `Foundation/Middlewares/`:** Move `MiddlewareInterface` to `Foundation/Commands/`.
- [ ] **Consolidate Debugger:** Merge `AvaxDump/` and root `DumpDebugger.php` into `Foundation/DumpDebugger/`.
- [ ] **Decompose `DataHandling`:** This is a forbidden name (§5.4). Merge its logic into `DataFoundation`.

### 🟡 MEDIUM: Modernization & Hygiene

- [ ] **Update `Avax.php`:** Fix stale path mappings for `CACHE`.
- [ ] **Deprecate Facades:** Move logic from `Foundation/Facade/` to explicit DI in all controllers.
- [ ] **Modernize Repository:** Update `Foundation/Repository/` to use Interfaces instead of `method_exists`.
- [ ] **Move Entity:** Move `Foundation/Entity/Entity.php` to `Foundation/Database/System/Capabilities/ORM/`.

### 🟢 LOW: Documentation (§150)

- [ ] **Create `how-this-works.md`:** Start with `HTTP`, `Auth`, and `Container`.
- [ ] **Run Analysis:** Locally run `composer analyse` and `composer style` to clear remaining warnings.

---

## 📜 Governance Compliance Matrix

| Rule                      | Status     | Finding                                           |
|---------------------------|------------|---------------------------------------------------|
| §14.3 (Forbidden Names)   | ❌ Failing  | "Helpers", "Exceptions", "Contracts" still exist. |
| §5.4 (Forbidden Suffixes) | ❌ Failing  | "DataHandling" uses forbidden "Handling" suffix.  |
| §150 (Documentation)      | ❌ Failing  | No `how-this-works.md` files found.               |
| §18 (Pipe Operator)       | ⚠️ Partial | Applied in `Text.php`, needs wider adoption.      |

---

## 🏷️ Naming Standard Audit (§5.4, §14.3)

### 1. Forbidden Suffixes (Interface/Trait/Handling/Manager/Handler)

Per `how-to-clean-code.md` §5.4 and §14.3, technical type suffixes and generic roles are strictly forbidden.

- **`DataHandling`** ➡️ **Violation:** Forbidden "Handling" suffix. Recommend renaming to **`Data`** or merging into *
  *`DataFoundation`**.
- **`AuthInterface.php`**, **`FilesystemInterface.php`**, **`ConfiguratorInterface.php`** ➡️ **Violation:** Forbidden "
  Interface" suffix.
- **`HandlesHydration.php` (Trait)** ➡️ **SMELL:** Contains technical type in name. Rename to the capability: *
  *`Hydratable`**.
- **`CsrfTokenManager.php`**, **`EntityManager.php`**, **`SavepointManager.php`**, **`LockManager.php`** ➡️ **Violation:
  ** Forbidden "Manager" suffix (§5.4). Rename to ownership concepts (e.g., `CsrfTokenRegistry`, `EntityStore`,
  `LockStore`).
- **`RouterRequestHandler.php`** ➡️ **Violation:** Forbidden "Handler" suffix. Rename to the action it performs (e.g.,
  `DispatchRoute`).

### 2. Component Names & Folder Logic

Per `how-to-architecture.md` §14.3, folders MUST be named using Verbs or Ownership concepts.

- **`DumpDebugger`**: Borderline. **`Dump`** or **`Diagnostics`** would be cleaner ownership concepts (§14.3).
- **`Repository`**: Forbidden generic name. Move to **`Database/ORM`** or rename to **`EntityStore`**.
- **`Entity`**: Forbidden generic name. Move to **`Database/ORM`**.
- **`Foundation/Exceptions/`**, **`Foundation/Contracts/`**, **`Foundation/Helpers/`** ➡️ **Violation:** Forbidden
  technical bucket names (§21). (Marked for deletion/relocation in Task List).

### 3. Framework & Namespace Consistency

- **Framework Name:** `Avax` is a strong brand concept.
- **Namespace:** `Avax\Foundation` is consistent. Ensure all new components use this root.
- **`ApplicationWorkflow`**: A bit wordy. Consider **`Workflow`** or **`Orchestration`** to follow the "Screaming"
  principle.
- **`ApplicationWorkflow/Saga/`**: Good use of a domain concept (Saga pattern).

---
**Report generated by Antigravity AI.**
