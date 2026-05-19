# fix-this.md

## Status

- Mode: Component and Framework Discipline Cleanup
- Source: Recursive governance/code review
- Scope: components/ and framework/
- Last generated: 2026-05-19T22:16:56+02:00
- Current rule: Fix by smallest safe remediation batch
- Non-goals:
  - no feature work
  - no roadmap work
  - no public API changes unless explicitly approved
  - no mechanical renames
  - no fake GREEN

## How To Use This File

This file is the active TODO backlog. Detailed evidence lives under `.agents/management/evidence/generated/discipline-review/`. Each TODO links back to review finding IDs. Each remediation batch needs its own 11++ prompt, must avoid unrelated components, and must not be marked DONE without validation and evidence.

## Severity Policy

- BLOCKER: mandatory governance violation that prevents a clean approval until fixed or formally accepted.
- HIGH: safety, public API, runtime, security, or architecture risk that must be fixed before a clean status.
- MEDIUM: maintainability, test proof, documentation, or local ownership issue that must be tracked and fixed or explicitly deferred.
- LOW: local cleanup or classification item with low blast radius.
- ACCEPTED_YELLOW: known debt with owner, reason, risk, expiry/target, and next action.
- ACCEPTED_EXCEPTION: only valid when explicitly justified by governance exception rules.

## Current Global Summary

- Reviewed components: 85
- Reviewed framework units: 83
- Highest severity count: BLOCKER 1, HIGH 247, MEDIUM 414, LOW 5, ACCEPTED_YELLOW 1
- Total findings count: 668
- Clean units count: 68
- Units needing remediation: 100
- Evidence paths: `.agents/management/evidence/generated/discipline-review/review-unit-discovery.md`, `global-governance-heatmap.md`, `remediation-plan.md`, `components/*-review.md`, `framework/*-review.md`

## Active Queue

### P0 — BLOCKER

#### TODO-001: Split and classify AuthBuilder assembly responsibility

