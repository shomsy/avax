# fix-this.md

## Status

- Mode: Canonical Review Reconciliation Backlog
- Source: Discipline Review + Dual Review + Supplemental Strict Review
- Scope: components/ and framework/
- Last generated: 2026-05-20T06:30:00+02:00
- Current rule: Fix by smallest safe remediation batch
- Current global status: RED / BLOCKED_BY_HOW_TO / TARGETED_REDESIGN
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no fake GREEN
  - no remediation without validation and evidence

## Source Material

- `fix-this.md` — LEGACY; existing backlog.
- `.agents/management/evidence/generated/discipline-review/global-governance-heatmap.md` — CANONICAL; discipline review aggregate.
- `.agents/management/evidence/generated/discipline-review/remediation-plan.md` — CANONICAL; discipline remediation plan.
- `.agents/management/evidence/generated/discipline-review/components/*-review.md` — CANONICAL; component discipline reviews.
- `.agents/management/evidence/generated/discipline-review/framework/*-review.md` — CANONICAL; framework discipline reviews.
- `.agents/management/evidence/generated/dual-review/strict-code-review/global-strict-code-review-report.md` — CANONICAL; dual strict aggregate.
- `.agents/management/evidence/generated/dual-review/how-to-deviations/global-how-to-deviation-report.md` — CANONICAL; dual how-to aggregate.
- `.agents/management/evidence/generated/dual-review/cross-map-strict-vs-how-to.md` — CANONICAL; cross-map.
- `strict-code-review.md` — SUPPLEMENTAL; supplemental strict review.
- `CURRENT_TRUTH.md` — SUPPLEMENTAL; project state.

## How To Use This File

This is the active remediation backlog. Evidence files contain the detailed review and reconciliation proof. Every TODO links to source finding IDs through `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`. Each remediation batch requires its own 11++ prompt, must avoid unrelated component mixing, and must not be marked DONE without validation and evidence. Security/runtime P0 items override aesthetic cleanup.

## Severity Policy

- P0 BLOCKER: confirmed or strongly suspected security/runtime/public-entrypoint breakage that must be handled before broad cleanup.
- P1 HIGH: serious public surface, DI/runtime, security, or architecture risk needing focused remediation.
- P2 MEDIUM: maintainability, test proof, documentation, or ownership issues that are real but lower immediate risk.
- P3 LOW: low-risk cleanup that must not displace P0/P1.
- NEEDS_VERIFICATION: serious or supplemental findings needing direct proof before remediation or demotion.
- ACCEPTED_YELLOW: known debt with owner, risk, mitigation, expiry, and touched-file rule.
- ACCEPTED_EXCEPTION: only allowed with explicit governance exception evidence.

## Current Global Summary

- Component units reviewed: 85
- Framework units reviewed: 83
- Source finding counts by source:
  - existing root fix-this TODOs: 245
  - discipline review DR findings: 668
  - dual strict SCR findings: 665
  - dual how-to HTD findings: 668 (667 explicit IDs plus reconstructed HTD-0666 count-gap disposition)
  - supplemental SAI findings/patterns: 243
- Canonical clusters count: 29
- TODO count: 34
- P0 count: 7
- P1 count: 14
- P2 count: 9
- P3 count: 1
- NEEDS_VERIFICATION count: 0
- ACCEPTED_YELLOW count: 1
- Evidence paths:
  - `.agents/management/evidence/generated/review-reconciliation/source-inventory.md`
  - `.agents/management/evidence/generated/review-reconciliation/extracted-findings.md`
  - `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
  - `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
  - `.agents/management/evidence/generated/review-reconciliation/canonical-todo-model.md`
  - `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`

## Immediate P0 Queue

### TODO-001: Harden serialized payload boundaries before any cache/callable cleanup

- Status: DONE
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Merge commit: 36a8e3547
- Evidence: commit 36a8e3547 fix(security): harden serialized payload boundaries against object injection

### TODO-002: Fix compiled container namespace emission before container remediation

- Status: DONE
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Merge commit: ae0c5689b
- Evidence: commit ae0c5689b fix(container): correct compiled namespace emission

### TODO-003: Unify CSRF/session authority and remove direct session mutation conflicts

