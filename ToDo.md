# 📋 Project ToDo List (Post-Code Review)

**Date:** 2026-04-26 21:15
**Source:** System-Wide Code Review

---

## 🔴 HIGH PRIORITY (Critical Design Issues)

### 1. Refactor Monolithic `ServiceResolver`

- **Finding:** `ServiceResolver.php` is ~5000 lines (God Class).
- **Goal:** Split into focused capability owners to reduce complexity and improve testability.
- **Actions:**
    - [ ] Extract `ResolutionPolicy` engine.
    - [ ] Extract `ResolutionTelemetry` and `Metrics` collection.
    - [ ] Extract `DebugReporting` / `DescribeService` logic.
    - [ ] Leave `ServiceResolver` as a thin orchestrator.

### 2. Harden Pipeline Invariants

- **Finding:** Extension safety relies on implicit step ordering.
- **Goal:** Ensure "Guard" policies cannot be bypassed by future extensions.
- **Actions:**
    - [ ] Implement explicit pre/post condition checks for `ResolutionPipeline` steps.
    - [ ] Implement explicit pre/post condition checks for `Psr15MiddlewarePipeline`.

---

## 🟠 MEDIUM PRIORITY (Governance & Documentation)

### 3. Upgrade System Documentation

- **Finding:** Core components fail "Ship Check" requirements.
- **Goal:** Enable developers to understand execution flow without reading code.
- **Actions:**
    - [ ] **HTTP:** Add Mermaid diagram and "Where to debug first" to `how-this-works.md`.
    - [ ] **Auth:** Create `how-this-works.md` with mandatory Mermaid sequence diagram.
    - [ ] **Container:** Create `how-this-works.md` showing the DI life-cycle.
    - [ ] **Database/ORM:** Create `how-this-works.md` for the relocated Repository/Entity logic.

### 4. Component Consolidation
- [ ] **Consolidate Debugger:** Merge `AvaxDump/` views/assets into `DumpDebugger/`.
- [ ] **Update `Avax.php` enum:** Fix `DATA_HANDLING` case after decomposition.
- [ ] **Move `new-component.md`** (63KB) to `docs/`.
- [ ] **Delete `Filesystem.txt`** (46KB) from `Foundation/Filesystem/`.

---

## 🟡 LOW PRIORITY (Standards & Optimization)

### 5. Standard Adoption

- [ ] **Pipe Operator (§18):** Audit all components for wider adoption of the pipe operator in data flows.
- [ ] **Static Analysis:** Resolve remaining minor warnings in `Foundation/Auth/tests`.
- [ ] **Performance Audit:** Verify reflection cache efficiency across different adapters (Redis/APC).

---

## 🔧 Technical Debt (Cleanup)

- [ ] **Delete old forwarding aliases** (Checked: zero static usages remaining):
  ```bash
  rm -rf Foundation/Exceptions/ Foundation/Contracts/ Foundation/Entity/ Foundation/Repository/
  ```

---
**ToDo list synchronized with Code Review findings — 2026-04-26 21:15**