- Status: OPEN
- Severity: BLOCKER
- Root type: COMPONENT
- Unit: `components/Identity/Auth`
- Finding IDs: DR-0603
- Rule sources: how-to-code-review.md §21
- Problem: Configuration builder is 797 lines (>300).
- Why it matters: Large builders become hidden containers and block clean status until classified/split.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php`
- Safe remediation batch: Large builder split/classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

### P1 — HIGH

#### TODO-002: Route raw filesystem access through an approved boundary

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/Builders`
- Finding IDs: DR-0044
- Rule sources: how-to-system-security.md §22
- Problem: Raw `is_file()` in framework route-dispatch builder is classified MIGRATE_TO_FILESYSTEM.
- Why it matters: Route-file existence is filesystem I/O in framework configuration; dogfooding requires the Filesystem owner or explicit boundary.
- Risk type: SECURITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/Builders/BuildDispatchConfiguredRoute.php:46`
- Safe remediation batch: Raw filesystem migration batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-003: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/ApiBlueprint`
- Finding IDs: DR-0341, DR-0342
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (7 `new` expressions detected).; PublicSurface directly instantiates collaborators (7 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29,31,33,34,35,36,38`; `components/API/ApiBlueprint/System/PublicSurface/ApiSurface.php:19,24,29,37,45,53,58`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-004: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/Contracts`
- Finding IDs: DR-0344
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (7 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/Contracts/System/PublicSurface/ApiContracts.php:22,27,38,49,57,65,80`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-005: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/GraphQL`
- Finding IDs: DR-0322, DR-0323, DR-0328
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 3 related findings: PublicSurface directly instantiates collaborators (4 `new` expressions detected).; PublicSurface directly instantiates collaborators (7 `new` expressions detected).; PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/GraphQL/System/PublicSurface/GraphQL.php:17,24,39,52`; `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php:59,61,62,63,80,104,121`; `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27,30,31,32,54,68,86,103`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-006: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/OpenAPI`
- Finding IDs: DR-0346
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (6 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/OpenAPI/System/PublicSurface/OpenAPI.php:22,23,31,36,44,49`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-007: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Cache`
- Finding IDs: DR-0049, DR-0050, DR-0048, DR-0051, DR-0052, DR-0053, DR-0054, DR-0055
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 8 related findings: PublicSurface directly instantiates collaborators (4 `new` expressions detected).; PublicSurface directly instantiates collaborators (4 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Cache/System/PublicSurface/CompiledCache.php:31,59,60,61`; `components/Application/Cache/System/PublicSurface/Cache.php:45,89,100,108`; `components/Application/Cache/System/PublicSurface/AvaxCache.php:153`; `components/Application/Cache/System/PublicSurface/Read/ReadFromCache.php:32,50`; `components/Application/Cache/System/PublicSurface/Read/RuntimeCacheTarget.php:21`; `components/Application/Cache/System/PublicSurface/Read/CompiledCacheTarget.php:29`; `components/Application/Cache/System/PublicSurface/Facade/CacheRegistry.php:33,47`; `components/Application/Cache/System/PublicSurface/Facade/Cache.php:71`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-008: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Cache`
- Finding IDs: DR-0424
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 334 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Cache/System/PublicSurface/AvaxCache.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-009: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Filesystem`
- Finding IDs: DR-0120
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (21 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Filesystem/System/PublicSurface/Filesystem.php:39,44,49,54,59,64,69,74...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-010: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Storage`
- Finding IDs: DR-0101
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (11 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Storage/System/PublicSurface/Storage.php:37,56,65,84,93,101,109,117...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-011: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Text`
- Finding IDs: DR-0098
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (25 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Text/System/PublicSurface/Text.php:29,34,49,54,59,64,69,74...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-012: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0287
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (10 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer/System/PublicSurface/DataTransfer.php:67,84,92,97,102,110,118,141...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-013: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/Database`
- Finding IDs: DR-0267, DR-0268, DR-0273, DR-0274
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 4 related findings: PublicSurface directly instantiates collaborators (3 `new` expressions detected).; PublicSurface directly instantiates collaborators (7 `new` expressions detected).; PublicSurface directly instantiates collaborators (4 `new` expressions detected)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Database/System/PublicSurface/shortcuts.php:48,67,86`; `components/DataStack/Database/System/PublicSurface/Database.php:36,51,56,61,66,71,76`; `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20,21,22,27`; `components/DataStack/Database/System/PublicSurface/Query.php:24`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-014: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/Persistence`
- Finding IDs: DR-0266
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Persistence/System/PublicSurface/DataLayer.php:31,32,33,35`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-015: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Diagnostics`
- Finding IDs: DR-0234, DR-0235
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (7 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Diagnostics/System/PublicSurface/HealthCheck.php:27,28,29,39,52,71,110`; `components/DeveloperTools/Diagnostics/System/PublicSurface/CheckResult.php:21`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-016: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Foundation/CallableSerialization`
- Finding IDs: DR-0237
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Foundation/CallableSerialization/System/PublicSurface/CallableSerialization.php:28,66,72`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-017: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP`
- Finding IDs: DR-0143
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/System/PublicSurface/Response.php:38,46,51`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-018: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Client`
- Finding IDs: DR-0126
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Client/System/PublicSurface/HttpClient.php:14,19,34`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-019: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/ContentNegotiation`
- Finding IDs: DR-0122, DR-0123
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (6 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/ContentNegotiation/System/PublicSurface/ContentNegotiation.php:23,30,44,45,46,47`; `components/HTTP/ContentNegotiation/System/PublicSurface/XmlFormatter.php:22`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-020: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Router`
- Finding IDs: DR-0146
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (5 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Router/System/PublicSurface/Router.php:46,49,107,117,130`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-021: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/SecureRequest`
- Finding IDs: DR-0144
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (6 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php:80,87,90,99,108,116`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-022: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Session`
- Finding IDs: DR-0140
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (5 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Session/System/PublicSurface/Session.php:30,91,92,129,130`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-023: Broken reference semantics batch

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/System`
- Finding IDs: DR-0039
- Rule sources: how-to-coding-standards.md
- Problem: Active broken reference: `Avax\Components\HTTP\System\Capabilities\MiddlewarePipeline\System\Foundation\Failure\MiddlewareFailure`.
- Why it matters: Broken active references undermine autoload truth and public compatibility.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/System`
- Safe remediation batch: Broken reference semantics batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-024: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Tokens`
- Finding IDs: DR-0382
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Tokens/System/PublicSurface/Tokens.php:33,34,35,50,51,52,56,60`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-025: Broken reference semantics batch

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/ApplicationWorkflow`
- Finding IDs: DR-0040, DR-0041, DR-0042, DR-0043
- Rule sources: how-to-coding-standards.md
- Problem: 4 related findings: Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\CompensationExecutor`.; Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\IdempotencyStore`.; Active broken reference: `Avax\Components\Operations\ApplicationWorkflow\System\PublicSurface\SagaState`....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/ApplicationWorkflow`; `components/Operations/ApplicationWorkflow`; `components/Operations/ApplicationWorkflow`; `components/Operations/ApplicationWorkflow`
- Safe remediation batch: Broken reference semantics batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-026: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/ApplicationWorkflow`
- Finding IDs: DR-0205, DR-0203, DR-0206
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 3 related findings: PublicSurface directly instantiates collaborators (8 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47,53,54,55,74,78,98,116`; `components/Operations/ApplicationWorkflow/System/PublicSurface/ApplicationWorkflow.php:18`; `components/Operations/ApplicationWorkflow/System/PublicSurface/Workflow.php:41`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-027: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/ApplicationWorkflow`
- Finding IDs: DR-0491
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 304 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-028: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/BackgroundProcesses`
- Finding IDs: DR-0185
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (10 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/BackgroundProcesses/System/PublicSurface/BackgroundProcesses.php:21,26,31,36,44,52,60,62...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-029: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Concurrency`
- Finding IDs: DR-0169
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Concurrency/System/PublicSurface/Concurrency.php:56,87,95`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-030: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Delivery`
- Finding IDs: DR-0165
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Delivery/System/PublicSurface/Delivery.php:16,21,26,31`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-031: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Events`
- Finding IDs: DR-0150, DR-0151
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (4 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Events/System/PublicSurface/Events.php:18,31,32,39`; `components/Operations/Events/System/PublicSurface/functions.php:20`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-032: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Filesystem`
- Finding IDs: DR-0196
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (6 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Filesystem/System/PublicSurface/Filesystem.php:18,23,28,33,38,46`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-033: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Mail`
- Finding IDs: DR-0192, DR-0190, DR-0193
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 3 related findings: PublicSurface directly instantiates collaborators (5 `new` expressions detected).; PublicSurface directly instantiates collaborators (2 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Mail/System/PublicSurface/Mailer.php:15,31,46,51,117`; `components/Operations/Mail/System/PublicSurface/SendResult.php:18,23`; `components/Operations/Mail/System/PublicSurface/RawMailBuilder.php:73`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-034: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/MemoryLifecycle`
- Finding IDs: DR-0201
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/MemoryLifecycle/System/PublicSurface/MemoryLifecycle.php:15,20,25`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-035: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/MessageBus`
- Finding IDs: DR-0182
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (4 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/MessageBus/System/PublicSurface/MessageBus.php:56,57,58,59`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-036: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Observability`
- Finding IDs: DR-0189
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (6 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Observability/System/PublicSurface/Observability.php:18,23,28,33,41,50`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-037: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Queue`
- Finding IDs: DR-0177, DR-0174, DR-0175, DR-0179
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 4 related findings: PublicSurface directly instantiates collaborators (4 `new` expressions detected).; PublicSurface directly instantiates collaborators (2 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Queue/System/PublicSurface/Tasks.php:14,20,26,40`; `components/Operations/Queue/System/PublicSurface/JobResult.php:19,24`; `components/Operations/Queue/System/PublicSurface/JobId.php:17`; `components/Operations/Queue/System/PublicSurface/TaskBatch.php:19`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-038: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Realtime`
- Finding IDs: DR-0164
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (3 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Realtime/System/PublicSurface/Realtime.php:22,31,51`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-039: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Scheduler`
- Finding IDs: DR-0183, DR-0184
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (3 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Scheduler/System/PublicSurface/Scheduler.php:19,32,45`; `components/Operations/Scheduler/System/PublicSurface/TaskRunner.php:14`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-040: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Tasks`
- Finding IDs: DR-0159
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (7 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Tasks/System/PublicSurface/Tasks.php:20,25,30,38,43,51,61`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-041: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/DataProtection`
- Finding IDs: DR-0312
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/DataProtection/System/PublicSurface/DataProtection.php:18,23,28,36,44,52,57,62`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-042: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/Privacy`
- Finding IDs: DR-0307
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (7 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Privacy/System/PublicSurface/Privacy.php:19,24,29,37,47,57,63`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-043: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/Redaction`
- Finding IDs: DR-0297
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Redaction/System/PublicSurface/Redaction.php:20,25,30,35,46,56,64,69`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-044: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/SystemDesign`
- Finding IDs: DR-0239
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (18 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/SystemDesign/System/PublicSurface/SystemDesignKit.php:56,65,70,99,116,134,152,169...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-045: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/SystemDesign`
- Finding IDs: DR-0501
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 491 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/SystemDesign/System/PublicSurface/SystemDesignKit.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-046: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ExternalState`
- Finding IDs: DR-0412
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (9 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:34,37,54,57,74,77,94,97...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-047: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0389
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (11 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface:207,211,254,271,272,274,275,285...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-048: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0638
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 366 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-049: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0392
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (35 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface:72,74,75,76,77,78,79,80...`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-050: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0394
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (8 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface:55,59,150,151,155,156,159,164`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-051: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/ApiBlueprint`
- Finding IDs: DR-0552
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 13 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/ApiBlueprint/System/Capabilities/EndpointDefinitions/EndpointDefinition.php:19`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-052: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/ApiBlueprint`
- Finding IDs: DR-0340, DR-0343
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php:29`; `components/API/ApiBlueprint/System/Flows/AnalyzeApiEvolution/AnalyzeApiEvolution.php:25`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-053: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/Contracts`
- Finding IDs: DR-0345
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/Contracts/System/Flows/RegisterApiVersion/RegisterApiVersion.php:13`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-054: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/GraphQL`
- Finding IDs: DR-0324, DR-0325, DR-0326, DR-0327, DR-0329, DR-0330, DR-0331, DR-0332, DR-0333, DR-0334, DR-0335, DR-0336, DR-0337, DR-0338, DR-0339
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 15 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:27`; `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:30`; `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:31`; `components/API/GraphQL/System/PublicSurface/GraphQLExecutor.php:32`; `components/API/GraphQL/System/Flows/ExecuteGraphQLMutation/ExecuteGraphQLMutation.php:18`; `components/API/GraphQL/System/Flows/ExecuteGraphQLQuery/ExecuteGraphQLQuery.php:18`; `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:21`; `components/API/GraphQL/System/Flows/ValidateGraphQLOperation/ValidateGraphQLOperation.php:22`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-055: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/GraphQL`
- Finding IDs: DR-0016
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/GraphQL`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-056: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/API/OpenAPI`
- Finding IDs: DR-0017
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/OpenAPI`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-057: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Cache`
- Finding IDs: DR-0064, DR-0065, DR-0066, DR-0067, DR-0068, DR-0069, DR-0070, DR-0071, DR-0072, DR-0073, DR-0074, DR-0075, DR-0076, DR-0077, DR-0078, DR-0079, DR-0047, DR-0056, DR-0057, DR-0058, ...
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 43 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:31`; `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:33`; `components/Application/Cache/System/Flows/Compiled/ClearCompiledCache/ClearCompiledCache.php:38`; `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:27`; `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:37`; `components/Application/Cache/System/Flows/Compiled/ReadCompiledCache/ReadCompiledCache.php:39`; `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:27`; `components/Application/Cache/System/Flows/Compiled/CompileCache/CompileCache.php:36`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-058: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Container`
- Finding IDs: DR-0443, DR-0446, DR-0449, DR-0451, DR-0452, DR-0454, DR-0442, DR-0448, DR-0456
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 9 related findings: Constructor has 16 parameters.; Constructor has 19 parameters.; Constructor has 35 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php:87`; `components/Application/Container/System/Capabilities/ContainerObservability/Observability/RuntimeReport.php:35`; `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php:35`; `components/Application/Container/System/Capabilities/Composition/Compilation/CompileReport.php:25`; `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php:63`; `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php:62`; `components/Application/Container/System/Capabilities/Resolution/LifetimePlan.php:33`; `components/Application/Container/System/Capabilities/Composition/Assembly/RuntimeAssembly.php:21`; ...
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-059: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Application/Validation`
- Finding IDs: DR-0118
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Validation/System/PublicSurface/Validation.php:18`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-060: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/CLI/Console`
- Finding IDs: DR-0225
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/CLI/Console/System/PublicSurface/Console.php:26`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-061: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0548, DR-0544, DR-0547, DR-0549
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 4 related findings: Constructor has 12 parameters.; Constructor has 9 parameters.; Constructor has 11 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/DataField.php:21`; `components/DataStack/DataTransfer/System/Configuration/DataTransferConfig.php:15`; `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompiledAttributeMetadata.php:37`; `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompiledSchemaMetadata.php:29`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-062: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0288, DR-0289, DR-0290, DR-0291, DR-0292, DR-0293, DR-0294, DR-0295
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 8 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToJsonApi.php:18`; `components/DataStack/DataTransfer/System/Flows/SerializeDataObject/ConvertDataObjectToArray.php:23`; `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:20`; `components/DataStack/DataTransfer/System/Flows/ReadDataObject/ReadDataObject.php:21`; `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/AttributeCompiler.php:35`; `components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/InspectDataShape.php:13`; `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/SerializeLegacyDTO.php:19`; `components/DataStack/DataTransfer/System/Capabilities/LegacyTransfer/AbstractDTO.php:27`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-063: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0023
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-064: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/Database`
- Finding IDs: DR-0535, DR-0522, DR-0523, DR-0524, DR-0525, DR-0526, DR-0528, DR-0529, DR-0530, DR-0532, DR-0533, DR-0540, DR-0541
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 13 related findings: Constructor has 16 parameters.; Constructor has 8 parameters.; Constructor has 9 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php:71`; `components/DataStack/Database/System/PublicSurface/Database.php:23`; `components/DataStack/Database/System/Capabilities/Observability/QueryEntry.php:14`; `components/DataStack/Database/System/Capabilities/Observability/SlowQueryStatistics.php:12`; `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:15`; `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php:94`; `components/DataStack/Database/System/Capabilities/Observability/SlowQueryReport.php:15`; `components/DataStack/Database/System/Capabilities/Transactions/DeadlockReport.php:14`; ...
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-065: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/Database`
- Finding IDs: DR-0269, DR-0270, DR-0271, DR-0272, DR-0275, DR-0276, DR-0277, DR-0278, DR-0279, DR-0280, DR-0281, DR-0282, DR-0283, DR-0284, DR-0285, DR-0286
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 16 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:20`; `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:21`; `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:22`; `components/DataStack/Database/System/PublicSurface/ManageEntityPersistence.php:27`; `components/DataStack/Database/System/Capabilities/Observability/QueryTimeline.php:44`; `components/DataStack/Database/System/Capabilities/Connections/MultiTenantPool.php:26`; `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:44`; `components/DataStack/Database/System/Capabilities/Connections/Pools/DatabaseConnectionPool.php:45`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-066: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DataStack/Persistence`
- Finding IDs: DR-0264, DR-0265
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Persistence/System/PublicSurface/DataLayer.php:32`; `components/DataStack/Persistence/System/PublicSurface/DataLayer.php:33`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-067: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Documentation/Api`
- Finding IDs: DR-0228, DR-0229, DR-0230, DR-0231
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Documentation/Api/System/Flows/RenderSwaggerUi/RenderSwaggerUi.php:13`; `components/DeveloperTools/Documentation/Api/System/Flows/RenderSwaggerUi/RenderSwaggerUi.php:22`; `components/DeveloperTools/Documentation/Api/System/Flows/GenerateApiDocs/GenerateApiDocs.php:13`; `components/DeveloperTools/Documentation/Api/System/Flows/GenerateApiDocs/GenerateApiDocs.php:23`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-068: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Foundation/CallableSerialization`
- Finding IDs: DR-0238
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Foundation/CallableSerialization/System/Flows/EncodeCallable/EncodeCallable.php:34`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-069: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Foundation/CallableSerialization`
- Finding IDs: DR-0030
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Foundation/CallableSerialization`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-070: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Client`
- Finding IDs: DR-0463, DR-0464, DR-0466
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 3 related findings: Constructor has 15 parameters.; Constructor has 12 parameters.; Constructor has 9 parameters.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Client/System/Capabilities/Requests/RequestOptions.php:40`; `components/HTTP/Client/System/Capabilities/Responses/ClientResponse.php:30`; `components/HTTP/Client/System/Capabilities/Resilience/RetryPolicy.php:37`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-071: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Client`
- Finding IDs: DR-0125
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Client/System/PublicSurface/HttpClient.php:14`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-072: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Dispatcher`
- Finding IDs: DR-0034
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Dispatcher`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-073: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Request`
- Finding IDs: DR-0127, DR-0133, DR-0129, DR-0130, DR-0131, DR-0132, DR-0134
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 7 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Request/System/PublicSurface/Request.php:33`; `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:41`; `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:43`; `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:44`; `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:45`; `components/HTTP/Request/System/Configuration/Builders/RequestBuilder.php:46`; `components/HTTP/Request/System/Capabilities/Headers/RequestHeaders.php:20`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-074: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Router`
- Finding IDs: DR-0145
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Router/System/PublicSurface/Router.php:46`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-075: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Security`
- Finding IDs: DR-0035
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Security`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-076: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/HTTP/Session`
- Finding IDs: DR-0139, DR-0138, DR-0141
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Session/System/PublicSurface/Session.php:30`; `components/HTTP/Session/System/Foundation/SessionRecord.php:26`; `components/HTTP/Session/System/Capabilities/Storage/RedisSessionStore.php:38`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-077: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Access`
- Finding IDs: DR-0631, DR-0629, DR-0630, DR-0632
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 4 related findings: Constructor has 16 parameters.; Constructor has 11 parameters.; Constructor has 8 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Access/System/Capabilities/Policy/IdentityPolicy.php:28`; `components/Identity/Access/System/Capabilities/Access.php:29`; `components/Identity/Access/System/Capabilities/RequireAccessPolicy/RequireAccessPolicy.php:31`; `components/Identity/Access/System/Capabilities/Policy/AccessPolicy.php:21`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-078: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Auth`
- Finding IDs: DR-0600, DR-0601, DR-0604, DR-0610, DR-0614, DR-0615, DR-0605, DR-0606, DR-0607, DR-0608, DR-0609, DR-0611, DR-0613, DR-0616, DR-0617, DR-0618, DR-0619, DR-0620, DR-0621, DR-0622, ...
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 24 related findings: Constructor has 27 parameters.; Constructor has 43 parameters.; Constructor has 12 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthExternalIdentityGraph.php:92`; `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php:136`; `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php:34`; `components/Identity/Auth/System/Flows/CheckAuthentication/AuthenticateRequest/AuthenticationContext.php:16`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/SCIM.php:38`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/ScimDirectory.php:17`; `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php:23`; `components/Identity/Auth/System/Flows/ChangeEmail/ConfirmEmailChange.php:23`; ...
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-079: Split and classify AuthBuilder assembly responsibility

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Auth`
- Finding IDs: DR-0370, DR-0373, DR-0374, DR-0375, DR-0376, DR-0377, DR-0359, DR-0360, DR-0361, DR-0362, DR-0363, DR-0364, DR-0365, DR-0366, DR-0367, DR-0368, DR-0369, DR-0371, DR-0372
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 19 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Auth/System/Flows/Register/CreateRegisteredUser.php:26`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/DeprovisionUser/DeprovisionUser.php:38`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/SuspendUser/SuspendUser.php:38`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/ReactivateUser/ReactivateUser.php:23`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadGroups/ReadScimGroups.php:22`; `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ReadUsers/ReadScimUsers.php:30`; `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`; `components/Identity/Auth/System/Foundation/Time/Expiry.php:16`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-080: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Credentials`
- Finding IDs: DR-0560, DR-0553, DR-0554, DR-0555, DR-0556, DR-0557, DR-0558, DR-0559, DR-0561, DR-0562, DR-0563, DR-0564, DR-0565, DR-0566
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 14 related findings: Constructor has 13 parameters.; Constructor has 8 parameters.; Constructor has 10 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/VerifyMfaChallenge.php:35`; `components/Identity/Credentials/System/Capabilities/Passkey/Passkey.php:30`; `components/Identity/Credentials/System/Capabilities/Mfa/Mfa.php:32`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Disable/DisableMfa.php:27`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Recover/ConfirmMfaRecovery.php:26`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/MfaEnrollment.php:17`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Enroll/StartMfaEnrollment.php:30`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Verify/StartMfaChallenge.php:30`; ...
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-081: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Credentials`
- Finding IDs: DR-0349, DR-0350
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:35`; `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php:36`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-082: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/ExternalIdentity`
- Finding IDs: DR-0573, DR-0574, DR-0575, DR-0581, DR-0584, DR-0585, DR-0586, DR-0590, DR-0591, DR-0594, DR-0597, DR-0598, DR-0572, DR-0577, DR-0578, DR-0579, DR-0582, DR-0583, DR-0588, DR-0589, ...
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 25 related findings: Constructor has 14 parameters.; Constructor has 20 parameters.; Constructor has 12 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/OAuth.php:41`; `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OidcProviderMetadata.php:21`; `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php:26`; `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequestData.php:18`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/OAuthTokenGrant.php:24`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/OAuthClient.php:48`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/AuthorizationCodeRecord.php:19`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/IntrospectToken/TokenIntrospection.php:20`; ...
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-083: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/ExternalIdentity`
- Finding IDs: DR-0355, DR-0356
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/UpdateClient/UpdateClient.php:31`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Runtime/ReadWorkloadIdentities/ReadWorkloadIdentities.php:25`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-084: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Security`
- Finding IDs: DR-0037
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Security`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-085: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Tenancy`
- Finding IDs: DR-0569, DR-0567, DR-0568, DR-0570, DR-0571
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 5 related findings: Constructor has 13 parameters.; Constructor has 8 parameters.; Constructor has 8 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Tenancy/System/Capabilities/Security/TenantSecurityChangeRequest.php:14`; `components/Identity/Tenancy/System/Capabilities/Security/TenantSecurityConfiguration.php:21`; `components/Identity/Tenancy/System/Capabilities/Security/Security.php:20`; `components/Identity/Tenancy/System/Capabilities/Model/TenantInvite.php:12`; `components/Identity/Tenancy/System/Capabilities/Tenants/Tenants.php:29`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-086: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Tenancy`
- Finding IDs: DR-0351, DR-0352, DR-0353, DR-0354
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Tenancy/System/Capabilities/Runtime/TenantSecurity/BeginChange/BeginTenantSecurityChange.php:36`; `components/Identity/Tenancy/System/Capabilities/Runtime/TenantSecurity/ApproveChange/ApproveTenantSecurityChange.php:30`; `components/Identity/Tenancy/System/Capabilities/Runtime/TenantSecurity/ApplyChange/ApplyTenantSecurityChange.php:37`; `components/Identity/Tenancy/System/Capabilities/Runtime/Tenant/InviteMember/InviteTenantMember.php:32`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-087: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Tokens`
- Finding IDs: DR-0634, DR-0633, DR-0635, DR-0636, DR-0637
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 5 related findings: Constructor has 12 parameters.; Constructor has 10 parameters.; Constructor has 9 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Record/RefreshTokenRecord.php:17`; `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Record/ResolvedToken.php:17`; `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Record/ResolvedWorkloadToken.php:16`; `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Flow/RefreshAuthentication.php:29`; `components/Identity/Tokens/System/Capabilities/Tokens/Runtime/Code/AuthorizationCodeRecord.php:15`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-088: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Identity/Tokens`
- Finding IDs: DR-0379, DR-0380, DR-0381, DR-0383, DR-0384, DR-0385, DR-0386, DR-0387
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 8 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Tokens/System/PublicSurface/Tokens.php:33`; `components/Identity/Tokens/System/PublicSurface/Tokens.php:34`; `components/Identity/Tokens/System/PublicSurface/Tokens.php:35`; `components/Identity/Tokens/System/Flows/AuthorizeToken/AuthorizeTokenRequest.php:17`; `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:24`; `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:25`; `components/Identity/Tokens/System/Flows/ExchangeToken/ExchangeAuthorizationCode.php:33`; `components/Identity/Tokens/System/Flows/IntrospectToken/IntrospectToken.php:23`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-089: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/ApplicationWorkflow`
- Finding IDs: DR-0494, DR-0495, DR-0496, DR-0492, DR-0493, DR-0497, DR-0498
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 7 related findings: Constructor has 14 parameters.; Constructor has 16 parameters.; Constructor has 13 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/ApplicationWorkflow/System/Flows/Saga/StartSaga/SagaInstance.php:26`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaDefinition.php:31`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/DefineSaga/SagaStepDefinition.php:23`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:24`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/StoreSagaState/SagaEvent.php:15`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/RunSagaStep/SagaStepResult.php:19`; `components/Operations/ApplicationWorkflow/System/Capabilities/Saga/Saga.php:24`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-090: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/ApplicationWorkflow`
- Finding IDs: DR-0204, DR-0207, DR-0208, DR-0209, DR-0210, DR-0211, DR-0212, DR-0213, DR-0214, DR-0215, DR-0216, DR-0217, DR-0218, DR-0219, DR-0220, DR-0221, DR-0222, DR-0223, DR-0224
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 19 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/ApplicationWorkflow/System/PublicSurface/Saga.php:47`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:38`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:39`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:40`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/Saga.php:42`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:15`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:16`; `components/Operations/ApplicationWorkflow/System/Flows/Saga/ConfigureSagaRuntime/ConfigureSagaRuntime.php:17`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-091: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/BackgroundProcesses`
- Finding IDs: DR-0186, DR-0187, DR-0188
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/BackgroundProcesses/System/Flows/MonitorBackgroundProcess/MonitorBackgroundProcess.php:13`; `components/Operations/BackgroundProcesses/System/Flows/RestartBackgroundProcess/RestartBackgroundProcess.php:15`; `components/Operations/BackgroundProcesses/System/Flows/RestartBackgroundProcess/RestartBackgroundProcess.php:16`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-092: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Concurrency`
- Finding IDs: DR-0170, DR-0171, DR-0172
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:22`; `components/Operations/Concurrency/System/Capabilities/RunWithFibers/FiberTaskRuntime.php:23`; `components/Operations/Concurrency/System/Capabilities/ChooseTaskRuntime/ChooseTaskRuntime.php:26`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-093: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Events`
- Finding IDs: DR-0148, DR-0149, DR-0152, DR-0153, DR-0147, DR-0154, DR-0155, DR-0156
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 8 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Events/System/PublicSurface/Events.php:31`; `components/Operations/Events/System/PublicSurface/Events.php:32`; `components/Operations/Events/System/Flows/CompileEventListeners/CompileEventListeners.php:36`; `components/Operations/Events/System/Flows/RegisterEventListeners/EventListenerDsl.php:28`; `components/Operations/Events/System/Foundation/GlobalEventListenerState.php:28`; `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:22`; `components/Operations/Events/System/Capabilities/Psr14/Psr14ListenerProviderAdapter.php:35`; `components/Operations/Events/System/Capabilities/ResolveEventListeners/ResolveEventListeners.php:28`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-094: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Mail`
- Finding IDs: DR-0191, DR-0194
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Mail/System/PublicSurface/Mailer.php:15`; `components/Operations/Mail/System/Capabilities/Queue/MailableBuilder.php:13`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-095: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Notifications`
- Finding IDs: DR-0157
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Notifications/System/PublicSurface/Notifier.php:20`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-096: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/Queue`
- Finding IDs: DR-0176, DR-0178, DR-0173, DR-0180, DR-0181
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 5 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Queue/System/PublicSurface/Tasks.php:40`; `components/Operations/Queue/System/PublicSurface/TaskBatch.php:19`; `components/Operations/Queue/System/Capabilities/TaskBus.php:18`; `components/Operations/Queue/System/Capabilities/Queue/RedisQueue.php:26`; `components/Operations/Queue/System/Capabilities/Queue/MemoryQueue/MemoryQueue.php:25`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-097: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Operations/RuntimeSupervision`
- Finding IDs: DR-0200
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/RuntimeSupervision/System/Capabilities/Supervision/Supervisor.php:28`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-098: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/DataProtection`
- Finding IDs: DR-0313, DR-0314, DR-0315, DR-0316, DR-0317
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 5 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/DataProtection/System/Flows/DecryptData/DecryptData.php:14`; `components/Security/DataProtection/System/Flows/DecryptData/DecryptData.php:15`; `components/Security/DataProtection/System/Flows/RotateEncryptionKey/RotateEncryptionKey.php:12`; `components/Security/DataProtection/System/Flows/EncryptData/EncryptData.php:13`; `components/Security/DataProtection/System/Flows/EncryptData/EncryptData.php:14`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-099: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/Privacy`
- Finding IDs: DR-0308, DR-0309, DR-0310, DR-0311
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Privacy/System/Flows/DeleteUserData/DeleteUserData.php:12`; `components/Security/Privacy/System/Flows/ExportUserData/ExportUserData.php:12`; `components/Security/Privacy/System/Flows/ApplyRetentionPolicy/ApplyRetentionPolicy.php:12`; `components/Security/Privacy/System/Capabilities/EnforceRetentionPolicy/EnforceRetentionPolicy.php:17`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-100: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/Redaction`
- Finding IDs: DR-0298, DR-0299, DR-0300, DR-0301, DR-0302, DR-0303
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 6 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Redaction/System/Flows/RedactLogData/RedactLogData.php:12`; `components/Security/Redaction/System/Flows/ApplyRedactionPolicy/ApplyRedactionPolicy.php:13`; `components/Security/Redaction/System/Flows/ApplyRedactionPolicy/ApplyRedactionPolicy.php:14`; `components/Security/Redaction/System/Flows/ClassifySensitiveData/ClassifySensitiveData.php:12`; `components/Security/Redaction/System/Capabilities/PolicyEngine/PolicyEngine.php:14`; `components/Security/Redaction/System/Capabilities/PolicyEngine/PolicyEngine.php:15`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-101: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/Security/Secrets`
- Finding IDs: DR-0305
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Secrets/System/Capabilities/Stores/EncryptedSecretStore.php:17`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-102: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: COMPONENT
- Unit: `components/SystemDesign`
- Finding IDs: DR-0503, DR-0508, DR-0505
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 3 related findings: Constructor has 13 parameters.; Constructor has 12 parameters.; Constructor has 8 parameters.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/SystemDesign/System/Capabilities/Capacity/CapacityModel.php:29`; `components/SystemDesign/System/Capabilities/Messaging/MessagingModel.php:41`; `components/SystemDesign/System/Capabilities/Consistency/ConsistencyModel.php:36`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-103: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Benchmarks`
- Finding IDs: DR-0650
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 18 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Benchmarks/Foundation/BenchmarkResult.php:9`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-104: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Benchmarks`
- Finding IDs: DR-0410
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Benchmarks/MicroBenchmarkRunner.php:17`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-105: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Doctor`
- Finding IDs: DR-0404
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Doctor/ExecuteDoctorChecks.php:21`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-106: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ExternalState`
- Finding IDs: DR-0413
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ExternalState/System/Capabilities/Drivers/Redis.php:27`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-107: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/FailureBoundary`
- Finding IDs: DR-0654, DR-0651, DR-0652, DR-0653, DR-0655
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 5 related findings: Constructor has 12 parameters.; Constructor has 8 parameters.; Constructor has 8 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/FailureBoundary/Foundation/FailurePolicy.php:17`; `framework/System/Capabilities/FailureBoundary/Configuration/FailureBoundaryConfiguration.php:12`; `framework/System/Capabilities/FailureBoundary/Foundation/CompiledMethodPolicy.php:12`; `framework/System/Capabilities/FailureBoundary/Foundation/FailureAction.php:15`; `framework/System/Capabilities/FailureBoundary/Capabilities/RunFailurePipeline/RunFailurePipeline.php:27`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-108: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/PreCommit`
- Finding IDs: DR-0405, DR-0406, DR-0407, DR-0408, DR-0409
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 5 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/PreCommit/PreCommitValidator.php:46`; `framework/System/Capabilities/PreCommit/PreCommitValidator.php:47`; `framework/System/Capabilities/PreCommit/PreCommitValidator.php:48`; `framework/System/Capabilities/PreCommit/PreCommit.php:57`; `framework/System/Capabilities/PreCommit/PreCommit.php:58`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-109: Classify constructor responsibility pressure

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Runtime`
- Finding IDs: DR-0646
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 13 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Runtime/Runtime.php:24`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-110: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Runtime`
- Finding IDs: DR-0417, DR-0418, DR-0419, DR-0420
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:48`; `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:49`; `framework/System/Capabilities/Runtime/WarmApplication/HandleWarmRequest.php:50`; `framework/System/Capabilities/Runtime/Worker/WorkerLoop.php:21`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-111: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Security`
- Finding IDs: DR-0414, DR-0415
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Security/PolicyEngine/DefinePolicy.php:34`; `framework/System/Capabilities/Security/Doctor/CheckSecurityRuntime.php:27`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-112: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/BootDsl`
- Finding IDs: DR-0395
- Rule sources: how-to-dependency-injection.md §3.4
- Problem: Null-coalescing fallback instantiates a dependency.
- Why it matters: Fallback construction hides missing dependencies and can turn boot-time errors into runtime behavior changes.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/BootDsl/BootDslBuilder.php:143`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-113: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/BootApplication`
- Finding IDs: DR-0396
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/BootApplication/BuildApplicationState.php:32`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-114: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/HandleIncomingHttp`
- Finding IDs: DR-0401, DR-0402, DR-0403
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/HandleIncomingHttp/MatchHttpRoute.php:29`; `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:34`; `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php:38`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-115: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/RunApplication`
- Finding IDs: DR-0397, DR-0398, DR-0399, DR-0400
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/RunApplication/RunApplication.php:61`; `framework/System/Flows/RunApplication/RunApplication.php:62`; `framework/System/Flows/RunApplication/RunApplication.php:63`; `framework/System/Flows/RunApplication/RunApplication.php:64`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-116: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0390, DR-0391
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface:72`; `framework/System/PublicSurface:74`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-117: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: HIGH
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0393
- Rule sources: how-to-dependency-injection.md §3.4
- Problem: Null-coalescing fallback instantiates a dependency.
- Why it matters: Fallback construction hides missing dependencies and can turn boot-time errors into runtime behavior changes.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface:150`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

### P2 — MEDIUM

#### TODO-118: Route raw filesystem access through an approved boundary

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0046
- Rule sources: how-to-system-security.md §22
- Problem: DataTransfer compiled metadata capabilities use raw file/directory operations classified NEEDS_DESIGN_DECISION.
- Why it matters: Compiled attribute/schema metadata may become hidden disk I/O unless bounded, documented, and tested.
- Risk type: SECURITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php; components/DataStack/DataTransfer/System/Capabilities/DataShapeInspection/CompileDataShapeSchema.php`
- Safe remediation batch: DataTransfer compiled metadata I/O batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-119: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Doctor`
- Finding IDs: DR-0662
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Doctor/CheckRuntimeMode.php:[24, 28, 32, 36, 40]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-120: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ExternalState`
- Finding IDs: DR-0665
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ExternalState/System/PublicSurface/ExternalState.php:[137]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-121: Route raw filesystem access through an approved boundary

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/FailureBoundary`
- Finding IDs: DR-0045
- Rule sources: how-to-system-security.md §22
- Problem: FailureBoundary compiled policy files use raw file operations classified NEEDS_DESIGN_DECISION.
- Why it matters: Compiled policy cache writes/reads may be valid, but the owner and safety boundary are not documented by the gate.
- Risk type: SECURITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/FailureBoundary/Capabilities/WriteCompiledFailurePolicies/WriteCompiledFailurePolicies.php:30-34; CompileFailurePolicies.php:39; ReadCompiledFailurePolicies.php:25-29; CompiledMethodPolicy.php:116`
- Safe remediation batch: FailureBoundary raw I/O design decision batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-122: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/RuntimeIsolation`
- Finding IDs: DR-0664
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/RuntimeIsolation/RuntimeIsolationGuard.php:[11, 12, 13]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-123: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ServeModes`
- Finding IDs: DR-0663
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ServeModes/ServeMode.php:[16, 17, 18, 19, 28, 29]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-124: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/ConfigureRuntime`
- Finding IDs: DR-0659
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/ConfigureRuntime/RuntimeConfiguration.php:[13, 14, 15, 16, 79, 80]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-125: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/HandleIncomingHttp`
- Finding IDs: DR-0661
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/HandleIncomingHttp/ReadIncomingHttpRequest.php:[16]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-126: Runtime-specific dependency classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/RunDoctor`
- Finding IDs: DR-0660
- Rule sources: AGENTS.md §14 runtime-agnostic-framework
- Problem: Framework core references specific runtimes outside narrow runtime adapter/boundary slices.
- Why it matters: Runtime-specific names in core framework units can leak optional adapters into general public/lifecycle APIs.
- Risk type: RUNTIME_SAFETY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/RunDoctor/RunDoctor.php:[70]`
- Safe remediation batch: Runtime-specific dependency classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-runtime-leaks.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-127: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/GraphQL`
- Finding IDs: DR-0551
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 165 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-128: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/SchemaGeneration`
- Finding IDs: DR-0319
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/SchemaGeneration/System/PublicSurface/SchemaGeneration.php:89`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-129: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Container`
- Finding IDs: DR-0103
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Container/System/PublicSurface/Container.php:59,137`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-130: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/DateTime`
- Finding IDs: DR-0099
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/DateTime/System/PublicSurface/SystemClock.php:19,24`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-131: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/FeatureFlags`
- Finding IDs: DR-0100
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/FeatureFlags/System/PublicSurface/FeatureFlags.php:26`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-132: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Pipeline`
- Finding IDs: DR-0461
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 172 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Pipeline/System/PublicSurface/Pipeline.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-133: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Text`
- Finding IDs: DR-0435, DR-0436
- Rule sources: how-to-code-review.md §21
- Problem: 2 related findings: PublicSurface file is 218 lines (>150).; PublicSurface file is 215 lines (>150).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Text/System/PublicSurface/Text.php`; `components/Application/Text/System/PublicSurface/shortcuts.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-134: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Validation`
- Finding IDs: DR-0119
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Validation/System/PublicSurface/Validation.php:18,26`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-135: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/CLI/Console`
- Finding IDs: DR-0226
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/CLI/Console/System/PublicSurface/Console.php:26`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-136: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/CLI/Console`
- Finding IDs: DR-0499, DR-0500
- Rule sources: how-to-code-review.md §21
- Problem: 2 related findings: PublicSurface file is 222 lines (>150).; PublicSurface file is 162 lines (>150).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/CLI/Console/System/PublicSurface/Command.php`; `components/CLI/Console/System/PublicSurface/Console.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-137: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Data`
- Finding IDs: DR-0240, DR-0241, DR-0242, DR-0243, DR-0244, DR-0245, DR-0246, DR-0247, DR-0248, DR-0249, DR-0250, DR-0251, DR-0252
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 13 related findings: PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Data/System/PublicSurface/Json.php:35`; `components/DataStack/Data/System/PublicSurface/Queue.php:18`; `components/DataStack/Data/System/PublicSurface/Sequence.php:23`; `components/DataStack/Data/System/PublicSurface/Set.php:23`; `components/DataStack/Data/System/PublicSurface/Arrhae.php:24`; `components/DataStack/Data/System/PublicSurface/MultiMap.php:23`; `components/DataStack/Data/System/PublicSurface/Matrix.php:19,29`; `components/DataStack/Data/System/PublicSurface/OrderedSet.php:23`; ...
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-138: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools`
- Finding IDs: DR-0236
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/System/PublicSurface/DeveloperTools.php:11`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-139: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Documentation/Api`
- Finding IDs: DR-0227
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Documentation/Api/System/PublicSurface/ApiDocumentation.php:19,24`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-140: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Dx`
- Finding IDs: DR-0232
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Dx/System/PublicSurface/Dx.php:14,19`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-141: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/TestSupport`
- Finding IDs: DR-0233
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/TestSupport/System/PublicSurface/Testing.php:18`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-142: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/AfterResponse`
- Finding IDs: DR-0142
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/AfterResponse/System/PublicSurface/AfterResponse.php:17,23`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-143: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Context`
- Finding IDs: DR-0135
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Context/System/PublicSurface/HttpContext.php:27`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-144: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Request`
- Finding IDs: DR-0128
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Request/System/PublicSurface/Request.php:33`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-145: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Request`
- Finding IDs: DR-0468
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 230 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Request/System/PublicSurface/Request.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-146: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Response`
- Finding IDs: DR-0124
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Response/System/PublicSurface/shortcuts.php:18`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-147: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Router`
- Finding IDs: DR-0484
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 190 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Router/System/PublicSurface/Router.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-148: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/SecureRequest`
- Finding IDs: DR-0482
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 167 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/SecureRequest/System/PublicSurface/SecureRequest.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-149: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Session`
- Finding IDs: DR-0477
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 232 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Session/System/PublicSurface/Session.php`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-150: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity`
- Finding IDs: DR-0388
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/System/PublicSurface/Identity.php:11`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-151: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity/Access`
- Finding IDs: DR-0378
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Access/System/PublicSurface/Access.php:29`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-152: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity/Auth`
- Finding IDs: DR-0357, DR-0358
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: 2 related findings: PublicSurface directly instantiates collaborators (1 `new` expressions detected).; PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Auth/System/PublicSurface/UserRecord.php:19`; `components/Identity/Auth/System/PublicSurface/User.php:19`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-153: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Integration/ObjectStorage`
- Finding IDs: DR-0318
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Integration/ObjectStorage/System/PublicSurface/ObjectStorage.php:23,53`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-154: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations`
- Finding IDs: DR-0195
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/System/PublicSurface/Operations.php:11`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-155: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Notifications`
- Finding IDs: DR-0158
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Notifications/System/PublicSurface/Notifier.php:20`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-156: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Parallelism`
- Finding IDs: DR-0198
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Parallelism/System/PublicSurface/Parallel.php:19,65`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-157: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Resilience`
- Finding IDs: DR-0160
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Resilience/System/PublicSurface/Resilience.php:15,20`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-158: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/RuntimeSupervision`
- Finding IDs: DR-0199
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/RuntimeSupervision/System/PublicSurface/RuntimeSupervision.php:14,19`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-159: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Presentation`
- Finding IDs: DR-0296
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Presentation/System/PublicSurface/Presentation.php:11`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-160: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Security/Cryptography`
- Finding IDs: DR-0306
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Cryptography/System/PublicSurface/Cryptography.php:56,58`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-161: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Security/Secrets`
- Finding IDs: DR-0304
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Secrets/System/PublicSurface/Secrets.php:22`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-162: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ResourceGovernance`
- Finding IDs: DR-0411
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (2 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ResourceGovernance/System/PublicSurface/ResourceGovernor.php:45,77`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-163: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/Runtime`
- Finding IDs: DR-0421
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/Runtime/GracefulShutdown/System/PublicSurface/GracefulShutdown.php:24`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-164: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/RuntimeSafety`
- Finding IDs: DR-0416
- Rule sources: how-to-design-components.md §6.2 PublicSurface Rule
- Problem: PublicSurface directly instantiates collaborators (1 `new` expressions detected).
- Why it matters: PublicSurface should receive and delegate; direct assembly risks public API classes becoming hidden composition roots.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/RuntimeSafety/StatelessBoundary/System/PublicSurface/StatelessBoundary.php:45`
- Safe remediation batch: PublicSurface assembly cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-165: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0639
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 198 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-166: Reduce PublicSurface behavior and assembly pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/PublicSurface`
- Finding IDs: DR-0640
- Rule sources: how-to-code-review.md §21
- Problem: PublicSurface file is 177 lines (>150).
- Why it matters: Large public surfaces require behavior-leak review; public classes may own internal decisions.
- Risk type: PUBLIC_API
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/PublicSurface`
- Safe remediation batch: PublicSurface size classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-167: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/ApiBlueprint`
- Finding IDs: DR-0014
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/ApiBlueprint`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-168: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/Contracts`
- Finding IDs: DR-0015
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/Contracts`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-169: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/Contracts`
- Finding IDs: DR-0001
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/Contracts`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-170: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/GraphQL`
- Finding IDs: DR-0550
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/GraphQL/System/PublicSurface/GraphQLSchema.php:39`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-171: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/OpenAPI`
- Finding IDs: DR-0347, DR-0348
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/OpenAPI/System/Capabilities/SchemaGeneration/BuildOpenApiDocument.php:18`; `components/API/OpenAPI/System/Capabilities/SchemaGeneration/BuildOpenApiDocument.php:19`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-172: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/SchemaGeneration`
- Finding IDs: DR-0320, DR-0321
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/SchemaGeneration/System/Capabilities/ReadDataObjectShape/ReadDataObjectShape.php:17`; `components/API/SchemaGeneration/System/Capabilities/ConvertDataObjectShapeToJsonSchema/ConvertDataObjectShapeToJsonSchema.php:19`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-173: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/API/SchemaGeneration`
- Finding IDs: DR-0018
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/API/SchemaGeneration`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-174: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Cache`
- Finding IDs: DR-0422, DR-0423, DR-0426, DR-0427, DR-0428, DR-0430, DR-0431, DR-0433
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 8 related findings: Constructor has 9 parameters.; Constructor has 9 parameters.; Constructor has 10 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Cache/System/Configuration/CacheConfiguration.php:16`; `components/Application/Cache/System/PublicSurface/AvaxCache.php:34`; `components/Application/Cache/System/Capabilities/Health/CacheHealthStatus.php:17`; `components/Application/Cache/System/Capabilities/Storage/StoreCachedValues/RedisCacheStore.php:30`; `components/Application/Cache/System/Capabilities/Distribution/ReplicateCachedValues/PrimaryReplicaPolicy.php:24`; `components/Application/Cache/System/Capabilities/Distribution/DistributeCachedValues/DetectUnhealthyCacheNode.php:9`; `components/Application/Cache/System/Capabilities/Observability/ObserveCache/CacheOperation.php:13`; `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifestEntry.php:26`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-175: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Cache`
- Finding IDs: DR-0425, DR-0432, DR-0434
- Rule sources: how-to-code-review.md §21
- Problem: 3 related findings: Class/file is 434 lines (>300).; Class/file is 312 lines (>300).; Class/file is 303 lines (>300).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Cache/System/Capabilities/Health/CacheHealthDetector.php`; `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheFreshness.php`; `components/Application/Cache/System/Capabilities/CompiledCache/DistributedCompiledCache/CompiledCacheManifest.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-176: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Config`
- Finding IDs: DR-0114, DR-0115, DR-0116, DR-0117
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 4 related findings: Constructor default parameter instantiates a dependency.; Null-coalescing fallback instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Config/System/Configuration/AppConfigurator.php:23`; `components/Application/Config/System/Configuration/AppConfigurator.php:23`; `components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:37`; `components/Application/Config/System/Capabilities/Configuration/AppConfigurator.php:37`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-177: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Container`
- Finding IDs: DR-0104, DR-0105, DR-0106, DR-0107, DR-0108, DR-0109, DR-0110, DR-0111, DR-0112, DR-0113
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 10 related findings: Null-coalescing fallback instantiates a dependency.; Constructor default parameter instantiates a dependency.; Null-coalescing fallback instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Container/System/Capabilities/Resolution/ResolveDependencies.php:92`; `components/Application/Container/System/Capabilities/Providers/ProviderRegistry.php:26`; `components/Application/Container/System/Capabilities/Execution/Injection/Invocation/FunctionCaller.php:62`; `components/Application/Container/System/Capabilities/Composition/Compilation/ServiceCompiler.php:29`; `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:122`; `components/Application/Container/System/Capabilities/Declaration/Bindings/DependencyRegistry.php:274`; `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:122`; `components/Application/Container/System/Capabilities/Declaration/Bindings/ServiceRegistry.php:273`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-178: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Container`
- Finding IDs: DR-0440, DR-0441, DR-0444, DR-0445, DR-0447, DR-0450, DR-0453, DR-0455, DR-0457, DR-0458, DR-0459, DR-0460
- Rule sources: how-to-code-review.md §21
- Problem: 12 related findings: Class/file is 318 lines (>300).; Class/file is 318 lines (>300).; Class/file is 516 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Container/System/Capabilities/Runtime/ServicePool.php`; `components/Application/Container/System/Capabilities/Runtime/DependencyPool.php`; `components/Application/Container/System/Capabilities/Composition/CreateContainerConfig.php`; `components/Application/Container/System/Capabilities/ContextualContainer/ContextContainer.php`; `components/Application/Container/System/Capabilities/ContainerObservability/Observability/GraphExporter.php`; `components/Application/Container/System/Capabilities/Composition/Compilation/ArtifactMetadata.php`; `components/Application/Container/System/Capabilities/Composition/Compilation/CompileContainer.php`; `components/Application/Container/System/Capabilities/Declaration/Ownership/RegistrationMetadata.php`; ...
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-179: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/DateTime`
- Finding IDs: DR-0438
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 446 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/DateTime/System/Capabilities/CarbonCompat/Date.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-180: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/FeatureFlags`
- Finding IDs: DR-0019
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/FeatureFlags`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-181: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Filesystem`
- Finding IDs: DR-0121
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Filesystem/System/Capabilities/LocalPaths/ResolvePath.php:10`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-182: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Localization`
- Finding IDs: DR-0020
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Localization`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-183: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Storage`
- Finding IDs: DR-0102
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Storage/System/Configuration/Builders/RegisterStorageDisks.php:29`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-184: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Text`
- Finding IDs: DR-0437
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 309 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Text/System/Capabilities/CaseConversion/Str.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-185: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Application/Validation`
- Finding IDs: DR-0462
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 352 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Application/Validation/System/Capabilities/Rules/Validator.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-186: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/CLI/Console`
- Finding IDs: DR-0021
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/CLI/Console`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-187: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Data`
- Finding IDs: DR-0253, DR-0254, DR-0255, DR-0256, DR-0257, DR-0258, DR-0259, DR-0260, DR-0261, DR-0262, DR-0263
- Rule sources: how-to-dependency-injection.md §3.4, how-to-dependency-injection.md §3.4, §6.9
- Problem: 11 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Data/System/Capabilities/Operators/Selection/ReadValueByPath.php:27`; `components/DataStack/Data/System/Capabilities/Forms/JsonForm/Json.php:28`; `components/DataStack/Data/System/Capabilities/Forms/CollectionForm/Collection.php:32`; `components/DataStack/Data/System/Capabilities/Forms/ArrayForm/Arrhae.php:60`; `components/DataStack/Data/System/Capabilities/Values/Temporal/Moment.php:28`; `components/DataStack/Data/System/Capabilities/Codecs/XmlCodec/EncodeXml.php:30`; `components/DataStack/Data/System/Capabilities/Structures/Priority/MinHeap.php:32`; `components/DataStack/Data/System/Capabilities/Structures/Priority/MaxHeap.php:32`; ...
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-188: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Data`
- Finding IDs: DR-0510, DR-0511
- Rule sources: how-to-code-review.md §21
- Problem: 2 related findings: Class/file is 454 lines (>300).; Class/file is 592 lines (>300).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Data/System/Capabilities/Forms/CollectionForm/Collection.php`; `components/DataStack/Data/System/Capabilities/Forms/ArrayForm/Arrhae.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-189: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Data`
- Finding IDs: DR-0022
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Data`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-190: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/DataTransfer`
- Finding IDs: DR-0545, DR-0546
- Rule sources: how-to-code-review.md §21
- Problem: 2 related findings: Class/file is 418 lines (>300).; Class/file is 319 lines (>300).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/DataTransfer/System/Flows/CreateDataObject/CreateDataObject.php`; `components/DataStack/DataTransfer/System/Capabilities/AttributeReading/CompileClassAttributes.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-191: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Database`
- Finding IDs: DR-0527, DR-0531, DR-0534, DR-0536, DR-0537, DR-0538, DR-0539, DR-0542, DR-0543
- Rule sources: how-to-code-review.md §21
- Problem: 9 related findings: Class/file is 394 lines (>300).; Class/file is 326 lines (>300).; Class/file is 510 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Database/System/Capabilities/Observability/SlowQueryDetector.php`; `components/DataStack/Database/System/Capabilities/Transactions/DeadlockDetector.php`; `components/DataStack/Database/System/Capabilities/Transactions/RunTransaction/Transaction.php`; `components/DataStack/Database/System/Capabilities/Query/State/QueryState.php`; `components/DataStack/Database/System/Capabilities/Query/Builder/QueryBuilder.php`; `components/DataStack/Database/System/Capabilities/Query/Execution/QueryOrchestrator.php`; `components/DataStack/Database/System/Capabilities/Query/Grammar/Grammar.php`; `components/DataStack/Database/System/Capabilities/ORM/Persisters/EntityPersister.php`; ...
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-192: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Persistence`
- Finding IDs: DR-0516, DR-0517, DR-0519, DR-0520, DR-0521
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 5 related findings: Constructor has 9 parameters.; Constructor has 9 parameters.; Constructor has 9 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Persistence/System/Capabilities/PersistenceDiagnostics/SlowPersistenceQueryDetector.php:18`; `components/DataStack/Persistence/System/Capabilities/PersistenceDiagnostics/SlowPersistenceQueryDetector.php:138`; `components/DataStack/Persistence/System/Capabilities/PersistenceDiagnostics/SlowPersistenceReport.php:15`; `components/DataStack/Persistence/System/Capabilities/PersistenceDiagnostics/SlowPersistenceStatistics.php:14`; `components/DataStack/Persistence/System/Capabilities/QueryIntent/DataQuery.php:21`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-193: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Persistence`
- Finding IDs: DR-0512, DR-0513, DR-0514, DR-0515, DR-0518
- Rule sources: how-to-code-review.md §21
- Problem: 5 related findings: Class/file is 467 lines (>300).; Class/file is 311 lines (>300).; Class/file is 311 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Persistence/System/Capabilities/ReadOptimization/ReadCache.php`; `components/DataStack/Persistence/System/Capabilities/ReadOptimization/BloomFilter.php`; `components/DataStack/Persistence/System/Capabilities/ReadOptimization/PersistenceBloomFilter.php`; `components/DataStack/Persistence/System/Capabilities/Consistency/EventualConsistency.php`; `components/DataStack/Persistence/System/Capabilities/PersistenceDiagnostics/SlowPersistenceQueryDetector.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-194: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DataStack/Persistence`
- Finding IDs: DR-0024
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DataStack/Persistence`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-195: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/CodeGeneration`
- Finding IDs: DR-0025
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/CodeGeneration`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-196: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Diagnostics`
- Finding IDs: DR-0026
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Diagnostics`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-197: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Documentation/Api`
- Finding IDs: DR-0002
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Documentation/Api`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-198: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/DumpDebugger`
- Finding IDs: DR-0027
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/DumpDebugger`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-199: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/DumpDebugger`
- Finding IDs: DR-0003
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/DumpDebugger`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-200: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/Dx`
- Finding IDs: DR-0028
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/Dx`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-201: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/DeveloperTools/TestSupport`
- Finding IDs: DR-0029
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/DeveloperTools/TestSupport`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-202: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP`
- Finding IDs: DR-0479, DR-0481
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 2 related findings: Constructor has 9 parameters.; Constructor has 9 parameters.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/System/Configuration/HttpConfiguration.php:25`; `components/HTTP/System/Capabilities/Uri/Uri.php:11`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-203: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP`
- Finding IDs: DR-0478
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 308 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/System/Configuration/RouterBootstrapper.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-204: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/AfterResponse`
- Finding IDs: DR-0031
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/AfterResponse`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-205: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Client`
- Finding IDs: DR-0465
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 306 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Client/System/Capabilities/Transports/CurlTransport.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-206: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Client`
- Finding IDs: DR-0004
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Client`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-207: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/ContentNegotiation`
- Finding IDs: DR-0032
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/ContentNegotiation`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-208: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Context`
- Finding IDs: DR-0033
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Context`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-209: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Request`
- Finding IDs: DR-0467, DR-0469, DR-0470, DR-0471, DR-0472, DR-0473
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 6 related findings: Constructor has 11 parameters.; Constructor has 9 parameters.; Constructor has 8 parameters....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Request/System/PublicSurface/Request.php:20`; `components/HTTP/Request/System/Flows/CreateRequestFromGlobals/CreateRequestFromGlobals.php:22`; `components/HTTP/Request/System/Capabilities/Uri/RequestUri.php:11`; `components/HTTP/Request/System/Capabilities/RequestData/RequestData.php:14`; `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php:35`; `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php:74`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-210: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Request`
- Finding IDs: DR-0474
- Rule sources: how-to-code-review.md §21
- Problem: Class/file is 446 lines (>300).
- Why it matters: Large units require responsibility review before they can be considered clean.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Request/System/Capabilities/IncomingRequest/ServerRequest.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-211: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Router`
- Finding IDs: DR-0483
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Router/System/PublicSurface/Router.php:27`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-212: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/Session`
- Finding IDs: DR-0475, DR-0476
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: 2 related findings: Constructor has 8 parameters.; Constructor has 11 parameters.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/Session/System/Configuration/SessionCookieSettings.php:9`; `components/HTTP/Session/System/Foundation/SessionRecord.php:11`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-213: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/URI`
- Finding IDs: DR-0136, DR-0137
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 2 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/URI/System/Capabilities/Parts/Authority.php:24`; `components/HTTP/URI/System/Capabilities/Parts/Authority.php:26`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-214: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/HTTP/URI`
- Finding IDs: DR-0036
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/HTTP/URI`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-215: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity/Auth`
- Finding IDs: DR-0602, DR-0612, DR-0624, DR-0626
- Rule sources: how-to-code-review.md §21
- Problem: 4 related findings: Class/file is 678 lines (>300).; Class/file is 871 lines (>300).; Class/file is 311 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php`; `components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php`; `components/Identity/Auth/System/Capabilities/Identity/Session/SessionIdentity.php`; `components/Identity/Auth/System/Capabilities/Identity/Jwt/JwtIdentity.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-216: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity/ExternalIdentity`
- Finding IDs: DR-0576, DR-0580, DR-0587
- Rule sources: how-to-code-review.md §21
- Problem: 3 related findings: Class/file is 310 lines (>300).; Class/file is 378 lines (>300).; Class/file is 405 lines (>300).
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php`; `components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Runtime/PushAuthorizationRequest/PushAuthorizationRequest.php`; `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-217: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Identity/Security`
- Finding IDs: DR-0005
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Identity/Security`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-218: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Integration/ObjectStorage`
- Finding IDs: DR-0006
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Integration/ObjectStorage`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-219: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/BackgroundProcesses`
- Finding IDs: DR-0007
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/BackgroundProcesses`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-220: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Delivery`
- Finding IDs: DR-0008
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Delivery`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-221: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Filesystem`
- Finding IDs: DR-0197
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Filesystem/System/Capabilities/StorageBackends/LocalStorageAdapter.php:18`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-222: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Filesystem`
- Finding IDs: DR-0038
- Rule sources: how-to-dependency-injection.md §4.0
- Problem: ServiceProvider coverage gate reports real code but no component ServiceProvider.
- Why it matters: A component without a real assembly owner pushes dependency defaults into runtime/public code or leaves boot behavior implicit.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Filesystem`
- Safe remediation batch: ServiceProvider coverage batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-223: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Logging`
- Finding IDs: DR-0166, DR-0167, DR-0168
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Logging/System/Capabilities/Logger/ErrorLogger.php:41`; `components/Operations/Logging/System/Capabilities/Writing/RotatingFileWriter.php:28`; `components/Operations/Logging/System/Capabilities/Writers/RotatingFileWriter.php:38`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-224: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Mail`
- Finding IDs: DR-0489
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 11 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Mail/System/Capabilities/Content/MimeMessage.php:9`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-225: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/MemoryLifecycle`
- Finding IDs: DR-0202
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: Constructor default parameter instantiates a dependency.
- Why it matters: Default `new` hides dependency assembly in runtime objects and can bypass container verification/fail-fast boot.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/MemoryLifecycle/System/Capabilities/Snapshot/MemorySnapshot.php:23`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-226: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/MemoryLifecycle`
- Finding IDs: DR-0009
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/MemoryLifecycle`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-227: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/MessageBus`
- Finding IDs: DR-0488
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 9 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/MessageBus/System/Capabilities/Envelope/MessageEnvelope.php:15`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-228: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Notifications`
- Finding IDs: DR-0485
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Notifications/System/Capabilities/MailNotificationContent.php:12`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-229: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Queue`
- Finding IDs: DR-0487
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Queue/System/Capabilities/Job/JobDefinition.php:9`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-230: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Realtime`
- Finding IDs: DR-0010
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Realtime`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-231: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Resilience`
- Finding IDs: DR-0486
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Resilience/System/Configuration/ResilienceConfiguration.php:9`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-232: Move hidden dependency construction out of runtime/public code

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/Resilience`
- Finding IDs: DR-0161, DR-0162, DR-0163
- Rule sources: how-to-dependency-injection.md §3.4, §6.9
- Problem: 3 related findings: Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.; Constructor default parameter instantiates a dependency.
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: CONFIGURATION_DI
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/Resilience/System/Capabilities/Fallback/FallbackBuilder.php:21`; `components/Operations/Resilience/System/Capabilities/Retry/RetryBuilder.php:16`; `components/Operations/Resilience/System/Capabilities/RateLimiter/RedisRateLimiter.php:34`
- Safe remediation batch: Direct dependency construction cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-233: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/RuntimeSupervision`
- Finding IDs: DR-0490
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/RuntimeSupervision/System/Capabilities/WorkerLifecycle/WorkerRecord.php:9`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-234: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Operations/RuntimeSupervision`
- Finding IDs: DR-0011
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Operations/RuntimeSupervision`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-235: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Security/DataProtection`
- Finding IDs: DR-0012
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/DataProtection`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-236: Test proof batch for component

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/Security/Privacy`
- Finding IDs: DR-0013
- Rule sources: how-to-unit-test.md
- Problem: No component-specific tests detected under tests/.
- Why it matters: Behavior is not proven at the component boundary.
- Risk type: TEST_PROOF
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/Security/Privacy`
- Safe remediation batch: Test proof batch for component
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `vendor/bin/phpunit --no-coverage --filter <component>`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-237: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: COMPONENT
- Unit: `components/SystemDesign`
- Finding IDs: DR-0502, DR-0504, DR-0506, DR-0507, DR-0509
- Rule sources: how-to-code-review.md §21
- Problem: 5 related findings: Class/file is 683 lines (>300).; Class/file is 328 lines (>300).; Class/file is 345 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `components/SystemDesign/System/Capabilities/ScenarioRunner/Scenario.php`; `components/SystemDesign/System/Capabilities/ArchitectureTesting/ArchitectureTest.php`; `components/SystemDesign/System/Capabilities/SchemaValidation/NativeYamlParser.php`; `components/SystemDesign/System/Capabilities/SchemaValidation/SchemaValidator.php`; `components/SystemDesign/System/Capabilities/Messaging/MessagingModel.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-238: Add real ServiceProvider assembly owner

- Status: OPEN
- Severity: MEDIUM
- Root type: CROSS_CUTTING
- Unit: `cross-cutting`
- Finding IDs: DR-0667
- Rule sources: tooling/governance/check-serviceprovider-governance-consistency.php
- Problem: ServiceProvider governance consistency gate says canonical rule is MISSING while reporting wording pass.
- Why it matters: A gate that reports a blocker and pass together weakens evidence truthfulness.
- Risk type: EVIDENCE
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `tooling/governance/check-serviceprovider-governance-consistency.php output`
- Safe remediation batch: Governance gate truthfulness cleanup batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-serviceprovider-governance-consistency.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-239: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/ContainerIntelligence`
- Finding IDs: DR-0647
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 9 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/ContainerIntelligence/ContainerDependencyExplanation.php:15`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-240: Large unit classification batch

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Capabilities/PreCommit`
- Finding IDs: DR-0648, DR-0649, DR-0656, DR-0657
- Rule sources: how-to-code-review.md §21
- Problem: 4 related findings: Class/file is 318 lines (>300).; Class/file is 319 lines (>300).; Class/file is 392 lines (>300)....
- Why it matters: Related findings share the same governance risk and can be remediated safely in one focused batch.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Capabilities/PreCommit/PreCommitValidator.php`; `framework/System/Capabilities/PreCommit/PreCommit.php`; `framework/System/Capabilities/PreCommit/Validators/LegacyCodeValidator.php`; `framework/System/Capabilities/PreCommit/Validators/ScriptRunnerValidator.php`
- Safe remediation batch: Large unit classification batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-241: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/BootDsl`
- Finding IDs: DR-0641
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 9 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/BootDsl/BootDslEngine.php:53`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-242: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/BuildApplication`
- Finding IDs: DR-0643
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 9 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/BuildApplication/Builders/ApplicationBuilder.php:40`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-243: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Configuration/Foundation`
- Finding IDs: DR-0642
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 9 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Configuration/Foundation/RuntimeConfiguration.php:14`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-244: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/CreateApplication`
- Finding IDs: DR-0644
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 10 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/CreateApplication/CreateApplication.php:41`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

#### TODO-245: Classify constructor responsibility pressure

- Status: OPEN
- Severity: MEDIUM
- Root type: FRAMEWORK
- Unit: `framework/System/Flows/RunApplication`
- Finding IDs: DR-0645
- Rule sources: how-to-modern-php-attributes-di.md constructor bloat
- Problem: Constructor has 8 parameters.
- Why it matters: Large constructors obscure responsibility boundaries and make DI/assembly changes risky.
- Risk type: MAINTAINABILITY
- Target state: The unit has explicit ownership, dependencies are assembled in approved Configuration/ServiceProvider contexts, and behavior remains covered by focused tests.
- Non-goals: No feature work; no unrelated renames/moves; no public API change unless explicitly approved; no broad mechanical rewrite; no fake GREEN.
- Files likely involved: `framework/System/Flows/RunApplication/RunApplication.php:45`
- Safe remediation batch: Constructor responsibility review batch
- Tests required: Add or update focused behavior/negative tests for the touched boundary; do not rely on smoke-only proof.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/<cleanup-evidence>.md` with command output and finding closure notes.
- Commit gate: no unrelated dirty files staged; no production/test files outside this TODO batch; validation evidence captured; no remaining BLOCKER/HIGH/MEDIUM output issue for this batch.
- Owner: AvaX maintainer
- Expiry/Target: next cleanup batch unless explicitly deferred
- Done when: all listed finding IDs are closed or explicitly reclassified with owner/expiry, focused tests pass, relevant gates pass, and evidence links the exact validation output.

### P3 — LOW

No active TODOs at this severity.

### Accepted YELLOW

- Reason accepted: legacy semantic PHPDoc ratchet has 0 touched/new blockers in this review-only pass.
- Owner: AvaX governance owner.
- Expiry/Target: next touched-file pass or dedicated documentation hardening phase.
- Next action: each cleanup prompt must upgrade PHPDoc for touched PublicSurface/runtime/security-sensitive files.
- Risk if ignored: architecture readability and AI comprehension debt accumulates.

### Accepted Exceptions

No accepted exceptions were created by this review.

## Component Backlog

### components/API/ApiBlueprint
- TODO-003
- TODO-051
- TODO-052
- TODO-167

### components/API/Contracts
- TODO-004
- TODO-053
- TODO-168
- TODO-169

### components/API/GraphQL
- TODO-005
- TODO-054
- TODO-055
- TODO-127
- TODO-170

### components/API/OpenAPI
- TODO-006
- TODO-056
- TODO-171

### components/API/SchemaGeneration
- TODO-128
- TODO-172
- TODO-173

### components/Application/Cache
- TODO-007
- TODO-008
- TODO-057
- TODO-174
- TODO-175

### components/Application/Config
- TODO-176

### components/Application/Container
- TODO-058
- TODO-129
- TODO-177
- TODO-178

### components/Application/DateTime
- TODO-130
- TODO-179

### components/Application/FeatureFlags
- TODO-131
- TODO-180

### components/Application/Filesystem
- TODO-009
- TODO-181

### components/Application/Localization
- TODO-182

### components/Application/Pipeline
- TODO-132

### components/Application/Storage
- TODO-010
- TODO-183

### components/Application/Text
- TODO-011
- TODO-133
- TODO-184

### components/Application/Validation
- TODO-059
- TODO-134
- TODO-185

### components/CLI/Console
- TODO-060
- TODO-135
- TODO-136
- TODO-186

### components/DataStack/Data
- TODO-137
- TODO-187
- TODO-188
- TODO-189

### components/DataStack/DataTransfer
- TODO-012
- TODO-061
- TODO-062
- TODO-063
- TODO-118
- TODO-190

### components/DataStack/Database
- TODO-013
- TODO-064
- TODO-065
- TODO-191

### components/DataStack/Persistence
- TODO-014
- TODO-066
- TODO-192
- TODO-193
- TODO-194

### components/DeveloperTools
- TODO-138

### components/DeveloperTools/CodeGeneration
- TODO-195

### components/DeveloperTools/Diagnostics
- TODO-015
- TODO-196

### components/DeveloperTools/Documentation/Api
- TODO-067
- TODO-139
- TODO-197

### components/DeveloperTools/DumpDebugger
- TODO-198
- TODO-199

### components/DeveloperTools/Dx
- TODO-140
- TODO-200

### components/DeveloperTools/TestSupport
- TODO-141
- TODO-201

### components/Foundation/CallableSerialization
- TODO-016
- TODO-068
- TODO-069

### components/HTTP
- TODO-017
- TODO-202
- TODO-203

### components/HTTP/AfterResponse
- TODO-142
- TODO-204

### components/HTTP/Client
- TODO-018
- TODO-070
- TODO-071
- TODO-205
- TODO-206

### components/HTTP/ContentNegotiation
- TODO-019
- TODO-207

### components/HTTP/Context
- TODO-143
- TODO-208

### components/HTTP/Dispatcher
- TODO-072

### components/HTTP/Request
- TODO-073
- TODO-144
- TODO-145
- TODO-209
- TODO-210

### components/HTTP/Response
- TODO-146

### components/HTTP/Router
- TODO-020
- TODO-074
- TODO-147
- TODO-211

### components/HTTP/SecureRequest
- TODO-021
- TODO-148

### components/HTTP/Security
- TODO-075

### components/HTTP/Session
- TODO-022
- TODO-076
- TODO-149
- TODO-212

### components/HTTP/System
- TODO-023

### components/HTTP/URI
- TODO-213
- TODO-214

### components/Identity
- TODO-150

### components/Identity/Access
- TODO-077
- TODO-151

### components/Identity/Auth
- TODO-001
- TODO-078
- TODO-079
- TODO-152
- TODO-215

### components/Identity/Credentials
- TODO-080
- TODO-081

### components/Identity/ExternalIdentity
- TODO-082
- TODO-083
- TODO-216

### components/Identity/Security
- TODO-084
- TODO-217

### components/Identity/Tenancy
- TODO-085
- TODO-086

### components/Identity/Tokens
- TODO-024
- TODO-087
- TODO-088

### components/Integration/ObjectStorage
- TODO-153
- TODO-218

### components/Operations
- TODO-154

### components/Operations/ApplicationWorkflow
- TODO-025
- TODO-026
- TODO-027
- TODO-089
- TODO-090

### components/Operations/BackgroundProcesses
- TODO-028
- TODO-091
- TODO-219

### components/Operations/Concurrency
- TODO-029
- TODO-092

### components/Operations/Delivery
- TODO-030
- TODO-220

### components/Operations/Events
- TODO-031
- TODO-093

### components/Operations/Filesystem
- TODO-032
- TODO-221
- TODO-222

### components/Operations/Logging
- TODO-223

### components/Operations/Mail
- TODO-033
- TODO-094
- TODO-224

### components/Operations/MemoryLifecycle
- TODO-034
- TODO-225
- TODO-226

### components/Operations/MessageBus
- TODO-035
- TODO-227

### components/Operations/Notifications
- TODO-095
- TODO-155
- TODO-228

### components/Operations/Observability
- TODO-036

### components/Operations/Parallelism
- TODO-156

### components/Operations/Queue
- TODO-037
- TODO-096
- TODO-229

### components/Operations/Realtime
- TODO-038
- TODO-230

### components/Operations/Resilience
- TODO-157
- TODO-231
- TODO-232

### components/Operations/RuntimeSupervision
- TODO-097
- TODO-158
- TODO-233
- TODO-234

### components/Operations/Scheduler
- TODO-039

### components/Operations/Tasks
- TODO-040

### components/Presentation
- TODO-159

### components/Security/Cryptography
- TODO-160

### components/Security/DataProtection
- TODO-041
- TODO-098
- TODO-235

### components/Security/Privacy
- TODO-042
- TODO-099
- TODO-236

### components/Security/Redaction
- TODO-043
- TODO-100

### components/Security/Secrets
- TODO-101
- TODO-161

### components/SystemDesign
- TODO-044
- TODO-045
- TODO-102
- TODO-237

## Framework Backlog

### framework/System/Capabilities/Benchmarks
- TODO-103
- TODO-104

### framework/System/Capabilities/ContainerIntelligence
- TODO-239

### framework/System/Capabilities/Doctor
- TODO-105
- TODO-119

### framework/System/Capabilities/ExternalState
- TODO-046
- TODO-106
- TODO-120

### framework/System/Capabilities/FailureBoundary
- TODO-107
- TODO-121

### framework/System/Capabilities/PreCommit
- TODO-108
- TODO-240

### framework/System/Capabilities/ResourceGovernance
- TODO-162

### framework/System/Capabilities/Runtime
- TODO-109
- TODO-110
- TODO-163

### framework/System/Capabilities/RuntimeIsolation
- TODO-122

### framework/System/Capabilities/RuntimeSafety
- TODO-164

### framework/System/Capabilities/Security
- TODO-111

### framework/System/Capabilities/ServeModes
- TODO-123

### framework/System/Configuration/BootDsl
- TODO-112
- TODO-241

### framework/System/Configuration/BuildApplication
- TODO-242

### framework/System/Configuration/Builders
- TODO-002

### framework/System/Configuration/ConfigureRuntime
- TODO-124

### framework/System/Configuration/Foundation
- TODO-243

### framework/System/Flows/BootApplication
- TODO-113

### framework/System/Flows/CreateApplication
- TODO-244

### framework/System/Flows/HandleIncomingHttp
- TODO-114
- TODO-125

### framework/System/Flows/RunApplication
- TODO-115
- TODO-245

### framework/System/Flows/RunDoctor
- TODO-126

### framework/System/PublicSurface
- TODO-047
- TODO-048

### framework/System/PublicSurface
- TODO-049
- TODO-116
- TODO-165

### framework/System/PublicSurface
- TODO-050
- TODO-117
- TODO-166

## Cross-Cutting Backlog

- TODO-238

## Cleanup Execution Order

Order is based only on severity, security/runtime risk, public API risk, architectural drift, test weakness, and ease of safe remediation.

1. TODO-001 — BLOCKER `components/Identity/Auth`: Split and classify AuthBuilder assembly responsibility
2. TODO-002 — HIGH `framework/System/Configuration/Builders`: Route raw filesystem access through an approved boundary
3. TODO-003 — HIGH `components/API/ApiBlueprint`: Reduce PublicSurface behavior and assembly pressure
4. TODO-004 — HIGH `components/API/Contracts`: Reduce PublicSurface behavior and assembly pressure
5. TODO-005 — HIGH `components/API/GraphQL`: Reduce PublicSurface behavior and assembly pressure
6. TODO-006 — HIGH `components/API/OpenAPI`: Reduce PublicSurface behavior and assembly pressure
7. TODO-007 — HIGH `components/Application/Cache`: Reduce PublicSurface behavior and assembly pressure
8. TODO-008 — HIGH `components/Application/Cache`: Reduce PublicSurface behavior and assembly pressure
9. TODO-009 — HIGH `components/Application/Filesystem`: Reduce PublicSurface behavior and assembly pressure
10. TODO-010 — HIGH `components/Application/Storage`: Reduce PublicSurface behavior and assembly pressure
11. TODO-011 — HIGH `components/Application/Text`: Reduce PublicSurface behavior and assembly pressure
12. TODO-012 — HIGH `components/DataStack/DataTransfer`: Reduce PublicSurface behavior and assembly pressure
13. TODO-013 — HIGH `components/DataStack/Database`: Reduce PublicSurface behavior and assembly pressure
14. TODO-014 — HIGH `components/DataStack/Persistence`: Reduce PublicSurface behavior and assembly pressure
15. TODO-015 — HIGH `components/DeveloperTools/Diagnostics`: Reduce PublicSurface behavior and assembly pressure
16. TODO-016 — HIGH `components/Foundation/CallableSerialization`: Reduce PublicSurface behavior and assembly pressure
17. TODO-017 — HIGH `components/HTTP`: Reduce PublicSurface behavior and assembly pressure
18. TODO-018 — HIGH `components/HTTP/Client`: Reduce PublicSurface behavior and assembly pressure
19. TODO-019 — HIGH `components/HTTP/ContentNegotiation`: Reduce PublicSurface behavior and assembly pressure
20. TODO-020 — HIGH `components/HTTP/Router`: Reduce PublicSurface behavior and assembly pressure
21. TODO-021 — HIGH `components/HTTP/SecureRequest`: Reduce PublicSurface behavior and assembly pressure
22. TODO-022 — HIGH `components/HTTP/Session`: Reduce PublicSurface behavior and assembly pressure
23. TODO-023 — HIGH `components/HTTP/System`: Broken reference semantics batch
24. TODO-024 — HIGH `components/Identity/Tokens`: Reduce PublicSurface behavior and assembly pressure
25. TODO-025 — HIGH `components/Operations/ApplicationWorkflow`: Broken reference semantics batch
26. TODO-026 — HIGH `components/Operations/ApplicationWorkflow`: Reduce PublicSurface behavior and assembly pressure
27. TODO-027 — HIGH `components/Operations/ApplicationWorkflow`: Reduce PublicSurface behavior and assembly pressure
28. TODO-028 — HIGH `components/Operations/BackgroundProcesses`: Reduce PublicSurface behavior and assembly pressure
29. TODO-029 — HIGH `components/Operations/Concurrency`: Reduce PublicSurface behavior and assembly pressure
30. TODO-030 — HIGH `components/Operations/Delivery`: Reduce PublicSurface behavior and assembly pressure
31. TODO-031 — HIGH `components/Operations/Events`: Reduce PublicSurface behavior and assembly pressure
32. TODO-032 — HIGH `components/Operations/Filesystem`: Reduce PublicSurface behavior and assembly pressure
33. TODO-033 — HIGH `components/Operations/Mail`: Reduce PublicSurface behavior and assembly pressure
34. TODO-034 — HIGH `components/Operations/MemoryLifecycle`: Reduce PublicSurface behavior and assembly pressure
35. TODO-035 — HIGH `components/Operations/MessageBus`: Reduce PublicSurface behavior and assembly pressure
36. TODO-036 — HIGH `components/Operations/Observability`: Reduce PublicSurface behavior and assembly pressure
37. TODO-037 — HIGH `components/Operations/Queue`: Reduce PublicSurface behavior and assembly pressure
38. TODO-038 — HIGH `components/Operations/Realtime`: Reduce PublicSurface behavior and assembly pressure
39. TODO-039 — HIGH `components/Operations/Scheduler`: Reduce PublicSurface behavior and assembly pressure
40. TODO-040 — HIGH `components/Operations/Tasks`: Reduce PublicSurface behavior and assembly pressure

## Next Recommended Batch

- Batch title: Split and classify AuthBuilder assembly responsibility
- Unit/component: `components/Identity/Auth`
- Exact TODO IDs: TODO-001
- Why this batch first: it has the highest severity under the quality/risk ordering and can be scoped to one unit.
- Strict scope: close only the listed finding IDs for this TODO.
- Forbidden changes: no feature work, no roadmap work, no unrelated renames, no public API changes unless explicitly approved.
- Validation commands: php tooling/governance/check-large-unit-thresholds.php
- Evidence path: `.agents/management/evidence/generated/discipline-review/<next-batch-evidence>.md`
- Expected final status: lower severity for this unit after evidence; not GREEN unless all unit findings are closed.

## Archived / Superseded Previous fix-this.md Items

- Preserved: previous file content summarized in `.agents/management/evidence/generated/discipline-review/fix-this-before-discipline-review.md`.
- Rewritten: direct-instantiation, AuthBuilder, ServiceProvider coverage, and PHPDoc ratchet items.
- Merged: stale direct-instantiation details merged into current `DR-*` findings.
- Obsolete: old runtime-composition leak count; current runtime-composition gate passed.
- Already fixed: old runtime-composition blocker class from 2026-05-15.
- Deferred to evidence: old area summaries and production-readiness framing.