- Status: DONE
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Merge commit: c3abfc1bb
- Evidence: `.agents/management/evidence/generated/post-round-002-ready-merge/main-merge-validation.md`
- Source clusters: CLUSTER-003
- Source finding IDs: SAI-0057, SAI-0058, SAI-0059, SAI-0060, SAI-0061, SAI-0062, SAI-0063, SAI-0064, SAI-0084
- Root type: COMPONENT
- Unit(s): components/HTTP/Session; components/HTTP/Security; components/HTTP
- Affected files: SessionScope.php; NativeSessionStore.php; CsrfToken.php; CsrfTokens.php; CsrfTokenGenerator.php; HTTP security shortcuts
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Multiple classes independently start/mutate PHP sessions and use conflicting CSRF token keys (`_token`, `_csrf_token`, `_csrf_tokens`).
- Why it matters: Session/CSRF split authority can invalidate tokens, bypass lifecycle controls, and leak state in long-lived workers.
- Target state: One session authority owns lifecycle and one CSRF token model delegates through it.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: CSRF/session authority consolidation; no unrelated HTTP API expansion.
- Tests required: negative CSRF validation tests, token rotation/regeneration tests, session lifecycle tests, duplicate helper load test.
- Validation commands: `vendor/bin/phpunit --filter "Csrf|Session" --no-coverage && php tooling/refactor/check-runtime-composition-leaks.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-004: Close dynamic class-loading execution paths at payload and recovery boundaries

- Status: DONE
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Merge commit: 43c5e6883 (TODO-004) + 634b552e5 (TODO-004-b)
- Evidence: `.agents/management/evidence/generated/post-round-002-ready-merge/main-merge-validation.md`
- Source clusters: CLUSTER-006
- Source finding IDs: SAI-0013, SAI-0014, SAI-0038, SAI-0039, SAI-0040, SAI-0041, SAI-0042, SAI-0053, SAI-0066, SAI-0067, SAI-0068, SAI-0070, SAI-0071, SAI-0072, SAI-0073, SAI-0074, SAI-0079, SAI-0121, SAI-0125, SAI-0130, SAI-0131, SAI-0148, SAI-0153, SAI-0165, SAI-0238
- Root type: CROSS_CUTTING
- Unit(s): `DeveloperTools/TestSupport/Capabilities/ContractTesting/Verification/ContractVerifier.php`, `DeveloperTools/DumpDebugger`, `Container/Foundation/SimpleContainer.php`, `Container/Foundation/FrozenContainer.php`, `Container/Capabilities/ResolveCallable/ResolveCallable.php`, `Container/Capabilities/Declaration/Bindings/DependencyRegistry.php`, `Container/Capabilities/Declaration/Bindings/ServiceRegistry.php`, `Container/* (multiple files)`, ... (17 units total)
- Affected files: QueueWorker.php; RunRecoveryAction.php; RunFallbackAction.php; migration/seeder dynamic instantiation; Container class_exists+new sites
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Runtime class strings and `class_exists()+new` paths instantiate classes outside verified container/configuration ownership.
- Why it matters: Dynamic class loading from payload or policy strings is a security/runtime boundary until allowlisted and container-mediated.
- Target state: Class-string inputs are allowlisted, typed, container-resolved, and validated before execution.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Start with QueueWorker and FailureBoundary recovery/fallback paths; track container internals separately inside same cluster.
- Tests required: negative tests for unknown class, wrong interface, payload class injection, missing fallback/recovery class.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "QueueWorker|FailureBoundary|Migration|Seeder" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-005: Remove worker-unsafe static secret and security runtime state

- Status: DONE
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Merge commit: 3f55d597d
- Evidence: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`
- Source clusters: CLUSTER-004
- Source finding IDs: SCR-0146, SCR-0303, SCR-0304, SCR-0411, SCR-0412, SCR-0415, SCR-0664, HTD-0146, HTD-0303, HTD-0304, HTD-0411, HTD-0412, HTD-0415, HTD-0664, SAI-0090, SAI-0106, SAI-0135, SAI-0136, SAI-0137, SAI-0138, SAI-0140, SAI-0157, SAI-0170, SAI-0211, OLD-FIX-046, OLD-FIX-093, OLD-FIX-101, OLD-FIX-106, ... (31 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `components/Operations/Events`, `components/Security/Secrets`, `framework/System/Capabilities/RuntimeSafety`, `framework/System/Capabilities/ExternalState`, `Security/Secrets`, `Events`, `ExternalState`, `ResourceGovernance/PublicSurface/ResourceGovernor.php`, ... (11 units total)
- Affected files: Secrets.php; Diagnostics.php; FailureBoundary.php; ExternalState.php; ResourceGovernor.php; GlobalEventListenerState.php; related static runtime holders
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Security/runtime public surfaces and facades hold mutable static state, including secret store and request diagnostics.
- Why it matters: Static state can leak users, secrets, diagnostics, or runtime state across requests in long-lived workers.
- Target state: State is request-scoped or runtime-owned, reset proof is automatic, and security state is never lazy global mutable state.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Security/runtime static-state batch; first Secrets, Diagnostics, FailureBoundary.
- Tests required: long-lived worker two-request leakage tests, reset tests, secret overwrite/isolation tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Secrets|Diagnostics|FailureBoundary|ExternalState|StateReset" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-006: Move framework public entrypoint object-graph assembly out of runtime/PublicSurface

- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-007
- Source finding IDs: SCR-0388, SCR-0391, SCR-0393, SCR-0410, SCR-0420, HTD-0388, HTD-0391, HTD-0392, HTD-0393, HTD-0394, HTD-0410, HTD-0420, SAI-0167, SAI-0168, SAI-0169, SAI-0171, SAI-0172, SAI-0173, SAI-0174, SAI-0176, SAI-0177, SAI-0213, SAI-0214, SAI-0225, SAI-0226, SAI-0230, OLD-FIX-047, OLD-FIX-048, ... (34 total; full mapping in source-finding-coverage.md)
- Root type: FRAMEWORK
- Unit(s): framework/System/PublicSurface; framework/System/Flows/RunApplication; framework/System/Flows/CreateApplication
- Affected files: Avax.php; BootDsl.php; App.php; RunApplication.php; CreateApplication.php; BootDslEngine.php
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Framework entrypoints and flows create runtime object graphs with `new`, including request dispatch pipeline creation.
- Why it matters: Public entrypoints become hidden composition roots, weakening testability, worker safety, and DI discipline.
- Target state: Configuration/builders assemble; PublicSurface delegates; runtime flows receive ready collaborators.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Framework entrypoint composition extraction; no API behavior changes without explicit approval.
- Tests required: Avax::create, BootDsl::create, App::handle, RunApplication pipeline regression tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-007: Split AuthBuilder into bounded configuration responsibilities

- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-008
- Source finding IDs: SCR-0368, SCR-0602, HTD-0368, HTD-0602, SAI-0099, OLD-FIX-001, OLD-FIX-079
- Root type: COMPONENT
- Unit(s): components/Identity/Auth
- Affected files: components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: AuthBuilder is 797 lines and is the highest canonical non-security BLOCKER.
- Why it matters: A builder this large becomes a hidden container and makes auth assembly hard to verify safely.
- Target state: Auth assembly is divided into named configuration responsibilities with unchanged public behavior and focused tests.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: AuthBuilder split/classification only; no auth feature changes.
- Tests required: existing AuthBuilder behavior tests plus focused graph assembly tests.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && vendor/bin/phpunit --filter "AuthBuilder|Auth" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

## P1 Queue — HIGH

### TODO-008: Retire remaining static mutable PublicSurface/runtime state by ownership slice

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-005
- Source finding IDs: SAI-0001, SAI-0002, SAI-0027, SAI-0028, SAI-0029, SAI-0030, SAI-0031, SAI-0032, SAI-0033, SAI-0043, SAI-0054, SAI-0069, SAI-0100, SAI-0101, SAI-0102, SAI-0103, SAI-0104, SAI-0105, SAI-0107, SAI-0108, SAI-0128, SAI-0194, SAI-0236
- Root type: CROSS_CUTTING
- Unit(s): `DeveloperTools/Diagnostics`, `API/SchemaGeneration`, `Cache/PublicSurface/Cache.php`, `Cache/PublicSurface/CompiledCache.php`, `Container/PublicSurface/Container.php`, `Storage/PublicSurface/Storage.php`, `Pipeline/PublicSurface/Pipeline.php`, `Facade/Foundation/BaseFacade.php`, ... (21 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Non-security components and runtime helpers use static mutable/lazy singleton state.
- Why it matters: Manual reset patterns are fragile under workers.
- Target state: Request/runtime state is scoped and reset by owner, not shared static singletons.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Batch by owner group: Application, Operations, DataStack, framework capabilities.
- Tests required: two-request leak tests per touched owner.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-009: Reduce API and DeveloperTools PublicSurface construction pressure

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0225, SCR-0226, SCR-0231, SCR-0232, SCR-0233, SCR-0234, SCR-0235, SCR-0236, SCR-0238, SCR-0295, SCR-0317, SCR-0318, SCR-0321, SCR-0322, SCR-0327, SCR-0340, SCR-0341, SCR-0343, SCR-0345, HTD-0225, HTD-0226, HTD-0231, HTD-0232, HTD-0233, HTD-0234, HTD-0235, HTD-0236, HTD-0238, ... (81 total; full mapping in source-finding-coverage.md)
- Root type: COMPONENT
- Unit(s): components/API; components/DeveloperTools
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: API/DeveloperTools PublicSurface classes directly instantiate collaborators.
- Why it matters: Public API classes should receive/delegate and remain stable.
- Target state: Assembly moves to Configuration/ServiceProvider/builder with behavior preserved.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: API + DeveloperTools PublicSurface batch only.
- Tests required: public surface behavior tests for GraphQL/OpenAPI/Contracts/ApiBlueprint/Diagnostics.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-010: Reduce Application component PublicSurface construction pressure

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0047, SCR-0048, SCR-0049, SCR-0050, SCR-0051, SCR-0052, SCR-0053, SCR-0054, SCR-0097, SCR-0098, SCR-0099, SCR-0100, SCR-0102, SCR-0118, SCR-0119, HTD-0047, HTD-0048, HTD-0049, HTD-0050, HTD-0051, HTD-0052, HTD-0053, HTD-0054, HTD-0097, HTD-0098, HTD-0099, HTD-0100, HTD-0102, ... (41 total; full mapping in source-finding-coverage.md)
- Root type: COMPONENT
- Unit(s): components/Application/*
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Application components such as Cache, Storage, Filesystem, Validation, Text construct collaborators from PublicSurface.
- Why it matters: Application facades become hidden composition roots and hard to test.
- Target state: Configuration owns assembly; PublicSurface receives/delegates.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Split into Cache first, then Storage/Filesystem/Text/Validation.
- Tests required: focused public behavior tests per touched component.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-011: Reduce HTTP PublicSurface construction pressure

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0121, SCR-0122, SCR-0123, SCR-0125, SCR-0127, SCR-0134, SCR-0139, SCR-0141, SCR-0142, SCR-0143, SCR-0145, HTD-0121, HTD-0122, HTD-0123, HTD-0125, HTD-0127, HTD-0134, HTD-0139, HTD-0141, HTD-0142, HTD-0143, HTD-0145, OLD-FIX-017, OLD-FIX-018, OLD-FIX-019, OLD-FIX-020, OLD-FIX-021, OLD-FIX-022, ... (36 total; full mapping in source-finding-coverage.md)
- Root type: COMPONENT
- Unit(s): components/HTTP/*
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: HTTP public surfaces directly construct request/client/session/router/response collaborators.
- Why it matters: HTTP is runtime-sensitive and public API construction hides failure modes.
- Target state: HTTP assembly is explicit and testable outside public entrypoints.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: HTTP Client/Session/Router/Request/Response/SecureRequest by safe slice.
- Tests required: HTTP public behavior and negative boundary tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php && vendor/bin/phpunit --filter "HTTP|Http" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-012: Reduce Operations PublicSurface construction pressure

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0149, SCR-0150, SCR-0157, SCR-0158, SCR-0159, SCR-0163, SCR-0164, SCR-0168, SCR-0173, SCR-0174, SCR-0176, SCR-0178, SCR-0181, SCR-0182, SCR-0183, SCR-0184, SCR-0188, SCR-0189, SCR-0191, SCR-0192, SCR-0194, SCR-0195, SCR-0197, SCR-0198, SCR-0200, SCR-0202, SCR-0204, SCR-0205, ... (76 total; full mapping in source-finding-coverage.md)
- Root type: COMPONENT
- Unit(s): components/Operations/*
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Operations public surfaces construct tasks, queue, events, scheduler, observability, filesystem, and runtime collaborators.
- Why it matters: Operations components are worker/runtime-sensitive; hidden assembly raises lifecycle risk.
- Target state: Operations assembly flows through configuration and explicit runtimes.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Queue/Events/Scheduler first, then remaining Operations public surfaces.
- Tests required: queue/events/scheduler/observability behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-013: Reduce Security/Identity/DataStack PublicSurface construction pressure

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0239, SCR-0240, SCR-0241, SCR-0242, SCR-0243, SCR-0244, SCR-0245, SCR-0246, SCR-0247, SCR-0248, SCR-0249, SCR-0250, SCR-0251, SCR-0265, SCR-0266, SCR-0267, SCR-0272, SCR-0273, SCR-0286, SCR-0296, SCR-0305, SCR-0306, SCR-0311, SCR-0356, SCR-0357, SCR-0377, SCR-0381, SCR-0387, ... (68 total; full mapping in source-finding-coverage.md)
- Root type: COMPONENT
- Unit(s): components/Security; components/Identity; components/DataStack
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Security/Identity/DataStack public surfaces and data boundaries construct collaborators internally.
- Why it matters: Security and data boundaries need explicit composition and negative tests.
- Target state: No security/data public surface creates mutable collaborator graphs.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Security Redaction/Secrets first, then Identity/DataStack.
- Tests required: negative security/data behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-014: Move constructor default dependency creation into approved configuration owners

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-010
- Source finding IDs: SCR-0046, SCR-0055, SCR-0057, SCR-0058, SCR-0059, SCR-0061, SCR-0062, SCR-0063, SCR-0064, SCR-0065, SCR-0066, SCR-0067, SCR-0068, SCR-0069, SCR-0070, SCR-0071, SCR-0072, SCR-0073, SCR-0074, SCR-0075, SCR-0076, SCR-0077, SCR-0078, SCR-0080, SCR-0081, SCR-0082, SCR-0083, SCR-0085, ... (529 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `components/Operations/RuntimeSupervision`, `components/Operations/MemoryLifecycle`, `components/DeveloperTools/Documentation/Api`, `components/Identity/ExternalIdentity`, `components/HTTP/URI`, `components/Application/Filesystem`, `components/API/OpenAPI`, `components/Application/Config`, ... (53 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Many constructors create dependencies in default parameters or hidden fallbacks.
- Why it matters: Default construction weakens DI, hides failure behavior, and complicates tests.
- Target state: Required dependencies fail fast or are assembled explicitly in Configuration.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Per component owner; do not sweep unrelated files.
- Tests required: focused constructor/assembly tests for each touched owner.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-015: Add missing ServiceProvider assembly owners without creating new behavior

- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-011
- Source finding IDs: SCR-0014, SCR-0015, SCR-0016, SCR-0017, SCR-0018, SCR-0019, SCR-0020, SCR-0021, SCR-0022, SCR-0023, SCR-0024, SCR-0025, SCR-0026, SCR-0027, SCR-0028, SCR-0029, SCR-0030, SCR-0031, SCR-0032, SCR-0033, SCR-0034, SCR-0035, SCR-0036, SCR-0037, SCR-0038, HTD-0014, HTD-0015, HTD-0016, ... (75 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `components/HTTP/Dispatcher`, `components/HTTP/URI`, `components/API/OpenAPI`, `components/Application/FeatureFlags`, `components/DeveloperTools/Dx`, `components/HTTP/Context`, `components/CLI/Console`, `components/HTTP/AfterResponse`, ... (25 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Real components are reported without ServiceProvider coverage.
- Why it matters: Components with real code need an explicit assembly owner.
- Target state: Each affected component has precise System/Configuration ServiceProvider registration.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: ServiceProvider coverage batch by component group.
- Tests required: registration/boot tests per provider.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php && vendor/bin/phpunit --filter "ServiceProvider|Provider" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-016: Fix broken reference semantics in public/runtime namespaces

- Status: DONE
- Priority: P1 HIGH
- Normalized severity: HIGH
- Merge commit: 6718fa716
- Evidence: `.agents/management/evidence/generated/post-round-002-ready-merge/main-merge-validation.md`
- Source clusters: CLUSTER-012
- Source finding IDs: SCR-0039, SCR-0040, SCR-0041, SCR-0042, SCR-0665, HTD-0039, HTD-0040, HTD-0041, HTD-0042, HTD-0668, OLD-FIX-023, OLD-FIX-025
- Root type: CROSS_CUTTING
- Unit(s): `components/Operations/ApplicationWorkflow`, `components/HTTP`, `components/HTTP/System`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Broken references remain in HTTP and Operations/ApplicationWorkflow evidence.
- Why it matters: Broken public/runtime references become autoload/runtime failures.
- Target state: All referenced classes resolve or references are removed with compatible behavior.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Broken reference semantic batch only.
- Tests required: autoload/reference regression tests for affected units.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php && composer dump-autoload -o`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-017: Route raw filesystem and path operations through approved first-party boundaries

- Status: DONE
- Priority: P1 HIGH
- Normalized severity: HIGH
- Merge commit: 182074351
- Evidence: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`
- Source clusters: CLUSTER-013
- Source finding IDs: SCR-0043, SCR-0044, SCR-0045, HTD-0043, HTD-0044, HTD-0045, SAI-0141, SAI-0142, SAI-0160, OLD-FIX-002, OLD-FIX-118, OLD-FIX-121
- Root type: CROSS_CUTTING
- Unit(s): `components/DataStack/DataTransfer`, `framework/System/Configuration/Builders`, `framework/System/Capabilities/FailureBoundary`, `Doctor/CheckAutoload.php`, `Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php`, `Doctor/*.php`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Raw filesystem checks/path traversal appear in framework route/doctor/runtime areas.
- Why it matters: Dogfooding and security rules require one owner for filesystem/path behavior.
- Target state: Approved Filesystem/Path capabilities own file checks and path decisions.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Start with route dispatch builder raw `is_file()`, then Doctor/runtime path checks.
- Tests required: path failure/negative tests.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php || true; php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-018: Harden security logging, redaction, and secret parameter handling

- Status: DONE
- Priority: P1 HIGH
- Normalized severity: HIGH
- Merge commit: 40b954daf
- Evidence: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`
- Source clusters: CLUSTER-019, CLUSTER-016
- Source finding IDs: SAI-0093, SAI-0097, SAI-0098, SAI-0122, SAI-0124, SAI-0126, SAI-0144, SAI-0145, SAI-0158
- Root type: CROSS_CUTTING
- Unit(s): `tests/Unit/Components/Security/Cryptography/CryptographyTest.php`, `CryptographyTest`, `SecretsCapabilitiesTest`, `Logging`, `Security/RequestSigning/VerifyInternalRequestSignature.php`, `Security/RequestSigning/SignInternalRequest.php`, `VerifyInternalRequestSignature.php, SignInternalRequest.php`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Findings include unredacted logging, missing `#[SensitiveParameter]`, and missing negative security tests.
- Why it matters: Secrets must not leak through logs, stack traces, or weak tests.
- Target state: Sensitive inputs are annotated/redacted and abuse cases are tested.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Logging/redaction/signature/crypto security hardening only.
- Tests required: negative tests for secret leakage, tampered ciphertext, invalid key, nested redaction.
- Validation commands: `vendor/bin/phpunit --filter "Redaction|Secrets|Cryptography|RequestSignature|Logging" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-019: Replace global helper service-locator shortcuts with testable boundaries

- Status: DONE
- Priority: P1 HIGH
- Normalized severity: HIGH
- Merge commit: c79c4df0b
- Evidence: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`
- Source clusters: CLUSTER-027
- Source finding IDs: SCR-0435, HTD-0435, SAI-0065, SAI-0080
- Root type: CROSS_CUTTING
- Unit(s): `components/Application/Text`, `All shortcuts.php (13 files)`, `HTTP/Security`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Shortcut files call `app()` at runtime and duplicate helper names.
- Why it matters: Global service locators hide dependencies and are hard to isolate in tests.
- Target state: Helpers are thin compatibility shims over explicit public boundaries or are documented as temporary.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: HTTP/security shortcuts first because of CSRF duplication.
- Tests required: helper load-order and behavior tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "shortcuts|csrf" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

## P2 Queue — MEDIUM

### TODO-020: Classify and reduce constructor bloat by owner

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-014, CLUSTER-015
- Source finding IDs: SCR-0421, SCR-0422, SCR-0424, SCR-0425, SCR-0427, SCR-0428, SCR-0429, SCR-0430, SCR-0431, SCR-0432, SCR-0433, SCR-0436, SCR-0437, SCR-0438, SCR-0439, SCR-0440, SCR-0441, SCR-0442, SCR-0443, SCR-0444, SCR-0445, SCR-0446, SCR-0447, SCR-0448, SCR-0449, SCR-0450, SCR-0453, SCR-0454, ... (483 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `components/Operations/RuntimeSupervision`, `components/Application/DateTime`, `components/Identity/ExternalIdentity`, `components/Operations/MessageBus`, `components/Operations/ApplicationWorkflow`, `components/HTTP/Client`, `components/Operations/Mail`, `components/API/GraphQL`, ... (41 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Large constructors and large classes exceed governance thresholds.
- Why it matters: High arity and large files hide multiple responsibilities.
- Target state: Each large unit is split, justified, or accepted with owner/expiry.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Identity/Auth and DataStack first after P0, then remaining large-unit report.
- Tests required: behavior-preserving tests for each touched split.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php && php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-021: Add missing behavior proof and negative tests by risk slice

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-028, CLUSTER-016
- Source finding IDs: SCR-0001, SCR-0003, SCR-0004, SCR-0005, SCR-0006, SCR-0007, SCR-0008, SCR-0009, SCR-0010, SCR-0011, SCR-0012, SCR-0013, HTD-0001, HTD-0003, HTD-0004, HTD-0005, HTD-0006, HTD-0007, HTD-0008, HTD-0009, HTD-0010, HTD-0011, HTD-0012, HTD-0013, SAI-0241, OLD-FIX-169, OLD-FIX-199, OLD-FIX-206, ... (37 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `components/Operations/RuntimeSupervision`, `components/Operations/MemoryLifecycle`, `components/HTTP/Client`, `components/API/Contracts`, `components/Security/Privacy`, `components/Identity/Security`, `components/Security/DataProtection`, `components/Operations/BackgroundProcesses`, ... (17 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Some units lack component-specific tests or have only weak/smoke proof.
- Why it matters: Cleanup without behavior proof is unsafe.
- Target state: Public behavior, failure modes, and security boundaries have focused tests.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Security/runtime first, then components with no detected tests.
- Tests required: new focused tests only for touched behavior.
- Validation commands: `vendor/bin/phpunit --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-022: Resolve forbidden concept folder names through approved governance decisions

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-017
- Source finding IDs: SAI-0010, SAI-0011, SAI-0075, SAI-0092, SAI-0123, SAI-0132, SAI-0154, SAI-0199, SAI-0240
- Root type: CROSS_CUTTING
- Unit(s): `components/API/Contracts/`, `components/DeveloperTools/Diagnostics/`, `components/HTTP/Security/`, `components/Identity/Security/`, `components/Operations/Events/`, `framework`, `Security/ directory`, `CROSS_CUTTING`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Folders such as Contracts, Diagnostics, Security, Events are flagged as concept-word names.
- Why it matters: AvaX folders must say flow/capability, and exceptions require explicit governance.
- Target state: Each flagged name is renamed by approved slice or documented as an exception with expiry.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Verification and decision doc first; no mechanical renames in same batch.
- Tests required: namespace/autoload checks after any approved rename.
- Validation commands: `php tooling/refactor/check-component-suite-structure.php && php tooling/refactor/check-namespace-drift.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-023: Collapse duplicate ownership and duplicate class implementations

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-018
- Source finding IDs: SAI-0021, SAI-0026, SAI-0133, SAI-0155, SAI-0156, SAI-0210, SAI-0242
- Root type: CROSS_CUTTING
- Unit(s): `Testing.php, ContractTesting.php`, `Container`, `ResourceGovernance/PublicSurface/ResourceGovernor.php + ResourceGovernance`, `ResourceGovernance/`, `Runtime/GracefulShutdown/`, `framework`, `CROSS_CUTTING`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Duplicate/near-duplicate classes and functions are reported across container, runtime, logging, CSRF, ResourceGovernance, and GracefulShutdown.
- Why it matters: Duplicate owners drift and make public/runtime behavior ambiguous.
- Target state: One owner per behavior, with compatibility handled explicitly.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Verify duplicates, choose owner, then remove or bridge one pair at a time.
- Tests required: regression tests for selected owner and compatibility bridge.
- Validation commands: `php tooling/refactor/check-duplicate-owners.php && php tooling/refactor/check-namespace-drift.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-024: Replace hidden superglobal/env/IO access in runtime flows

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-020
- Source finding IDs: SAI-0016, SAI-0017, SAI-0018, SAI-0082, SAI-0086, SAI-0139, SAI-0159, SAI-0161, SAI-0183, SAI-0190, SAI-0224
- Root type: CROSS_CUTTING
- Unit(s): `DeveloperTools/Diagnostics`, `DeveloperTools/CodeGeneration/Capabilities/Generators/CodeGenerator.php`, `DeveloperTools/DumpDebugger/Capabilities/Formatters/VariableFormatter.php`, `HTTP/AfterResponse`, `HTTP`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php`, `GracefulShutdown/.../ShutdownSequence.php`, `PreCommit/PreCommitValidator.php`, ... (10 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Runtime code reads `$_SERVER`, `$_SESSION`, env vars, STDERR, headers/echo, or exits directly.
- Why it matters: Hidden I/O weakens portability and long-lived runtime safety.
- Target state: Runtime context, request objects, or configured capabilities provide all environment and I/O access.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: ReportRuntimeFailure/ExternalState/Doctor/PreCommit/SendResponse by slice.
- Tests required: runtime context and CLI/non-CLI tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Runtime|Doctor|PreCommit|SendResponse" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-025: Make error handling explicit where catch-and-continue hides failures

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-024
- Source finding IDs: SAI-0012, SAI-0047, SAI-0049, SAI-0050, SAI-0083, SAI-0149, SAI-0166, SAI-0197
- Root type: CROSS_CUTTING
- Unit(s): `API/ApiBlueprint`, `FeatureFlags/PublicSurface/FeatureFlags.php`, `Config`, `Text`, `HTTP`, `PreCommit/PreCommit.php`, `PreCommit.php`, `framework`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Several flows catch broad exceptions and continue/log without structured failure behavior.
- Why it matters: Silent failure obscures production incidents and can weaken security boundaries.
- Target state: Errors are fail-closed, reported through approved channels, or explicitly documented.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Rpc/AfterResponse/PreCommit/Cryptography fallback paths by slice.
- Tests required: failure-mode tests proving expected behavior.
- Validation commands: `vendor/bin/phpunit --filter "Failure|Exception|AfterResponse|PreCommit|Cryptography" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-027: Document public interface contracts and failure modes

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-022
- Source finding IDs: SCR-0002, HTD-0002, SAI-0019, SAI-0178, SAI-0179, SAI-0180, SAI-0181, SAI-0215, SAI-0216, SAI-0217, SAI-0243, OLD-FIX-197
- Root type: CROSS_CUTTING
- Unit(s): `components/DeveloperTools/Documentation/Api`, `Multiple files`, `framework`, `HttpKernelInterface.php`, `RuntimeKernelInterface.php`, `CROSS_CUTTING`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Public/kernel interfaces and some contracts lack semantic PHPDoc and `@throws` documentation.
- Why it matters: Public APIs need stable semantics and failure contracts before cleanup can be trusted.
- Target state: Interfaces explain intent, returns, invariants, and failure modes.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Framework kernel/public interfaces first, then security/runtime public contracts.
- Tests required: documentation gate plus existing interface tests.
- Validation commands: `php tooling/governance/check-semantic-phpdoc.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-028: Replace empty stubs/no-op methods with explicit behavior or explicit unsupported failure

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-023
- Source finding IDs: SAI-0020, SAI-0191, SAI-0192, SAI-0193, SAI-0196, SAI-0227, SAI-0228, SAI-0229
- Root type: CROSS_CUTTING
- Unit(s): `API/DeveloperTools/`, `framework`, `ResetApplicationState.php`, `ShutdownRuntime.php`, `ConfigureRuntime.php`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Empty classes and no-op methods hide incomplete runtime behavior.
- Why it matters: Silent no-ops create false readiness and make tests lie.
- Target state: Every incomplete behavior either does real work or fails explicitly with documented status.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: ResetApplicationState, ShutdownRuntime, ConfigureRuntime, empty exception markers by slice.
- Tests required: tests proving explicit behavior/failure.
- Validation commands: `vendor/bin/phpunit --filter "ResetApplicationState|ShutdownRuntime|ConfigureRuntime" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

### TODO-029: Measure and reduce DI/container object graph performance pressure

- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-026
- Source finding IDs: See source-finding-coverage.md
- Root type: CROSS_CUTTING
- Unit(s): multiple
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: DIContainer and related runtime paths create repeated short-lived object graphs and serialize large dependency graphs.
- Why it matters: Hot-path container churn can become worker memory/latency risk.
- Target state: Object creation is bounded or moved to compile/boot paths with measurements.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Measure first; change only after benchmark/test proof.
- Tests required: focused microbenchmark or existing performance gate plus behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

## P3 Queue — LOW

### TODO-030: Close low-risk compat/version/style cleanup with evidence

- Status: OPEN
- Priority: P3 LOW
- Normalized severity: LOW
- Source clusters: CLUSTER-025, CLUSTER-031
- Source finding IDs: SAI-0127, SAI-0200, SAI-0234, SAI-0235
- Root type: CROSS_CUTTING
- Unit(s): `ApplicationWorkflow`, `framework`, `compat.php`, `AvaxVersion.php`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Low-severity findings cover compat expiry, version string confusion, IDE annotations, optional extension fallback, and governance proof hygiene.
- Why it matters: Low-noise cleanup prevents future confusion but must not displace P0/P1.
- Target state: Each low item is either fixed, documented with expiry, or archived as obsolete.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Low-risk docs/style cleanup only after P0/P1.
- Tests required: focused lint/governance checks only.
- Validation commands: `composer validate --no-check-publish && php tooling/governance/check-root-evidence-hygiene.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

## Needs Verification

### TODO-026: Verify SQL/CSV injection and table-name interpolation findings before remediation (VERIFIED)

- Status: VERIFIED
- Priority: P1
- Normalized severity: HIGH
- Source clusters: CLUSTER-021
- Source finding IDs: SAI-0076, SAI-0077, SAI-0078, SAI-0085, SAI-0087
- Root type: CROSS_CUTTING
- Unit(s): `HTTP/Session`, `DataStack/Database`, `DataStack/Persistence`, `HTTP/ContentNegotiation`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Supplemental findings identify SQL/table interpolation and CSV injection risks, but exploitability depends on validation/quoting paths.
- Why it matters: Security findings must not be downgraded without proof, but remediation needs exact threat shape.
- Target state: Each suspected injection surface is classified confirmed P1, false-positive candidate, or accepted exception with proof.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Verification-only search/test batch; no code remediation.
- Tests required: targeted negative tests only after confirming exploitability.
- Validation commands: `rg -n "sprintf|CSV|fputcsv|wrap\(|table" components/DataStack components/HTTP && vendor/bin/phpunit --filter "Grammar|SessionStore|CsvFormat" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Verification evidence: `.agents/management/evidence/generated/todo-026-sql-csv-verification/`
- Verification commit: 0954ef171
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: fix-this.md reflects verified disposition with sub-TODO split, evidence files committed, and each confirmed finding has an owning remediation TODO.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

- Verification disposition:
  - SAI-0085 (CSV formula injection) → **CONFIRMED P1** → see TODO-026a below
  - VER-001 (CompileDataQuery identifier interpolation) → **CONFIRMED P1** → see TODO-026b below
  - SAI-0076/0077/0078 (Grammar sprintf with wrap() mitigation) → **PARTIAL MEDIUM** — upstream caller audit needed, no code change yet
  - VER-002 (DatabaseSessionStore table name sprintf) → **ACCEPTED_EXCEPTION** — constructor-injected, validated against `/^[a-zA-Z_]\w*$/`, no user-controlled input path
  - SAI-0087 (IDE noinspection annotations) → **P3 LOW** — merge into TODO-030

### TODO-026a: Escape CSV formula injection cells before rendering

- Status: OPEN
- Priority: P1
- Normalized severity: HIGH
- Source finding IDs: SAI-0085
- Root type: SECURITY
- Unit(s): `HTTP/ContentNegotiation`
- Affected files: `components/HTTP/ContentNegotiation/System/Capabilities/Formats/CsvFormat.php`, `components/HTTP/ContentNegotiation/System/PublicSurface/CsvFormatter.php`
- Rule sources: how-to-system-security.md
- Problem: CsvFormat.php and CsvFormatter.php use `fputcsv()` with `escape='\\'` but do not prefix cells starting with `=`, `+`, `-`, `@`, or tab characters. Values like `=cmd|'/C calc'!A0` execute as formulas when opened in Excel/LibreOffice.
- Why it matters: CSV formula injection is a confirmed attack vector (OWASP CSV Injection). Any user-supplied data exported as CSV can trigger arbitrary formula execution.
- Target state: All cell values starting with `=`, `+`, `-`, `@`, or `\t` are prefixed with a neutral character before fputcsv. Negative tests prove formula characters are escaped.
- Safe remediation batch: One security hardening batch limited to CSV output formatting.
- Tests required: negative tests for `=`, `+`, `-`, `@`, `\t` prefix characters.
- Validation commands: `vendor/bin/phpunit --filter "CsvFormat|CsvFormatter|CsvInjection" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- Done when: CSV formula prefix escaping implemented; negative tests pass; validation GREEN.
- Owner: AvaX maintainer
- Expiry/Target: next remediation batch

### TODO-026b: Sanitize identifier interpolation in CompileDataQuery SQL compilation

- Status: OPEN
- Priority: P1
- Normalized severity: HIGH
- Source finding IDs: VER-001
- Root type: SECURITY
- Unit(s): `DataStack/Persistence`
- Affected files: `components/DataStack/Persistence/System/Flows/CompileDataQuery/CompileDataQuery.php`
- Rule sources: how-to-system-security.md
- Problem: CompileDataQuery::buildSql() interpolates `$select` columns, `$join['table']`, `$join['on']`, `$orderBy` field/direction, and `$condition['field']` directly into SQL strings via sprintf/concat without any identifier wrapping, validation, or allowlist. If DataQuery fields originate from user input, this is SQL injection.
- Why it matters: Unlike the base Grammar class which uses `wrap()` for all identifiers, CompileDataQuery has zero identifier protection. More direct SQL injection vector than the Grammar sprintf findings.
- Target state: All identifiers in CompileDataQuery are either: (a) routed through Grammar::wrap(), (b) validated against a strict allowlist pattern `/^[a-zA-Z_]\w*$/`, or (c) documented as internal-only with explicit trust boundaries.
- Safe remediation batch: One security hardening batch limited to CompileDataQuery identifier handling.
- Tests required: negative tests prove malicious identifiers are rejected or escaped.
- Validation commands: `vendor/bin/phpunit --filter "CompileDataQuery|SqlInjection" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/todo-026-sql-csv-verification/confirmed-findings.md`
- Done when: Identifier sanitization implemented; negative tests pass; validation GREEN.
- Owner: AvaX maintainer
- Expiry/Target: next remediation batch

### TODO-031: Verify supplemental-only scan claims before promoting or dropping them (VERIFIED_WITH_MAPPINGS)

- Status: VERIFIED_WITH_MAPPINGS
- Priority: P2
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-030, CLUSTER-032
- Source finding IDs: DR-0667, DR-0668, SCR-0056, SCR-0060, SCR-0103, SCR-0105, SCR-0107, SCR-0108, SCR-0109, SCR-0110, SCR-0114, SCR-0116, SCR-0262, SCR-0359, SCR-0361, SCR-0362, SCR-0363, SCR-0364, SCR-0365, SCR-0366, SCR-0367, SCR-0392, SCR-0394, SCR-0423, SCR-0434, SCR-0460, SCR-0467, SCR-0476, ... (141 total; full mapping in source-finding-coverage.md)
- Root type: CROSS_CUTTING
- Unit(s): `tooling/governance/check-serviceprovider-governance-consistency.php output`, `tooling/governance/check-security-governance.php`, `components/Application/Config`, `components/Application/Pipeline`, `components/Operations/ApplicationWorkflow`, `components/CLI/Console`, `components/API/GraphQL`, `components/HTTP/Router`, ... (56 units total)
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Some supplemental strict-review claims were scan-only or explicitly listed as known gaps, including zero-test claims and clean-unit claims.
- Why it matters: Review discipline forbids false positives and fake clean status.
- Target state: Every supplemental-only claim is confirmed, demoted to false-positive candidate with evidence, or merged into an active TODO.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Verification-only batch.
- Tests required: none unless verification creates confirmed remediation TODO.
- Validation commands: `rg -n "<claim-specific pattern>" components framework tests && find tests -type f | sort`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`
- Verification evidence: `.agents/management/evidence/generated/todo-031-supplemental-claims-verification/`
- Verification commit: 3619e7e8a
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: fix-this.md reflects VERIFIED_WITH_MAPPINGS status with mapping summary, evidence files committed, and each target TODO remediation re-scans its affected files.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred

- Verification result: All 141 mapped source IDs verified. No new P0/P1/P2 findings discovered. Confirmed claims map to existing active TODOs as follows:
  - Runtime class loading / dynamic instantiation → TODO-004
  - Framework public entrypoint composition → TODO-006
  - Component PublicSurface construction → TODO-009 through TODO-013
  - Constructor default parameter instantiation → TODO-014
  - ServiceProvider assembly gaps → TODO-015
  - Global helper service-locator shortcuts → TODO-019
  - Constructor bloat / large units → TODO-020
  - Missing or weak behavior test proof → TODO-021
  - Forbidden concept folder names → TODO-022
  - Duplicate ownership / duplicate classes → TODO-023
  - Hidden superglobal/env/IO access → TODO-024
  - DR-0667 (ServiceProvider governance wording gap) → TODO-015
  - DR-0668 (security governance tool missing) → governance index PLANNED/NOT IMPLEMENTED
  - Unmapped IDs (95+ individual DR/SCR/SAI/HTD without individual evidence files) remain covered by their aggregate cluster definitions (CLUSTER-009 through CLUSTER-024) and will be re-scanned during remediation of their target TODOs. These are unverifiable source mappings with aggregate claim coverage, not false positives.

## Accepted YELLOW

### TODO-032: Maintain semantic PHPDoc legacy ratchet while cleaning touched files

- Status: ACCEPTED_YELLOW
- Priority: ACCEPTED_YELLOW
- Normalized severity: ACCEPTED_YELLOW
- Source clusters: CLUSTER-029
- Source finding IDs: DR-0666
- Root type: CROSS_CUTTING
- Unit(s): `components/ and framework/`
- Affected files: multiple
- Rule sources: see source finding IDs in `source-finding-coverage.md` and cluster details in `finding-clusters.md`.
- Problem: Semantic PHPDoc gate reports 9810 legacy ratchet violations with 0 touched/new blockers in discipline evidence.
- Why it matters: The debt is broad and should not block every cleanup, but touched public/runtime/security files must not add noise.
- Target state: Ratchet remains accepted yellow with owner, risk, expiry, and touched-file cleanup rule.
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no unrelated cleanup
  - no fake GREEN
- Safe remediation batch: Apply touched-file semantic PHPDoc cleanup inside each remediation batch.
- Tests required: semantic PHPDoc gate after each cleanup batch.
- Validation commands: `php tooling/governance/check-semantic-phpdoc.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/global-governance-heatmap.md`
- Commit gate: no unrelated dirty files staged; no production/test files outside the prompt scope; validation output captured; no fake GREEN claim.
- Done when: mapped source findings are closed or reclassified with evidence, validation passes, and source coverage is updated.
- Owner: AvaX maintainer
- Expiry/Target: review at next cleanup milestone

- Reason accepted: legacy semantic PHPDoc ratchet has current accepted-YELLOW evidence and 0 touched/new blockers in the discipline review.
- Risk: documentation debt can obscure public/runtime/security intent if touched files are not cleaned.
- Mitigation: every remediation batch must clean semantic PHPDoc in touched public/runtime/security files.
- Next action: keep ratchet gate active and update evidence after each cleanup.

## Accepted Exceptions

- None. No explicit accepted exception was created in this reconciliation pass.

## Component Backlog

### components/ and framework/
- TODO-032
### components/API/ApiBlueprint
- TODO-009, TODO-014, TODO-015, TODO-020
### components/API/Contracts
- TODO-009, TODO-014, TODO-015, TODO-021
### components/API/Contracts/
- TODO-022
### components/API/GraphQL
- TODO-009, TODO-014, TODO-015, TODO-020, TODO-031
### components/API/OpenAPI
- TODO-009, TODO-014, TODO-015
### components/API/SchemaGeneration
- TODO-009, TODO-014, TODO-015
### components/Application/Cache
- TODO-001, TODO-010, TODO-014, TODO-020, TODO-031
### components/Application/Config
- TODO-014, TODO-031
### components/Application/Container
- TODO-002, TODO-010, TODO-014, TODO-020, TODO-031
### components/Application/DateTime
- TODO-010, TODO-020
### components/Application/FeatureFlags
- TODO-010, TODO-015
### components/Application/Filesystem
- TODO-010, TODO-014
### components/Application/Localization
- TODO-015
### components/Application/Pipeline
- TODO-010, TODO-031
### components/Application/Storage
- TODO-010, TODO-014
### components/Application/Text
- TODO-010, TODO-019, TODO-020, TODO-031
### components/Application/Validation
- TODO-010, TODO-014, TODO-020
### components/CLI/Console
- TODO-009, TODO-014, TODO-015, TODO-031
### components/DataStack/Data
- TODO-013, TODO-014, TODO-015, TODO-020, TODO-031
### components/DataStack/DataTransfer
- TODO-013, TODO-014, TODO-015, TODO-017, TODO-020
### components/DataStack/Database
- TODO-013, TODO-014, TODO-020
### components/DataStack/Persistence
- TODO-013, TODO-014, TODO-015, TODO-020
### components/DeveloperTools
- TODO-009
### components/DeveloperTools/CodeGeneration
- TODO-015
### components/DeveloperTools/Diagnostics
- TODO-009, TODO-015
### components/DeveloperTools/Diagnostics/
- TODO-022
### components/DeveloperTools/Documentation/Api
- TODO-009, TODO-014, TODO-027
### components/DeveloperTools/DumpDebugger
- TODO-015, TODO-021
### components/DeveloperTools/Dx
- TODO-009, TODO-015
### components/DeveloperTools/TestSupport
- TODO-009, TODO-015
### components/Foundation/CallableSerialization
- TODO-009, TODO-014, TODO-015
### components/HTTP
- TODO-011, TODO-016, TODO-020
### components/HTTP/AfterResponse
- TODO-011, TODO-015
### components/HTTP/Client
- TODO-011, TODO-014, TODO-020, TODO-021
### components/HTTP/ContentNegotiation
- TODO-011, TODO-015
### components/HTTP/Context
- TODO-011, TODO-015
### components/HTTP/Dispatcher
- TODO-015
### components/HTTP/Request
- TODO-011, TODO-014, TODO-020, TODO-031
### components/HTTP/Response
- TODO-011
### components/HTTP/Router
- TODO-011, TODO-014, TODO-020, TODO-031
### components/HTTP/SecureRequest
- TODO-011, TODO-031
### components/HTTP/Security
- TODO-015
### components/HTTP/Security/
- TODO-022
### components/HTTP/Session
- TODO-011, TODO-014, TODO-020, TODO-031
### components/HTTP/System
- TODO-016
### components/HTTP/URI
- TODO-014, TODO-015
### components/Identity
- TODO-013
### components/Identity/Access
- TODO-013, TODO-020, TODO-031
### components/Identity/Auth
- TODO-007, TODO-013, TODO-014, TODO-020, TODO-031
### components/Identity/Credentials
- TODO-014, TODO-020
### components/Identity/ExternalIdentity
- TODO-014, TODO-020
### components/Identity/Security
- TODO-015, TODO-021
### components/Identity/Security/
- TODO-022
### components/Identity/Tenancy
- TODO-014, TODO-020
### components/Identity/Tokens
- TODO-013, TODO-014, TODO-020
### components/Integration/ObjectStorage
- TODO-009, TODO-021
### components/Operations
- TODO-012
### components/Operations/ApplicationWorkflow
- TODO-012, TODO-014, TODO-016, TODO-020, TODO-031
### components/Operations/BackgroundProcesses
- TODO-012, TODO-014, TODO-021
### components/Operations/Concurrency
- TODO-012, TODO-014
### components/Operations/Delivery
- TODO-012, TODO-021
### components/Operations/Events
- TODO-005, TODO-012, TODO-014
### components/Operations/Events/
- TODO-022
### components/Operations/Filesystem
- TODO-012, TODO-014, TODO-015
### components/Operations/Logging
- TODO-014
### components/Operations/Mail
- TODO-012, TODO-014, TODO-020
### components/Operations/MemoryLifecycle
- TODO-012, TODO-014, TODO-021
### components/Operations/MessageBus
- TODO-012, TODO-020
### components/Operations/Notifications
- TODO-012, TODO-014, TODO-020
### components/Operations/Observability
- TODO-012
### components/Operations/Parallelism
- TODO-012
### components/Operations/Queue
- TODO-012, TODO-014, TODO-020
### components/Operations/Realtime
- TODO-012, TODO-021
### components/Operations/Resilience
- TODO-012, TODO-014, TODO-020
### components/Operations/RuntimeSupervision
- TODO-012, TODO-014, TODO-020, TODO-021
### components/Operations/Scheduler
- TODO-012
### components/Operations/Tasks
- TODO-012
### components/Presentation
- TODO-009
### components/Security/Cryptography
- TODO-013
### components/Security/DataProtection
- TODO-013, TODO-014, TODO-021
### components/Security/Privacy
- TODO-013, TODO-014, TODO-021
### components/Security/Redaction
- TODO-013, TODO-014
### components/Security/Secrets
- TODO-005
### components/SystemDesign
- TODO-009, TODO-020, TODO-031

## Framework Backlog

### framework/System/Capabilities/Benchmarks
- TODO-014, TODO-020
### framework/System/Capabilities/ContainerIntelligence
- TODO-020
### framework/System/Capabilities/Doctor
- TODO-014, TODO-031
### framework/System/Capabilities/ExternalState
- TODO-005
### framework/System/Capabilities/FailureBoundary
- TODO-017, TODO-020
### framework/System/Capabilities/PreCommit
- TODO-014, TODO-020
### framework/System/Capabilities/ResourceGovernance
- TODO-006
### framework/System/Capabilities/Runtime
- TODO-006, TODO-014, TODO-020
### framework/System/Capabilities/RuntimeIsolation
- TODO-031
### framework/System/Capabilities/RuntimeSafety
- TODO-005
### framework/System/Capabilities/Security
- TODO-014
### framework/System/Capabilities/ServeModes
- TODO-031
### framework/System/Configuration/BootDsl
- TODO-020, TODO-031
### framework/System/Configuration/BuildApplication
- TODO-020
### framework/System/Configuration/Builders
- TODO-017
### framework/System/Configuration/ConfigureRuntime
- TODO-031
### framework/System/Configuration/Foundation
- TODO-020
### framework/System/Flows/BootApplication
- TODO-014
### framework/System/Flows/CreateApplication
- TODO-020
### framework/System/Flows/HandleIncomingHttp
- TODO-014, TODO-031
### framework/System/Flows/RunApplication
- TODO-014, TODO-020
### framework/System/Flows/RunDoctor
- TODO-031
### framework/System/PublicSurface
- TODO-006, TODO-014, TODO-031

## Cross-Cutting Backlog

- CLUSTER-001: Unsafe deserialization and serialized payload trust boundaries -> TODO-001
- CLUSTER-002: Compiled container namespace generation can emit broken runtime PHP -> TODO-002
- CLUSTER-003: CSRF/session authority conflict and direct session mutation -> TODO-003
- CLUSTER-004: Worker-unsafe static security/runtime state -> TODO-005
- CLUSTER-005: Static mutable state outside security-critical entrypoints -> TODO-008
- CLUSTER-006: Runtime class loading and dynamic instantiation leaks -> TODO-004
- CLUSTER-007: Framework public entrypoints assemble runtime object graphs -> TODO-006
- CLUSTER-008: AuthBuilder exceeds safe configuration-builder size -> TODO-007
- CLUSTER-009: PublicSurface classes construct collaborators instead of delegating -> TODO-006, TODO-009, TODO-010, TODO-011, TODO-012, TODO-013
- CLUSTER-010: Constructor default parameters instantiate dependencies -> TODO-014
- CLUSTER-011: Missing ServiceProvider assembly coverage -> TODO-015
- CLUSTER-012: Broken reference semantics in public/runtime namespaces -> TODO-016
- CLUSTER-013: Raw filesystem/path operations bypass approved boundaries -> TODO-017
- CLUSTER-014: Constructor bloat and dependency pressure -> TODO-020
- CLUSTER-015: Large units require responsibility split or explicit classification -> TODO-020
- CLUSTER-016: Security-sensitive behavior lacks negative/boundary tests -> TODO-018
- CLUSTER-017: Forbidden concept folder names and naming exceptions -> TODO-022
- CLUSTER-018: Duplicate ownership and duplicate class implementations -> TODO-023
- CLUSTER-019: Secret logging/redaction and SensitiveParameter gaps -> TODO-018
- CLUSTER-020: Hidden superglobal/env/IO access in runtime paths -> TODO-024
- CLUSTER-021: SQL/CSV/path injection surfaces requiring verification -> TODO-026
- CLUSTER-022: Public interface/PHPDoc contract gaps -> TODO-027
- CLUSTER-023: Empty stubs/no-op methods and incomplete units -> TODO-028
- CLUSTER-024: Silent catch-and-continue error handling -> TODO-025
- CLUSTER-025: Low-risk compat/version/style cleanup -> TODO-030
- CLUSTER-027: Global helper service-locator shortcuts -> TODO-019
- CLUSTER-028: Missing or weak behavior test proof -> TODO-021
- CLUSTER-029: Accepted semantic PHPDoc legacy ratchet -> TODO-032
- CLUSTER-032: Other review findings requiring triage -> TODO-031

## Cleanup Execution Order

1. ~~`TODO-001`~~ unsafe deserialization — DONE (36a8e3547)
2. ~~`TODO-002`~~ compiled container namespace generation — DONE (ae0c5689b)
3. ~~`TODO-003`~~ CSRF/session authority conflict — DONE (c3abfc1bb)
4. ~~`TODO-004`~~ dynamic class loading from payload/recovery boundaries — DONE (43c5e6883 + 634b552e5)
5. ~~`TODO-005`~~ worker-unsafe static secret/security runtime state — DONE (3f55d597d)
6. `TODO-006` framework public entrypoint object-graph assembly (P0 BLOCKER) — NEXT
7. `TODO-007` AuthBuilder large builder (P0 BLOCKER)
8. P1 security/runtime/PublicSurface/DI batches: `TODO-008` through `TODO-019`.
9. P2 test trust, architecture, docs, and maintainability batches.
10. P3 low-risk cleanup and accepted-yellow ratchet maintenance.

## Next Recommended Batch

- Batch title: P0 Framework Entrypoint Composition Extraction
- Exact TODO IDs: TODO-006
- Why this batch first: framework public entrypoints (`Avax.php`, `BootDsl.php`, `App.php`, `RunApplication.php`, `CreateApplication.php`) create runtime object graphs with `new`, weakening testability, worker safety, and DI discipline. TODO-001 through TODO-005 are DONE, making TODO-006 the next highest P0.
- Strict scope: `Avax.php`, `BootDsl.php`, `App.php`, `RunApplication.php`, `CreateApplication.php`, `BootDslEngine.php` — extract object-graph assembly into configuration owners.
- Forbidden changes: no framework API redesign, no behavior change, no public API change unless explicitly approved, no unrelated formatting.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && php tooling/refactor/check-public-surface.php`
- Evidence path: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md` plus a new cleanup evidence file for the batch.
- Expected final status: TODO-006 closed or split with explicit residual evidence; no broader GREEN claim.

### Post-Round-002 Priority Order (corrected after TODO-001 and TODO-002 closure)

1. **TODO-006** — framework public entrypoint object-graph assembly (P0 BLOCKER) — NEXT
2. **TODO-007** — AuthBuilder split (P0 BLOCKER)
3. **TODO-008 through TODO-015** — P1 security/runtime/PublicSurface/DI batches
4. **TODO-020 through TODO-032** — P2/P3/ACCEPTED_YELLOW

## Source Finding Disposition Summary

- ACTIVE_TODO: 73
- MERGED_DUPLICATE: 1484
- NEEDS_VERIFICATION: 263
- ACCEPTED_YELLOW: 1
- ACCEPTED_EXCEPTION: 0
- FALSE_POSITIVE_CANDIDATE: 0
- OBSOLETE / ALREADY_FIXED: 0
- Complete source-level disposition table: `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`.

## Archived / Superseded Previous fix-this.md Items

- Preserved: all meaningful previous TODO source IDs remain mapped through `OLD-FIX-*` rows in source coverage.
- Rewritten: previous duplicate component TODOs are collapsed into cluster-based TODOs.
- Merged: duplicate DR/SCR/HTD/OLD-FIX IDs share canonical cluster/TODO dispositions.
- Obsolete: none marked obsolete without evidence.
- Already fixed: none marked already fixed in this pass.
- Deferred to evidence: historical detail from the old backlog is retained in reconciliation evidence, not copied as raw noise.

## Validation Command Summary

| Command | Status | Evidence summary | Impact |
|---|---|---|---|
| `composer validate --no-check-publish` | PASS | `./composer.json is valid` | Composer metadata valid. |
| `composer dump-autoload -o` | PASS_WITH_WARNING | Optimized autoload generated with 9346 classes; PSR-4 warning for `framework/System/Foundation/compat.php` class `xhp_`. | Autoload generation completed; warning remains evidence, not GREEN proof. |
| `php tooling/refactor/check-component-suite-structure.php` | PASS | `PASS` | Structure gate passed. |
| `php tooling/refactor/check-duplicate-owners.php` | PASS | `PASS` | Duplicate owner scanner passed; semantic duplicate clusters remain from review evidence. |
| `php tooling/refactor/check-namespace-drift.php` | PASS | `PASS` | Namespace drift scanner passed; compiled-container source-generation risk remains separately tracked. |
| `php tooling/refactor/check-public-surface.php` | PASS | `PASS` | Scanner passed; semantic PublicSurface findings remain in TODOs. |
| `php tooling/refactor/check-runtime-composition-leaks.php` | PASS | `PASS` | Scanner passed; supplemental semantic runtime-composition risks remain in TODOs. |
| `php tooling/governance/check-governance-index-current.php` | PASS | Governance index current. | Governance index is current. |
| `php tooling/governance/check-root-evidence-hygiene.php` | PASS | Root evidence hygiene passed. | Root evidence hygiene passed. |
| `bash verify-governance.sh .` | PASS_WITH_SIDE_EFFECTS | Governance script passed and updated generated governance event/provenance/context files. | Side-effect files were not staged. |
| `php tooling/refactor/check-direct-instantiation.php` | FAIL_EXPECTED_FINDINGS | 761 output lines of constructor/default/fallback instantiation findings. | Supports TODO-004, TODO-006, TODO-009 through TODO-014. |
| `php tooling/refactor/check-constructor-bloat.php` | FAIL_EXPECTED_FINDINGS | 460 output lines of CHECK/WARNING constructor arity findings. | Supports TODO-020. |
| `php tooling/refactor/check-service-provider-coverage.php` | FAIL_EXPECTED_FINDINGS | 25 missing ServiceProvider reports. | Supports TODO-015. |
| `php tooling/refactor/check-broken-reference-semantics.php` | FAIL_EXPECTED_FINDINGS | 5 active broken references. | Supports TODO-016. |
| `php tooling/governance/check-large-unit-thresholds.php` | FAIL_EXPECTED_FINDINGS | 3887 files scanned; 107 findings; 1 BLOCKER (`AuthBuilder`), 106 REVIEW. | Supports TODO-007 and TODO-020. |

No production PHP code, tests, composer files, or tracked autoload files were intentionally changed by this planning pass. Optional gate failures are the evidence feeding the backlog, not remediation failures.

