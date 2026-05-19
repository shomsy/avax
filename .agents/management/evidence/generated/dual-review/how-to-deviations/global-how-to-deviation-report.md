# Global How-To Deviation Report

Generated: 2026-05-19T23:31:00+02:00

## 1. Executive Summary

- Governance documents found by `find . -path "*/how-to-*.md" -type f`: 96
- Current authoritative `.agents/how-to` documents applied: 20
- How-to deviations: 668
- Global how-to compliance decision: **BLOCKED_BY_HOW_TO**

## 2. Governance Documents Found

- `./.agents/.rules/governance/architecture/how-to-architecture-extension.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-clean-code.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-code-style.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-coding-standards.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/documentation/how-to-document-flow.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/documentation/how-to-document.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/review/how-to-code-review.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/review/how-to-strict-review.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/testing/how-to-unit-test.md` — READ; mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/how-to/how-to-architecture-extension-with-ddd.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-architecture.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-clean-code.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-code-review.md` — READ; authoritative local review process and also mandatory how-to source
- `./.agents/how-to/how-to-code-style.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-coding-standards.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-dependency-injection.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-design-components.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-document.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-dogfooding.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-git.md` — READ; applies to staged output and commit discipline, not unit internals except evidence/staging checks
- `./.agents/how-to/how-to-modern-php-attributes-di.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-production-readiness.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-runtime-composition.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-system-performance.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-system-security.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-unit-test.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-use-advanced-architecture-patterns.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/how-to/how-to-write-avax.md` — READ; authoritative local how-to file; strict review consumes relevant quality/security/runtime/test portions
- `./.agents/management/evidence/archive/legacy-evidence/EVIDENCE.backup-before-harness-v4-1/cleanup/how-to-coverage-gaps.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents/management/evidence/archive/legacy-evidence/cleanup/how-to-coverage-gaps.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/architecture/how-to-architecture-extension.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/architecture/how-to-architecture.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-clean-code.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-code-style.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-coding-standards.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/documentation/how-to-document-flow.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/documentation/how-to-document.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/review/how-to-code-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/review/how-to-strict-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/testing/how-to-unit-test.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-architecture-extension-with-ddd.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-architecture.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-clean-code.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-code-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-code-style.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-coding-standards.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-dependency-injection.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-design-components.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-document.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-dogfooding.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-git.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-modern-php-attributes-di.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-production-readiness.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-runtime-composition.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-system-performance.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-system-security.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-unit-test.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-use-advanced-architecture-patterns.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/architecture/how-to-architecture-extension.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/architecture/how-to-architecture.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-clean-code.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-code-style.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-coding-standards.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/documentation/how-to-document-flow.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/documentation/how-to-document.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/review/how-to-code-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/review/how-to-strict-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/testing/how-to-unit-test.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-architecture-extension-with-ddd.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-architecture.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-clean-code.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-code-review.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-code-style.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-coding-standards.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-dependency-injection.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-design-components.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-document.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-dogfooding.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-git.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-modern-php-attributes-di.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-production-readiness.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-runtime-composition.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-system-performance.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-system-security.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-unit-test.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-use-advanced-architecture-patterns.md` — READ; backup/archive evidence only; not authoritative for this pass
- `./components/Application/Container/how-to-code-review.md` — READ; component-local advisory rule for Application/Container only; global root how-to still wins
- `./components/Application/Container/how-to-coding-standards.md` — READ; component-local advisory rule for Application/Container only; global root how-to still wins
- `./components/Application/Container/how-to-document.md` — READ; component-local advisory rule for Application/Container only; global root how-to still wins
- `./docs/Router/how-to-ARCHITECTURE EXECUTION POLICY.md` — READ; documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./docs/governance/how-to-architecture-extension.md` — READ; documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./docs/governance/how-to-architecture.md` — READ; documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./how-to-write-avax.md` — READ; documentation/reference source; lower precedence than AGENTS.md and .agents/how-to

## 3. Governance Documents Applied

