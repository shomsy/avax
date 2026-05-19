# Stage Report: V5.8.x Governance Hardening Preflight

## 0. Context

- **Branch**: main
- **Commit**: a9752964530bf170d12e72145f7aafb43502ed25
- **Worktree status**: Dirty (pre-existing changes in how-to files, management, and framework)

## 1. Discovered how-to inventory

Discovered at `.agents/how-to/`:

- how-to-architecture-extension-with-ddd.md
- how-to-architecture.md
- how-to-clean-code.md
- how-to-code-review.md
- how-to-code-style.md
- how-to-coding-standards.md
- how-to-dependency-injection.md
- how-to-design-components.md
- how-to-document.md
- how-to-dogfooding.md
- how-to-events-listeners-event-sourcing-cqrs-realtime.md
- how-to-git.md
- how-to-modern-php-attributes-di.md
- how-to-production-readiness.md
- how-to-runtime-composition.md
- how-to-system-performance.md
- how-to-system-security.md
- how-to-unit-test.md
- how-to-use-advanced-architecture-patterns.md

## 2. Assessment

- **Missing expected documents**: None (all from the user's list are present).
- **Duplicate or overlapping rule areas**:
    - `how-to-dependency-injection.md` and `how-to-runtime-composition.md` both cover container ownership (resolved in
      previous turn with cross-reference, but needs hardening).
    - `how-to-architecture.md` and `how-to-design-components.md` overlap on component structural rules.
- **Documents with stale draft/chat wording**:
    - `how-to-dependency-injection.md` (Section 3.7 and 7 were added in a conversational context).
    - `how-to-runtime-composition.md` (Section 8).
    - `how-to-git.md` (Created in previous turn, very focused).
    - `how-to-code-review.md` (Section 16).
- **Documents with conflicting MUST/MAY/ONLY wording**: TBD during Phase 2 audit.
- **Documents that need patching**: All how-to files for normative consistency. `how-to-code-review.md` needs the
  Dynamic Inventory Rule.
- **Gates that should enforce the rules**:
    - `php tooling/refactor/check-public-surface.php`
    - `php tooling/refactor/check-runtime-leaks.php`
    - `php tooling/governance/check-governance-index-current.php`
- **Validation commands**:
    - `composer validate --no-check-publish`
    - `composer dump-autoload -o`
    - `vendor/bin/phpunit --no-coverage`
    - `vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw`

## 3. Preflight Conclusion

Inventory is complete. Worktree is dirty with pre-existing governance updates that need to be reconciled and hardened.
Proceeding to Phase 1: Worktree baseline.
