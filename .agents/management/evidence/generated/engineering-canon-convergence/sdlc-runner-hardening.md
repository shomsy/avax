# SDLC Runner Hardening Report

This report documents the verification and operational safety of the AvaX SDLC runners in `tooling/sdlc/`.

## Runner Status Verification

### 1. Preflight Runner (`preflight.php`)
- **Action**: Runs initial environment checks, ensuring that PHP 8.4 is available and git repository is accessible.
- **Exit Semantics**: Exits with non-zero on missing binaries or dirty workspace check failures when strictly required.

### 2. Changed-Scope Validation (`validate-changed.php`)
- **Action**: Orchestrates all 10 active checkers targeting only the current change scope.
- **Required Commands**:
  - `git diff --check`
  - `check-engineering-canon-traceability.php`
  - `check-scenario-input.php`
  - `check-coupling-decisions.php`
  - `check-architecture-fitness-functions.php`
  - `check-antipatterns.php`
  - `check-data-correctness-evidence.php`
  - `check-enterprise-application-boundaries.php`
  - `check-runtime-concurrency-safety.php`
  - `check-refactoring-safety.php`
- **Non-Required Commands**:
  - `check-construction-checklist.php` (YELLOW warning only)
  - Canonical truth, leakage, index current, and stage lock.
- **Exit Semantics**: Returns exit code `1` immediately if any required command fails.

### 3. Governance Validation (`validate-governance.php`)
- **Action**: Orchestrates all active checkers.
- **Required Commands**: Matches the changed-scope validation suite strictly.
- **Exit Semantics**: Returns exit code `1` if any required check fails.

### 4. Agent Task Runner (`validate-agent-task.php`)
- **Action**: Runs `preflight.php`, `validate-changed.php`, and `validate-governance.php` sequentially.
- **Exit Semantics**: Exits with code `1` if any of the three runners fail.

## Conclusion
The runner orchestration layer operates deterministically, ensuring that no execution failures are hidden or bypassed.