- `.agents/how-to/how-to-architecture-extension-with-ddd.md` — architecture and ownership
- `.agents/how-to/how-to-architecture.md` — architecture and ownership
- `.agents/how-to/how-to-clean-code.md` — coding standard and maintainability
- `.agents/how-to/how-to-code-review.md` — review process
- `.agents/how-to/how-to-code-style.md` — coding standard and maintainability
- `.agents/how-to/how-to-coding-standards.md` — security boundaries
- `.agents/how-to/how-to-dependency-injection.md` — DI, runtime composition, service provider ownership
- `.agents/how-to/how-to-design-components.md` — architecture and ownership
- `.agents/how-to/how-to-document.md` — documentation and evidence
- `.agents/how-to/how-to-dogfooding.md` — internal dogfooding and boundary reuse
- `.agents/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — architecture and ownership
- `.agents/how-to/how-to-git.md` — workflow and commit discipline
- `.agents/how-to/how-to-modern-php-attributes-di.md` — DI, runtime composition, service provider ownership
- `.agents/how-to/how-to-production-readiness.md` — general governance
- `.agents/how-to/how-to-runtime-composition.md` — DI, runtime composition, service provider ownership
- `.agents/how-to/how-to-system-performance.md` — performance and memory
- `.agents/how-to/how-to-system-security.md` — security boundaries
- `.agents/how-to/how-to-unit-test.md` — test proof
- `.agents/how-to/how-to-use-advanced-architecture-patterns.md` — architecture and ownership
- `.agents/how-to/how-to-write-avax.md` — general governance

## 4. Governance Documents Skipped With Reason

- `./.agents/.rules/governance/architecture/how-to-architecture-extension.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-clean-code.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-code-style.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/coding/how-to-coding-standards.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/documentation/how-to-document-flow.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/documentation/how-to-document.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/review/how-to-code-review.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/review/how-to-strict-review.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/.rules/governance/standards/testing/how-to-unit-test.md` — mounted reusable rule; lower precedence than local AvaX how-to rules
- `./.agents/management/evidence/archive/legacy-evidence/EVIDENCE.backup-before-harness-v4-1/cleanup/how-to-coverage-gaps.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents/management/evidence/archive/legacy-evidence/cleanup/how-to-coverage-gaps.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/architecture/how-to-architecture-extension.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/architecture/how-to-architecture.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-clean-code.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-code-style.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/coding/how-to-coding-standards.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/documentation/how-to-document-flow.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/documentation/how-to-document.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/review/how-to-code-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/review/how-to-strict-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/.rules/governance/standards/testing/how-to-unit-test.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-architecture-extension-with-ddd.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-architecture.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-clean-code.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-code-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-code-style.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-coding-standards.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-dependency-injection.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-design-components.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-document.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-dogfooding.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-git.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-modern-php-attributes-di.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-production-readiness.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-runtime-composition.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-system-performance.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-system-security.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-unit-test.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v4-1/how-to/how-to-use-advanced-architecture-patterns.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/architecture/how-to-architecture-extension.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/architecture/how-to-architecture.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-clean-code.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-code-style.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/coding/how-to-coding-standards.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/documentation/how-to-document-flow.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/documentation/how-to-document.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/review/how-to-code-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/review/how-to-strict-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/.rules/governance/standards/testing/how-to-unit-test.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-architecture-extension-with-ddd.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-architecture.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-clean-code.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-code-review.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-code-style.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-coding-standards.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-dependency-injection.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-design-components.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-document.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-dogfooding.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-events-listeners-event-sourcing-cqrs-realtime.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-git.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-modern-php-attributes-di.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-production-readiness.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-runtime-composition.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-system-performance.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-system-security.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-unit-test.md` — backup/archive evidence only; not authoritative for this pass
- `./.agents.backup-before-harness-v6-20260517-221607/how-to/how-to-use-advanced-architecture-patterns.md` — backup/archive evidence only; not authoritative for this pass
- `./components/Application/Container/how-to-code-review.md` — component-local advisory rule for Application/Container only; global root how-to still wins
- `./components/Application/Container/how-to-coding-standards.md` — component-local advisory rule for Application/Container only; global root how-to still wins
- `./components/Application/Container/how-to-document.md` — component-local advisory rule for Application/Container only; global root how-to still wins
- `./docs/Router/how-to-ARCHITECTURE EXECUTION POLICY.md` — documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./docs/governance/how-to-architecture-extension.md` — documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./docs/governance/how-to-architecture.md` — documentation/reference source; lower precedence than AGENTS.md and .agents/how-to
- `./how-to-write-avax.md` — documentation/reference source; lower precedence than AGENTS.md and .agents/how-to

## 5. Rule Families Checked

- `how-to-architecture-extension-with-ddd.md`: PublicSurface delegation; domain modeling placement; no runtime leakage
- `how-to-architecture.md`: flow/capability ownership; folder says flow or capability; recursive ownership
- `how-to-clean-code.md`: correctness, readability, simplicity, cohesive units, meaningful names
- `how-to-code-review.md`: strict review process; hard gates; symptom/root-cause/impact/evidence/risk findings
- `how-to-code-style.md`: constructor promotion, nullable type format, return types, named argument discipline
- `how-to-coding-standards.md`: modern PHP, strict types, architecture and naming standards
- `how-to-dependency-injection.md`: constructor injection, service provider ownership, no service locator, fail-fast dependencies
- `how-to-design-components.md`: canonical component shape; PublicSurface/Flows/Capabilities/Configuration/Foundation roles
- `how-to-document.md`: canonical documentation location, HOW_THIS_WORKS standard, evidence truthfulness
- `how-to-dogfooding.md`: first-party capability reuse, public boundary dogfooding, storage/queue/messaging/internal reuse
- `how-to-events-listeners-event-sourcing-cqrs-realtime.md`: events/listeners/CQRS/realtime vocabulary and placement
- `how-to-git.md`: commit discipline, no unrelated staging, security must scream, gate self-tests
- `how-to-modern-php-attributes-di.md`: modern PHP feature use, attribute validation, DI/autowiring discipline, constructor pressure
- `how-to-production-readiness.md`: readiness color rules, validation agreement, no optimistic readiness claims
- `how-to-runtime-composition.md`: no runtime composition leaks; no hidden fallback construction; boot/run separation
- `how-to-system-performance.md`: no hidden I/O, bounded work, hot path proof, memory discipline
- `how-to-system-security.md`: deny by default, no secret exposure, secure defaults, negative tests for security-sensitive behavior
- `how-to-unit-test.md`: behavior proof, failure/edge tests, public behavior tests, no smoke-only tests
- `how-to-use-advanced-architecture-patterns.md`: no pattern-name dumping grounds; explicit decision rules for advanced patterns
- `how-to-write-avax.md`: local AvaX writing, naming, runtime, evidence, and testing rules

## 6. Deviation Summary By How-To Document

| How-to document / family | Deviation count |
|---|---:|
| `how-to-code-review.md` | 479 |
| `how-to-dependency-injection.md` | 401 |
| `how-to-design-components.md` | 164 |
| `how-to-modern-php-attributes-di.md` | 161 |
| `how-to-clean-code.md` | 59 |
| `how-to-runtime-composition.md` | 23 |
| `how-to-unit-test.md` | 13 |
| `how-to-system-performance.md` | 7 |
| `how-to-coding-standards.md` | 4 |
| `how-to-dogfooding.md` | 3 |
| `how-to-system-security.md` | 3 |
| `how-to-document.md` | 1 |

## 7. Deviation Summary By Review Unit

| Review unit | Deviation count |
|---|---:|
| `components/Application/Cache` | 64 |
| `components/Identity/Auth` | 50 |
| `components/DataStack/Database` | 42 |
| `components/Operations/ApplicationWorkflow` | 34 |
| `components/Application/Container` | 33 |
| `components/Identity/ExternalIdentity` | 30 |
| `components/DataStack/Data` | 27 |
| `components/API/GraphQL` | 21 |
| `components/DataStack/DataTransfer` | 17 |
| `components/HTTP/Request` | 16 |
| `components/Identity/Credentials` | 16 |
| `components/DataStack/Persistence` | 14 |
| `components/Identity/Tokens` | 14 |
| `components/Operations/Events` | 10 |
| `components/Operations/Queue` | 10 |
| `components/SystemDesign` | 10 |
| `framework/System/PublicSurface` | 10 |
| `framework/System/Capabilities/PreCommit` | 10 |
| `components/Identity/Tenancy` | 9 |
| `components/HTTP/Client` | 7 |
| `components/Security/DataProtection` | 7 |
| `components/HTTP/Session` | 7 |
| `components/Security/Redaction` | 7 |
| `components/DeveloperTools/Documentation/Api` | 6 |
| `components/Security/Privacy` | 6 |
| `components/API/ApiBlueprint` | 6 |
| `framework/System/Capabilities/FailureBoundary` | 6 |
| `components/Operations/Mail` | 6 |
| `framework/System/Capabilities/Runtime` | 6 |
| `components/Operations/BackgroundProcesses` | 5 |
| `components/CLI/Console` | 5 |
| `components/HTTP` | 5 |
| `components/Operations/Resilience` | 5 |
| `components/Identity/Access` | 5 |
| `framework/System/Flows/RunApplication` | 5 |
| `components/API/Contracts` | 4 |
| `components/Operations/RuntimeSupervision` | 4 |
| `components/API/OpenAPI` | 4 |
| `components/API/SchemaGeneration` | 4 |
| `components/Application/Text` | 4 |
| `components/Application/Config` | 4 |
| `components/HTTP/Router` | 4 |
| `components/Operations/Concurrency` | 4 |
| `framework/System/Flows/HandleIncomingHttp` | 4 |
| `components/Operations/MemoryLifecycle` | 3 |
| `components/DeveloperTools/Diagnostics` | 3 |
| `components/Foundation/CallableSerialization` | 3 |
| `components/HTTP/ContentNegotiation` | 3 |
| `components/HTTP/URI` | 3 |
| `components/Operations/Filesystem` | 3 |
| `components/Application/Validation` | 3 |
| `components/Operations/Notifications` | 3 |
| `components/Operations/Logging` | 3 |
| `framework/System/Capabilities/ExternalState` | 3 |
| `components/DeveloperTools/DumpDebugger` | 2 |
| `components/Identity/Security` | 2 |
| `components/Integration/ObjectStorage` | 2 |
| `components/Operations/Delivery` | 2 |
| `components/Operations/Realtime` | 2 |
| `components/Application/FeatureFlags` | 2 |
| `components/DeveloperTools/Dx` | 2 |
| `components/DeveloperTools/TestSupport` | 2 |
| `components/HTTP/AfterResponse` | 2 |
| `components/HTTP/Context` | 2 |
| `components/Application/DateTime` | 2 |
| `components/Application/Storage` | 2 |
| `components/Application/Filesystem` | 2 |
| `components/HTTP/SecureRequest` | 2 |
| `components/Operations/MessageBus` | 2 |
| `components/Operations/Scheduler` | 2 |
| `components/Security/Secrets` | 2 |
| `framework/System/Capabilities/Doctor` | 2 |
| `framework/System/Capabilities/Benchmarks` | 2 |
| `framework/System/Capabilities/Security` | 2 |
| `components/Application/Localization` | 1 |
| `components/DeveloperTools/CodeGeneration` | 1 |
| `components/HTTP/Dispatcher` | 1 |
| `components/HTTP/Security` | 1 |
| `framework/System/Configuration/Builders` | 1 |
| `components/HTTP/Response` | 1 |
| `components/Operations/Tasks` | 1 |
| `components/Operations/Observability` | 1 |
| `components/Operations` | 1 |
| `components/Operations/Parallelism` | 1 |
| `components/DeveloperTools` | 1 |
| `components/Presentation` | 1 |
| `components/Security/Cryptography` | 1 |
| `components/Identity` | 1 |
| `framework/System/Flows/BootApplication` | 1 |
| `framework/System/Capabilities/ResourceGovernance` | 1 |
| `framework/System/Capabilities/RuntimeSafety` | 1 |
| `components/Application/Pipeline` | 1 |
| `framework/System/Configuration/BootDsl` | 1 |
| `framework/System/Configuration/Foundation` | 1 |
| `framework/System/Configuration/BuildApplication` | 1 |
| `framework/System/Flows/CreateApplication` | 1 |
| `framework/System/Capabilities/ContainerIntelligence` | 1 |
| `framework/System/Configuration/ConfigureRuntime` | 1 |
| `framework/System/Flows/RunDoctor` | 1 |
| `framework/System/Capabilities/ServeModes` | 1 |
| `framework/System/Capabilities/RuntimeIsolation` | 1 |

## 8. Deviation Summary By Severity

- BLOCKER: 1
- HIGH: 247
- MEDIUM: 414
- LOW: 6

## BLOCKER Deviations

| Deviation ID | Severity | Unit | Governance source | Location | Observed gap | Related SCR |
|---|---|---|---|---|---|---|
| HTD-0602 | BLOCKER | `components/Identity/Auth` | how-to-code-review.md §21; how-to-design-components.md §6.5.1 | `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php` | Configuration builder is 797 lines (>300). | SCR-0602 |

## HIGH Deviations

| Deviation ID | Severity | Unit | Governance source | Location | Observed gap | Related SCR |
|---|---|---|---|---|---|---|
| HTD-0668 | HIGH | `components/HTTP` | how-to-coding-standards.md; AGENTS.md §18; tooling/refactor/check-broken-reference-semantics.php | `components/HTTP/System` | Active broken reference: Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\System\Foundation\Failure\MiddlewareFailure. | SCR-0665 |
| HTD-0016 | HIGH | `components/API/GraphQL` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/API/GraphQL` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0016 |
| HTD-0017 | HIGH | `components/API/OpenAPI` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/API/OpenAPI` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0017 |
| HTD-0023 | HIGH | `components/DataStack/DataTransfer` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DataStack/DataTransfer` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0023 |
| HTD-0030 | HIGH | `components/Foundation/CallableSerialization` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/Foundation/CallableSerialization` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0030 |
| HTD-0034 | HIGH | `components/HTTP/Dispatcher` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/Dispatcher` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0034 |
| HTD-0035 | HIGH | `components/HTTP/Security` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0035 |
| HTD-0037 | HIGH | `components/Identity/Security` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/Identity/Security` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0037 |
| HTD-0039 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-coding-standards.md; AGENTS.md §18; tooling/refactor/check-broken-reference-semantics.php | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`. | SCR-0039 |
| HTD-0040 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-coding-standards.md; AGENTS.md §18; tooling/refactor/check-broken-reference-semantics.php | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`. | SCR-0040 |
| HTD-0041 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-coding-standards.md; AGENTS.md §18; tooling/refactor/check-broken-reference-semantics.php | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`. | SCR-0041 |
| HTD-0042 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-coding-standards.md; AGENTS.md §18; tooling/refactor/check-broken-reference-semantics.php | `components/Operations/ApplicationWorkflow` | Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaStep`. | SCR-0042 |
| HTD-0043 | HIGH | `framework/System/Configuration/Builders` | how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php | `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46` | Raw `is_file()` in framework route-dispatch builder is classified MIGRATE_TO_FILESYSTEM. | SCR-0043 |
| HTD-0048 | HIGH | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0048 |
| HTD-0049 | HIGH | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0049 |
| HTD-0063 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31` | Constructor default parameter instantiates a dependency. | SCR-0063 |
| HTD-0064 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33` | Constructor default parameter instantiates a dependency. | SCR-0064 |
| HTD-0065 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38` | Constructor default parameter instantiates a dependency. | SCR-0065 |
| HTD-0066 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27` | Constructor default parameter instantiates a dependency. | SCR-0066 |
| HTD-0067 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37` | Constructor default parameter instantiates a dependency. | SCR-0067 |
| HTD-0068 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39` | Constructor default parameter instantiates a dependency. | SCR-0068 |
| HTD-0069 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27` | Constructor default parameter instantiates a dependency. | SCR-0069 |
| HTD-0070 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36` | Constructor default parameter instantiates a dependency. | SCR-0070 |
| HTD-0071 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:40` | Constructor default parameter instantiates a dependency. | SCR-0071 |
| HTD-0072 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Operations/StoreCachedValue/StoreCachedValue.php:24` | Constructor default parameter instantiates a dependency. | SCR-0072 |
| HTD-0073 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:23` | Constructor default parameter instantiates a dependency. | SCR-0073 |
| HTD-0074 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Protection/ProtectCacheSource/AcquireCacheStampedeLock.php:30` | Constructor default parameter instantiates a dependency. | SCR-0074 |
| HTD-0075 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Lifecycle/EvictCachedValue/EvictCachedValue.php:18` | Constructor default parameter instantiates a dependency. | SCR-0075 |
| HTD-0076 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Lifecycle/WarmCache/WarmCache.php:21` | Constructor default parameter instantiates a dependency. | SCR-0076 |
| HTD-0077 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:26` | Constructor default parameter instantiates a dependency. | SCR-0077 |
| HTD-0078 | HIGH | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Flows/Lifecycle/RefreshCachedValue/RefreshCachedValue.php:29` | Constructor default parameter instantiates a dependency. | SCR-0078 |
| HTD-0097 | HIGH | `components/Application/Text` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Text/System/PublicSurface/Text.php:29,34,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (25 `new` expressions detected). | SCR-0097 |
| HTD-0100 | HIGH | `components/Application/Storage` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Storage/System/PublicSurface/Storage.php:37,56,65,84,93,101,109,117...` | PublicSurface directly instantiates collaborators (11 `new` expressions detected). | SCR-0100 |
| HTD-0117 | HIGH | `components/Application/Validation` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Validation/System/PublicSurface/Validation.php:18` | Constructor default parameter instantiates a dependency. | SCR-0117 |
| HTD-0119 | HIGH | `components/Application/Filesystem` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Filesystem/System/PublicSurface/Filesystem.php:39,44,49,54,59,64,69,74...` | PublicSurface directly instantiates collaborators (21 `new` expressions detected). | SCR-0119 |
| HTD-0121 | HIGH | `components/HTTP/ContentNegotiation` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php:23,30,44,45,46,47` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | SCR-0121 |
| HTD-0124 | HIGH | `components/HTTP/Client` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14` | Constructor default parameter instantiates a dependency. | SCR-0124 |
| HTD-0125 | HIGH | `components/HTTP/Client` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0125 |
| HTD-0126 | HIGH | `components/HTTP/Request` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/HTTP/Request/System/PublicSurface/Request.php:33` | Constructor default parameter instantiates a dependency. | SCR-0126 |
| HTD-0132 | HIGH | `components/HTTP/Request` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41` | Constructor default parameter instantiates a dependency. | SCR-0132 |
| HTD-0138 | HIGH | `components/HTTP/Session` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/HTTP/Session/System/PublicSurface/Session.php:30` | Constructor default parameter instantiates a dependency. | SCR-0138 |
| HTD-0139 | HIGH | `components/HTTP/Session` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/Session/System/PublicSurface/Session.php:30,91,92,129,130` | PublicSurface directly instantiates collaborators (5 `new` expressions detected). | SCR-0139 |
| HTD-0142 | HIGH | `components/HTTP` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/System/PublicSurface/Response.php:38,46,51` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0142 |
| HTD-0143 | HIGH | `components/HTTP/SecureRequest` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php:80,87,90,99,108,116` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | SCR-0143 |
| HTD-0144 | HIGH | `components/HTTP/Router` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/HTTP/Router/System/PublicSurface/Router.php:46` | Constructor default parameter instantiates a dependency. | SCR-0144 |
| HTD-0145 | HIGH | `components/HTTP/Router` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/HTTP/Router/System/PublicSurface/Router.php:46,49,107,117,130` | PublicSurface directly instantiates collaborators (5 `new` expressions detected). | SCR-0145 |
| HTD-0147 | HIGH | `components/Operations/Events` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Events/System/PublicSurface/Events.php:31` | Constructor default parameter instantiates a dependency. | SCR-0147 |
| HTD-0148 | HIGH | `components/Operations/Events` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Events/System/PublicSurface/Events.php:32` | Constructor default parameter instantiates a dependency. | SCR-0148 |
| HTD-0149 | HIGH | `components/Operations/Events` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0149 |
| HTD-0151 | HIGH | `components/Operations/Events` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36` | Constructor default parameter instantiates a dependency. | SCR-0151 |
| HTD-0152 | HIGH | `components/Operations/Events` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28` | Constructor default parameter instantiates a dependency. | SCR-0152 |
| HTD-0156 | HIGH | `components/Operations/Notifications` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Notifications/System/PublicSurface/Notifier.php:20` | Constructor default parameter instantiates a dependency. | SCR-0156 |
| HTD-0158 | HIGH | `components/Operations/Tasks` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Tasks/System/PublicSurface/Tasks.php:20,25,30,38,43,51,61` | PublicSurface directly instantiates collaborators (7 `new` expressions detected). | SCR-0158 |
| HTD-0163 | HIGH | `components/Operations/Realtime` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Realtime/System/PublicSurface/Realtime.php:22,31,51` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0163 |
| HTD-0164 | HIGH | `components/Operations/Delivery` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Delivery/System/PublicSurface/Delivery.php:16,21,26,31` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0164 |
| HTD-0168 | HIGH | `components/Operations/Concurrency` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Concurrency/System/PublicSurface/Concurrency.php:56,87,95` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0168 |
| HTD-0169 | HIGH | `components/Operations/Concurrency` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:22` | Constructor default parameter instantiates a dependency. | SCR-0169 |
| HTD-0170 | HIGH | `components/Operations/Concurrency` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:23` | Constructor default parameter instantiates a dependency. | SCR-0170 |
| HTD-0171 | HIGH | `components/Operations/Concurrency` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Concurrency/System/Capabilities/ChooseTaskRuntime/ChooseTaskRuntime.php:26` | Constructor default parameter instantiates a dependency. | SCR-0171 |
| HTD-0175 | HIGH | `components/Operations/Queue` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Queue/System/PublicSurface/Tasks.php:40` | Constructor default parameter instantiates a dependency. | SCR-0175 |
| HTD-0176 | HIGH | `components/Operations/Queue` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Queue/System/PublicSurface/Tasks.php:14,20,26,40` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0176 |
| HTD-0177 | HIGH | `components/Operations/Queue` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Queue/System/PublicSurface/TaskBatch.php:19` | Constructor default parameter instantiates a dependency. | SCR-0177 |
| HTD-0181 | HIGH | `components/Operations/MessageBus` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/MessageBus/System/PublicSurface/MessageBus.php:56,57,58,59` | PublicSurface directly instantiates collaborators (4 `new` expressions detected). | SCR-0181 |
| HTD-0182 | HIGH | `components/Operations/Scheduler` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Scheduler/System/PublicSurface/Scheduler.php:19,32,45` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0182 |
| HTD-0184 | HIGH | `components/Operations/BackgroundProcesses` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php:21,26,31,36,44,52,60,62...` | PublicSurface directly instantiates collaborators (10 `new` expressions detected). | SCR-0184 |
| HTD-0185 | HIGH | `components/Operations/BackgroundProcesses` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/BackgroundProcesses/System/Flows/MonitorBackgroundProcess/MonitorBackgroundProcess.php:13` | Constructor default parameter instantiates a dependency. | SCR-0185 |
| HTD-0186 | HIGH | `components/Operations/BackgroundProcesses` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/BackgroundProcesses/System/Flows/RestartBackgroundProcess/RestartBackgroundProcess.php:15` | Constructor default parameter instantiates a dependency. | SCR-0186 |
| HTD-0187 | HIGH | `components/Operations/BackgroundProcesses` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/BackgroundProcesses/System/Flows/RestartBackgroundProcess/RestartBackgroundProcess.php:16` | Constructor default parameter instantiates a dependency. | SCR-0187 |
| HTD-0188 | HIGH | `components/Operations/Observability` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Observability/System/PublicSurface/Observability.php:18,23,28,33,41,50` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | SCR-0188 |
| HTD-0190 | HIGH | `components/Operations/Mail` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/Mail/System/PublicSurface/Mailer.php:15` | Constructor default parameter instantiates a dependency. | SCR-0190 |
| HTD-0191 | HIGH | `components/Operations/Mail` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Mail/System/PublicSurface/Mailer.php:15,31,46,51,117` | PublicSurface directly instantiates collaborators (5 `new` expressions detected). | SCR-0191 |
| HTD-0195 | HIGH | `components/Operations/Filesystem` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/Filesystem/System/PublicSurface/Filesystem.php:18,23,28,33,38,46` | PublicSurface directly instantiates collaborators (6 `new` expressions detected). | SCR-0195 |
| HTD-0199 | HIGH | `components/Operations/RuntimeSupervision` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/RuntimeSupervision/System/Capabilities/Supervision/Supervisor.php:28` | Constructor default parameter instantiates a dependency. | SCR-0199 |
| HTD-0200 | HIGH | `components/Operations/MemoryLifecycle` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/MemoryLifecycle/System/PublicSurface/MemoryLifecycle.php:15,20,25` | PublicSurface directly instantiates collaborators (3 `new` expressions detected). | SCR-0200 |
| HTD-0203 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47` | Constructor default parameter instantiates a dependency. | SCR-0203 |
| HTD-0204 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47,53,54,55,74,78,98,116` | PublicSurface directly instantiates collaborators (8 `new` expressions detected). | SCR-0204 |
| HTD-0206 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:38` | Constructor default parameter instantiates a dependency. | SCR-0206 |
| HTD-0207 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:39` | Constructor default parameter instantiates a dependency. | SCR-0207 |
| HTD-0208 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:40` | Constructor default parameter instantiates a dependency. | SCR-0208 |
| HTD-0209 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:42` | Constructor default parameter instantiates a dependency. | SCR-0209 |
| HTD-0210 | HIGH | `components/Operations/ApplicationWorkflow` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:15` | Constructor default parameter instantiates a dependency. | SCR-0210 |

