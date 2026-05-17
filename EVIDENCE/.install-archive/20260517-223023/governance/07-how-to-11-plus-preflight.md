# Preflight: Stage how-to governance 11+ hardening

## Status

- **Branch**: main
- **Commit**: a97529645 (hardening: V5.8.8 PHPStan, Runtime Gate & Truth Integrity Closure)
- **Worktree Status**: DIRTY (M: how-to-code-review.md, how-to-dependency-injection.md, how-to-production-readiness.md,
  how-to-runtime-composition.md, how-to.txt, TODO.md, CURRENT_TRUTH.md, AuthBuilder.php, ApplicationBuilder.php,
  DispatchConfiguredRoute.php; ??: how-to-git.md, various EVIDENCE files)

## Discovered how-to inventory

- .agents/how-to/how-to-architecture-extension-with-ddd.md
- .agents/how-to/how-to-architecture.md
- .agents/how-to/how-to-clean-code.md
- .agents/how-to/how-to-code-review.md
- .agents/how-to/how-to-code-style.md
- .agents/how-to/how-to-coding-standards.md
- .agents/how-to/how-to-dependency-injection.md
- .agents/how-to/how-to-design-components.md
- .agents/how-to/how-to-document.md
- .agents/how-to/how-to-dogfooding.md
- .agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md
- .agents/how-to/how-to-git.md
- .agents/how-to/how-to-modern-php-attributes-di.md
- .agents/how-to/how-to-production-readiness.md
- .agents/how-to/how-to-runtime-composition.md
- .agents/how-to/how-to-system-performance.md
- .agents/how-to/how-to-system-security.md
- .agents/how-to/how-to-unit-test.md
- .agents/how-to/how-to-use-advanced-architecture-patterns.md

## Documents read

- AGENTS.md
- CURRENT_TRUTH.md
- EVIDENCE/EXECUTION.md
- .agents/management/TODO.md
- .agents/management/ACTIVE.md
- .agents/management/BUGS.md

## Documents to be patched (target list)

- how-to-architecture.md
- how-to-dependency-injection.md
- how-to-runtime-composition.md
- how-to-design-components.md
- how-to-code-review.md
- how-to-architecture-extension-with-ddd.md
- how-to-production-readiness.md
- how-to-git.md
- how-to-unit-test.md
- how-to-clean-code.md
- how-to-document.md
- how-to-system-security.md
- how-to-system-performance.md

## Rule areas audited

- ServiceProvider requirements
- Component filesystem shape
- Concept words as folders
- Container lifetime/scope taxonomy
- PublicSurface factory boundaries
- DDD factory assembly limits
- Exception Register protocol
- Gate self-tests
- Quality ratchets
- AI code suspicion
- Large unit review thresholds
- Canonical term registry
- Status state machine
- Security/performance triggers
- Examples as architecture
- Component status ownership

## Known ambiguity areas

- ServiceProvider "active" vs "every" contradiction.
- Factory assembly vs Domain object creation limits.
- Container scope mapping to explicit APIs.

## Known contradiction areas

- Some documents say "every ACTIVE component" needs a provider, others imply only those with runtime behavior.

## Gate enforcement gaps

- No automated check for "hollow" ServiceProviders.
- No automated check for "concept words" as folders (except some custom rectors).
- No automated check for PublicSurface behavior leaks (requires manual audit triggers).

## Validation commands

- composer validate --no-check-publish
- composer dump-autoload -o
- php tooling/governance/check-truth-consistency.php
- php tooling/refactor/check-runtime-composition-leaks.php
- php tooling/components/check-component-runtime-assembly.php
- php tooling/refactor/check-public-surface.php
- php tooling/components/check-hollow-public-surfaces.php