## MEDIUM Deviations

| Deviation ID | Severity | Unit | Governance source | Location | Observed gap | Related SCR |
|---|---|---|---|---|---|---|
| HTD-0001 | MEDIUM | `components/API/Contracts` | how-to-unit-test.md; how-to-design-components.md §4 | `components/API/Contracts` | No component-specific tests detected under tests/. | SCR-0001 |
| HTD-0002 | MEDIUM | `components/DeveloperTools/Documentation/Api` | how-to-unit-test.md; how-to-design-components.md §4 | `components/DeveloperTools/Documentation/Api` | No component-specific tests detected under tests/. | SCR-0002 |
| HTD-0003 | MEDIUM | `components/DeveloperTools/DumpDebugger` | how-to-unit-test.md; how-to-design-components.md §4 | `components/DeveloperTools/DumpDebugger` | No component-specific tests detected under tests/. | SCR-0003 |
| HTD-0004 | MEDIUM | `components/HTTP/Client` | how-to-unit-test.md; how-to-design-components.md §4 | `components/HTTP/Client` | No component-specific tests detected under tests/. | SCR-0004 |
| HTD-0005 | MEDIUM | `components/Identity/Security` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Identity/Security` | No component-specific tests detected under tests/. | SCR-0005 |
| HTD-0006 | MEDIUM | `components/Integration/ObjectStorage` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Integration/ObjectStorage` | No component-specific tests detected under tests/. | SCR-0006 |
| HTD-0007 | MEDIUM | `components/Operations/BackgroundProcesses` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Operations/BackgroundProcesses` | No component-specific tests detected under tests/. | SCR-0007 |
| HTD-0008 | MEDIUM | `components/Operations/Delivery` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Operations/Delivery` | No component-specific tests detected under tests/. | SCR-0008 |
| HTD-0009 | MEDIUM | `components/Operations/MemoryLifecycle` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Operations/MemoryLifecycle` | No component-specific tests detected under tests/. | SCR-0009 |
| HTD-0010 | MEDIUM | `components/Operations/Realtime` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Operations/Realtime` | No component-specific tests detected under tests/. | SCR-0010 |
| HTD-0011 | MEDIUM | `components/Operations/RuntimeSupervision` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Operations/RuntimeSupervision` | No component-specific tests detected under tests/. | SCR-0011 |
| HTD-0012 | MEDIUM | `components/Security/DataProtection` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Security/DataProtection` | No component-specific tests detected under tests/. | SCR-0012 |
| HTD-0013 | MEDIUM | `components/Security/Privacy` | how-to-unit-test.md; how-to-design-components.md §4 | `components/Security/Privacy` | No component-specific tests detected under tests/. | SCR-0013 |
| HTD-0014 | MEDIUM | `components/API/ApiBlueprint` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/API/ApiBlueprint` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0014 |
| HTD-0015 | MEDIUM | `components/API/Contracts` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/API/Contracts` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0015 |
| HTD-0018 | MEDIUM | `components/API/SchemaGeneration` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/API/SchemaGeneration` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0018 |
| HTD-0019 | MEDIUM | `components/Application/FeatureFlags` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/Application/FeatureFlags` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0019 |
| HTD-0020 | MEDIUM | `components/Application/Localization` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/Application/Localization` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0020 |
| HTD-0021 | MEDIUM | `components/CLI/Console` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/CLI/Console` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0021 |
| HTD-0022 | MEDIUM | `components/DataStack/Data` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DataStack/Data` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0022 |
| HTD-0024 | MEDIUM | `components/DataStack/Persistence` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DataStack/Persistence` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0024 |
| HTD-0025 | MEDIUM | `components/DeveloperTools/CodeGeneration` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DeveloperTools/CodeGeneration` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0025 |
| HTD-0026 | MEDIUM | `components/DeveloperTools/Diagnostics` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DeveloperTools/Diagnostics` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0026 |
| HTD-0027 | MEDIUM | `components/DeveloperTools/DumpDebugger` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DeveloperTools/DumpDebugger` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0027 |
| HTD-0028 | MEDIUM | `components/DeveloperTools/Dx` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DeveloperTools/Dx` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0028 |
| HTD-0029 | MEDIUM | `components/DeveloperTools/TestSupport` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/DeveloperTools/TestSupport` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0029 |
| HTD-0031 | MEDIUM | `components/HTTP/AfterResponse` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/AfterResponse` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0031 |
| HTD-0032 | MEDIUM | `components/HTTP/ContentNegotiation` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/ContentNegotiation` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0032 |
| HTD-0033 | MEDIUM | `components/HTTP/Context` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/Context` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0033 |
| HTD-0036 | MEDIUM | `components/HTTP/URI` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/HTTP/URI` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0036 |
| HTD-0038 | MEDIUM | `components/Operations/Filesystem` | how-to-dependency-injection.md §4.0; how-to-design-components.md §6.5 | `components/Operations/Filesystem` | ServiceProvider coverage gate reports real code but no component ServiceProvider. | SCR-0038 |
| HTD-0044 | MEDIUM | `framework/System/Capabilities/FailureBoundary` | how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php | `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116` | FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION. | SCR-0044 |
| HTD-0045 | MEDIUM | `components/DataStack/DataTransfer` | how-to-system-security.md §22; how-to-dogfooding.md Filesystem rule; check-raw-file-operations.php | `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php` | DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION. | SCR-0045 |
| HTD-0046 | MEDIUM | `components/Application/Cache` | how-to-dependency-injection.md §3.4, §6.9; how-to-code-review.md Critical Quality Signal Rule | `components/Application/Cache/System/Configuration/CacheConfiguration.php:21` | Constructor default parameter instantiates a dependency. | SCR-0046 |
| HTD-0047 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/AvaxCache.php:153` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | SCR-0047 |
| HTD-0050 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Read/ReadFromCache.php:32,50` | PublicSurface directly instantiates collaborators (2 `new` expressions detected). | SCR-0050 |
| HTD-0051 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php:21` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | SCR-0051 |
| HTD-0052 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php:29` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | SCR-0052 |
| HTD-0053 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Facade/CacheRegistry.php:33,47` | PublicSurface directly instantiates collaborators (2 `new` expressions detected). | SCR-0053 |
| HTD-0054 | MEDIUM | `components/Application/Cache` | how-to-design-components.md §6.2 PublicSurface Rule; how-to-dependency-injection.md §3.4 | `components/Application/Cache/System/PublicSurface/Facade/Cache.php:71` | PublicSurface directly instantiates collaborators (1 `new` expressions detected). | SCR-0054 |

## LOW Deviations

| Deviation ID | Severity | Unit | Governance source | Location | Observed gap | Related SCR |
|---|---|---|---|---|---|---|
| HTD-0428 | LOW | `components/Application/Cache` | how-to-code-review.md §21; how-to-clean-code.md module design | `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/CacheReplication.php` | Class/file is 316 lines (>300). | SCR-0428 |
| HTD-0438 | LOW | `components/Application/Container` | how-to-code-review.md §21; how-to-clean-code.md module design | `components/Application/Container/System/Foundation/DIContainer.php` | Class/file is 538 lines (>300). | SCR-0438 |
| HTD-0479 | LOW | `components/HTTP` | how-to-code-review.md §21; how-to-clean-code.md module design | `components/HTTP/System/Foundation/Values/HeaderName.php` | Class/file is 335 lines (>300). | SCR-0479 |
| HTD-0657 | LOW | `framework/System/Capabilities/PreCommit` | how-to-code-review.md §21; how-to-clean-code.md module design | `framework/System/Capabilities/PreCommit/Models/PreCommitResult.php` | Class/file is 381 lines (>300). | SCR-0657 |
| HTD-0665 | LOW | `CROSS_CUTTING` | how-to-document.md Semantic PHPDoc Rule; check-semantic-phpdoc.php | `components/ and framework/` | Semantic PHPDoc gate reports 9810 legacy ratchet violations, 0 touched/new blocking violations. | - |
| HTD-0667 | LOW | `CROSS_CUTTING` | AGENTS.md §19 validation unavailable command reporting | `tooling/governance/check-security-governance.php` | Requested security governance checker is not present; available security tools are under tooling/security/. | - |

## 13. Most Violated Rules

| Rule family | Count |
|---|---:|
| `how-to-code-review.md` | 479 |
| `how-to-dependency-injection.md` | 401 |
| `how-to-design-components.md` | 164 |
| `how-to-modern-php-attributes-di.md` | 161 |
| `how-to-clean-code.md` | 59 |
| `how-to-runtime-composition.md` | 23 |
| `how-to-unit-test.md` | 13 |
| `how-to-system-performance.md` | 7 |
| `how-to-coding-standards.md` | 4 |
| `how-to-dogfooding.md` | 3 |
| `how-to-system-security.md` | 3 |
| `how-to-document.md` | 1 |

## 14. Units With No Deviations

- `RUC-010` `components/Application/Facade` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-018` `components/CLI` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-034` `components/HTTP/ApiVersioning` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-039` `components/HTTP/Middleware` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-077` `components/Presentation/View` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-078` `components/Security` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUC-081` `components/Security/Hashing` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-001` `framework/System` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-002` `framework/System/Capabilities` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-004` `framework/System/Capabilities/ComponentManifest` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-005` `framework/System/Capabilities/ComponentRegistry` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-006` `framework/System/Capabilities/ConfigExplanation` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-007` `framework/System/Capabilities/ConfigValidation` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-008` `framework/System/Capabilities/Configuration` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-013` `framework/System/Capabilities/Health` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-014` `framework/System/Capabilities/HealthCheck` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-015` `framework/System/Capabilities/MetadataWarmup` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-017` `framework/System/Capabilities/Queue` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-018` `framework/System/Capabilities/RequestScope` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-020` `framework/System/Capabilities/ResponseNormalization` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-021` `framework/System/Capabilities/RouteIntelligence` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-022` `framework/System/Capabilities/Routing` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-024` `framework/System/Capabilities/RuntimeBoundary` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-027` `framework/System/Capabilities/RuntimeTimeline` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-030` `framework/System/Capabilities/StateReset` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-031` `framework/System/Capabilities/SystemDesign` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-032` `framework/System/Capabilities/TracingTimeline` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-033` `framework/System/Capabilities/WorkerManagement` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-034` `framework/System/Configuration` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-040` `framework/System/Configuration/LoadConfiguration` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-041` `framework/System/Configuration/RegisterComponents` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-042` `framework/System/Flows` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-043` `framework/System/Flows/AuditContainerScope` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-045` `framework/System/Flows/CheckRuntimeIsolation` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-047` `framework/System/Flows/DescribeDependency` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-048` `framework/System/Flows/DetectRouteConflict` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-049` `framework/System/Flows/DetectStateLeak` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-050` `framework/System/Flows/DiscoverComponents` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-051` `framework/System/Flows/ExplainConfig` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-052` `framework/System/Flows/ExplainContainerResolution` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-053` `framework/System/Flows/ExplainRouteMatch` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-054` `framework/System/Flows/HandleException` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-056` `framework/System/Flows/HandleRuntimeFailure` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-057` `framework/System/Flows/HandleWorkerRequest` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-058` `framework/System/Flows/InspectStaticState` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-059` `framework/System/Flows/ListComponents` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-060` `framework/System/Flows/ListContainerBindings` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-061` `framework/System/Flows/ListRoutes` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-062` `framework/System/Flows/RegisterHealthRoutes` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-063` `framework/System/Flows/ResetApplicationState` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-065` `framework/System/Flows/RunConsoleCommand` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-067` `framework/System/Flows/ShutdownRuntime` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-068` `framework/System/Flows/StartWorker` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-069` `framework/System/Flows/ValidateConfig` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-070` `framework/System/Flows/VerifyRequestScopeWasClosed` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-071` `framework/System/Flows/VerifyResetWasExecuted` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-072` `framework/System/Foundation` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-073` `framework/System/Foundation/Environment` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-074` `framework/System/Foundation/Exception` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-075` `framework/System/Foundation/Failure` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-076` `framework/System/Foundation/Paths` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-077` `framework/System/Foundation/Result` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-078` `framework/System/Foundation/Time` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-079` `framework/System/Foundation/Version` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-081` `framework/System/PublicSurface/Console` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-082` `framework/System/PublicSurface/Http` — no exact HTD finding in current evidence; not a blanket GREEN claim.
- `RUF-083` `framework/System/PublicSurface/Runtime` — no exact HTD finding in current evidence; not a blanket GREEN claim.

## 15. Units With Blocked/Unverifiable Rules

- No how-to file was unreadable. Semantic proof remains bounded by generated evidence and validation commands; scanner-only absence must not be used as GREEN proof.

## 16. Global How-To Compliance Decision

Decision: **BLOCKED_BY_HOW_TO**.
Reason: at least one BLOCKER how-to deviation remains and many HIGH deviations remain across DI/runtime/PublicSurface/component discipline.

## Reconciled Finding Addendum

- `HTD-0668` maps `DR-0039` from discipline remediation-plan/fix-this reconciliation into the how-to deviation set. It is HIGH because active broken reference semantics violate coding standards, ownership truth, and broken-reference gate expectations.


## Validation Command Summary

Sandbox note: initial sandbox attempts for `composer validate --no-check-publish`, `php tooling/refactor/check-component-suite-structure.php`, and `php tooling/refactor/check-direct-instantiation.php` failed with Docker socket permission denial. The same validation was rerun with approved escalation through the project wrapper.

| Command | Status | Evidence summary | Impact |
|---|---|---|---|
| `composer validate --no-check-publish` | PASS | `./composer.json is valid` | Composer metadata valid. |
| `composer dump-autoload -o` | PASS_WITH_WARNING | Generated optimized autoload files containing 9346 classes; warning: `framework/System/Foundation/compat.php` class `xhp_` skipped for PSR-4 mismatch. | Autoload generated; warning remains evidence, not GREEN proof. |
| `php tooling/refactor/check-component-suite-structure.php` | PASS | `PASS` | Structure gate passed. |
| `php tooling/refactor/check-duplicate-owners.php` | PASS | `PASS` | Duplicate owner gate passed. |
| `php tooling/refactor/check-namespace-drift.php` | PASS | `PASS` | Namespace drift gate passed. |
| `php tooling/refactor/check-public-surface.php` | PASS | `PASS` | Public surface scanner passed; semantic PublicSurface findings still remain from strict review. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | `PASS` | Runtime composition leak gate passed. |
| `php tooling/governance/check-governance-index-current.php` | PASS | `GREEN: Governance index is current.` | Governance index current. |
| `php tooling/governance/check-root-evidence-hygiene.php` | PASS | `GREEN: Root evidence hygiene PASSED.` | Root evidence hygiene passed. |
| `bash verify-governance.sh .` | PASS | `Governance Verified: FULL_GREEN_EXECUTABLE_GOVERNANCE_RUNTIME_READY`; script updated generated governance event/provenance files. | Governance harness passed; generated side effects were not staged. |
| `php tooling/refactor/check-direct-instantiation.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 761 output lines, constructor/default/fallback instantiation findings across framework/components. | Supports SCR/HTD DI/runtime findings. |
| `php tooling/refactor/check-constructor-bloat.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 460 output lines, CHECK/WARNING constructor arity findings including Runtime and Identity/Auth. | Supports complexity/DI findings. |
| `php tooling/refactor/check-service-provider-coverage.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 25 missing component ServiceProvider reports. | Supports ServiceProvider how-to deviations. |
| `php tooling/refactor/check-broken-reference-semantics.php` | FAIL_EXPECTED_FINDINGS | `FAIL`, 5 active broken references. | Supports broken reference strict/how-to findings, including DR-0039 reconciliation. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL_EXPECTED_FINDINGS | 3887 scanned, 107 findings, 1 BLOCKER (`AuthBuilder`), 106 REVIEW. | Supports large-unit and AuthBuilder BLOCKER findings. |

No production code, tests, composer files, autoload files, or `fix-this.md` were changed by this review pass. Validation failures above are the reviewed quality findings, not accidental remediation failures.
