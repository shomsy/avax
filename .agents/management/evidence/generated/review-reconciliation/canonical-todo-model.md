# Canonical TODO Model

Generated: 2026-05-20T00:00:00+02:00

Every TODO links to clusters and source finding IDs. Full source-to-TODO coverage is in `source-finding-coverage.md`.


## TODO-001: Harden serialized payload boundaries before any cache/callable cleanup
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-001
- Source finding IDs: SCR-0079, SCR-0084, SCR-0426, HTD-0079, HTD-0084, HTD-0426, SAI-0024, SAI-0088, SAI-0091, SAI-0095, SAI-0239, OLD-FIX-174
- Affected units: `components/Application/Cache`, `Cache`, `Foundation/CallableSerialization`, `Application/Cache`, `Security/Cryptography`, `CROSS_CUTTING`
- Affected files: PhpCacheSerializer.php; SerializeClosureThroughLibrary.php; RedisCacheStore.php; DecryptValue.php verification
- Problem: Confirmed unsafe or partially unsafe deserialization paths exist in cache/callable serialization; DecryptValue fallback requires exploitability verification.
- Why it matters: Untrusted serialized payloads can become object injection/RCE surfaces or unsafe backward-compatibility paths.
- Target state: Only explicitly allowed classes or non-PHP serialization cross trust boundaries; fallbacks are fail-closed and tested.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: One security hardening batch limited to serialization/deserialization boundaries.
- Safe batch boundary: One security hardening batch limited to serialization/deserialization boundaries.
- Tests required: negative tests for malicious serialized objects, invalid payloads, checksum failure, Redis/cache payload tampering, and DecryptValue fallback behavior.
- Validation commands: `vendor/bin/phpunit --filter "CacheSerializer|CallableSerialization|DecryptValue|RedisCacheStore" --no-coverage && php tooling/refactor/check-broken-reference-semantics.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-002: Fix compiled container namespace emission before container remediation
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-002
- Source finding IDs: SCR-0451, SCR-0452, HTD-0451, HTD-0452, SAI-0022, SAI-0023, SAI-0048, SAI-0051, SAI-0052, OLD-FIX-058, OLD-FIX-178
- Affected units: components/Application/Container
- Affected files: MethodEmitter.php; CompileContainer.php
- Problem: Compiled container source emits old `Avax\Container\...` namespace references while current code lives under `Avax\Components\Application\Container\System\...`.
- Why it matters: Generated container artifacts can fail at runtime even if static source validation passes.
- Target state: Generated compiled-container code uses current namespaces or a documented compatibility layer with tests.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Container compilation namespace-only batch; no container behavior redesign.
- Safe batch boundary: Container compilation namespace-only batch; no container behavior redesign.
- Tests required: compiled-container generation regression test and runtime include/resolve smoke with generated artifact.
- Validation commands: `vendor/bin/phpunit --filter "CompileContainer|MethodEmitter|CompiledContainer" --no-coverage && php tooling/refactor/check-namespace-drift.php && php tooling/refactor/check-broken-reference-semantics.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-003: Unify CSRF/session authority and remove direct session mutation conflicts
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-003
- Source finding IDs: SAI-0057, SAI-0058, SAI-0059, SAI-0060, SAI-0061, SAI-0062, SAI-0063, SAI-0064, SAI-0084
- Affected units: components/HTTP/Session; components/HTTP/Security; components/HTTP
- Affected files: SessionScope.php; NativeSessionStore.php; CsrfToken.php; CsrfTokens.php; CsrfTokenGenerator.php; HTTP security shortcuts
- Problem: Multiple classes independently start/mutate PHP sessions and use conflicting CSRF token keys (`_token`, `_csrf_token`, `_csrf_tokens`).
- Why it matters: Session/CSRF split authority can invalidate tokens, bypass lifecycle controls, and leak state in long-lived workers.
- Target state: One session authority owns lifecycle and one CSRF token model delegates through it.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: CSRF/session authority consolidation; no unrelated HTTP API expansion.
- Safe batch boundary: CSRF/session authority consolidation; no unrelated HTTP API expansion.
- Tests required: negative CSRF validation tests, token rotation/regeneration tests, session lifecycle tests, duplicate helper load test.
- Validation commands: `vendor/bin/phpunit --filter "Csrf|Session" --no-coverage && php tooling/refactor/check-runtime-composition-leaks.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-004: Close dynamic class-loading execution paths at payload and recovery boundaries
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-006
- Source finding IDs: SAI-0013, SAI-0014, SAI-0038, SAI-0039, SAI-0040, SAI-0041, SAI-0042, SAI-0053, SAI-0066, SAI-0067, SAI-0068, SAI-0070, SAI-0071, SAI-0072, SAI-0073, SAI-0074, SAI-0079, SAI-0121, SAI-0125, SAI-0130, SAI-0131, SAI-0148, SAI-0153, SAI-0165, SAI-0238
- Affected units: `DeveloperTools/TestSupport/Capabilities/ContractTesting/Verification/ContractVerifier.php`, `DeveloperTools/DumpDebugger`, `Container/Foundation/SimpleContainer.php`, `Container/Foundation/FrozenContainer.php`, `Container/Capabilities/ResolveCallable/ResolveCallable.php`, `Container/Capabilities/Declaration/Bindings/DependencyRegistry.php`, `Container/Capabilities/Declaration/Bindings/ServiceRegistry.php`, `Container/* (multiple files)`, `DataStack/Database`, `DataStack/DataTransfer`, `Queue`, `FailureBoundary/Capabilities/RunRecoveryAction/RunRecoveryAction.php`, ... (17 units total)
- Affected files: QueueWorker.php; RunRecoveryAction.php; RunFallbackAction.php; migration/seeder dynamic instantiation; Container class_exists+new sites
- Problem: Runtime class strings and `class_exists()+new` paths instantiate classes outside verified container/configuration ownership.
- Why it matters: Dynamic class loading from payload or policy strings is a security/runtime boundary until allowlisted and container-mediated.
- Target state: Class-string inputs are allowlisted, typed, container-resolved, and validated before execution.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Start with QueueWorker and FailureBoundary recovery/fallback paths; track container internals separately inside same cluster.
- Safe batch boundary: Start with QueueWorker and FailureBoundary recovery/fallback paths; track container internals separately inside same cluster.
- Tests required: negative tests for unknown class, wrong interface, payload class injection, missing fallback/recovery class.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "QueueWorker|FailureBoundary|Migration|Seeder" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-005: Remove worker-unsafe static secret and security runtime state
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-004
- Source finding IDs: SCR-0146, SCR-0303, SCR-0304, SCR-0411, SCR-0412, SCR-0415, SCR-0664, HTD-0146, HTD-0303, HTD-0304, HTD-0411, HTD-0412, HTD-0415, HTD-0664, SAI-0090, SAI-0106, SAI-0135, SAI-0136, SAI-0137, SAI-0138, SAI-0140, SAI-0157, SAI-0170, SAI-0211, OLD-FIX-046, OLD-FIX-093, OLD-FIX-101, OLD-FIX-106, OLD-FIX-120, OLD-FIX-161, OLD-FIX-164
- Affected units: `components/Operations/Events`, `components/Security/Secrets`, `framework/System/Capabilities/RuntimeSafety`, `framework/System/Capabilities/ExternalState`, `Security/Secrets`, `Events`, `ExternalState`, `ResourceGovernance/PublicSurface/ResourceGovernor.php`, `RuntimeSafety/StatelessBoundary/PublicSurface/StatelessBoundary.php`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php`, `framework`
- Affected files: Secrets.php; Diagnostics.php; FailureBoundary.php; ExternalState.php; ResourceGovernor.php; GlobalEventListenerState.php; related static runtime holders
- Problem: Security/runtime public surfaces and facades hold mutable static state, including secret store and request diagnostics.
- Why it matters: Static state can leak users, secrets, diagnostics, or runtime state across requests in long-lived workers.
- Target state: State is request-scoped or runtime-owned, reset proof is automatic, and security state is never lazy global mutable state.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Security/runtime static-state batch; first Secrets, Diagnostics, FailureBoundary.
- Safe batch boundary: Security/runtime static-state batch; first Secrets, Diagnostics, FailureBoundary.
- Tests required: long-lived worker two-request leakage tests, reset tests, secret overwrite/isolation tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Secrets|Diagnostics|FailureBoundary|ExternalState|StateReset" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-006: Move framework public entrypoint object-graph assembly out of runtime/PublicSurface
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-007
- Source finding IDs: SCR-0388, SCR-0391, SCR-0393, SCR-0410, SCR-0420, HTD-0388, HTD-0391, HTD-0392, HTD-0393, HTD-0394, HTD-0410, HTD-0420, SAI-0167, SAI-0168, SAI-0169, SAI-0171, SAI-0172, SAI-0173, SAI-0174, SAI-0176, SAI-0177, SAI-0213, SAI-0214, SAI-0225, SAI-0226, SAI-0230, OLD-FIX-047, OLD-FIX-048, OLD-FIX-049, OLD-FIX-050, OLD-FIX-162, OLD-FIX-163, OLD-FIX-165, OLD-FIX-166
- Affected units: framework/System/PublicSurface; framework/System/Flows/RunApplication; framework/System/Flows/CreateApplication
- Affected files: Avax.php; BootDsl.php; App.php; RunApplication.php; CreateApplication.php; BootDslEngine.php
- Problem: Framework entrypoints and flows create runtime object graphs with `new`, including request dispatch pipeline creation.
- Why it matters: Public entrypoints become hidden composition roots, weakening testability, worker safety, and DI discipline.
- Target state: Configuration/builders assemble; PublicSurface delegates; runtime flows receive ready collaborators.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Framework entrypoint composition extraction; no API behavior changes without explicit approval.
- Safe batch boundary: Framework entrypoint composition extraction; no API behavior changes without explicit approval.
- Tests required: Avax::create, BootDsl::create, App::handle, RunApplication pipeline regression tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-runtime-composition-leaks.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-007: Split AuthBuilder into bounded configuration responsibilities
- Status: OPEN
- Priority: P0 BLOCKER
- Normalized severity: BLOCKER
- Source clusters: CLUSTER-008
- Source finding IDs: SCR-0368, SCR-0602, HTD-0368, HTD-0602, SAI-0099, OLD-FIX-001, OLD-FIX-079
- Affected units: components/Identity/Auth
- Affected files: components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php
- Problem: AuthBuilder is 797 lines and is the highest canonical non-security BLOCKER.
- Why it matters: A builder this large becomes a hidden container and makes auth assembly hard to verify safely.
- Target state: Auth assembly is divided into named configuration responsibilities with unchanged public behavior and focused tests.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: AuthBuilder split/classification only; no auth feature changes.
- Safe batch boundary: AuthBuilder split/classification only; no auth feature changes.
- Tests required: existing AuthBuilder behavior tests plus focused graph assembly tests.
- Validation commands: `php tooling/governance/check-large-unit-thresholds.php && vendor/bin/phpunit --filter "AuthBuilder|Auth" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-008: Retire remaining static mutable PublicSurface/runtime state by ownership slice
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-005
- Source finding IDs: SAI-0001, SAI-0002, SAI-0027, SAI-0028, SAI-0029, SAI-0030, SAI-0031, SAI-0032, SAI-0033, SAI-0043, SAI-0054, SAI-0069, SAI-0100, SAI-0101, SAI-0102, SAI-0103, SAI-0104, SAI-0105, SAI-0107, SAI-0108, SAI-0128, SAI-0194, SAI-0236
- Affected units: `DeveloperTools/Diagnostics`, `API/SchemaGeneration`, `Cache/PublicSurface/Cache.php`, `Cache/PublicSurface/CompiledCache.php`, `Container/PublicSurface/Container.php`, `Storage/PublicSurface/Storage.php`, `Pipeline/PublicSurface/Pipeline.php`, `Facade/Foundation/BaseFacade.php`, `FeatureFlags/PublicSurface/FeatureFlags.php`, `Container`, `Cache/Storage/Pipeline/Validation/Facade/FeatureFlags`, `DataStack/DataTransfer`, ... (21 units total)
- Affected files: multiple
- Problem: Non-security components and runtime helpers use static mutable/lazy singleton state.
- Why it matters: Manual reset patterns are fragile under workers.
- Target state: Request/runtime state is scoped and reset by owner, not shared static singletons.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Batch by owner group: Application, Operations, DataStack, framework capabilities.
- Safe batch boundary: Batch by owner group: Application, Operations, DataStack, framework capabilities.
- Tests required: two-request leak tests per touched owner.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-009: Reduce API and DeveloperTools PublicSurface construction pressure
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0225, SCR-0226, SCR-0231, SCR-0232, SCR-0233, SCR-0234, SCR-0235, SCR-0236, SCR-0238, SCR-0295, SCR-0317, SCR-0318, SCR-0321, SCR-0322, SCR-0327, SCR-0340, SCR-0341, SCR-0343, SCR-0345, HTD-0225, HTD-0226, HTD-0231, HTD-0232, HTD-0233, HTD-0234, HTD-0235, HTD-0236, HTD-0238, HTD-0295, HTD-0317, HTD-0318, HTD-0321, HTD-0322, HTD-0327, HTD-0340, HTD-0341, HTD-0343, HTD-0345, SAI-0003, SAI-0004, SAI-0005, SAI-0006, SAI-0007, SAI-0008, SAI-0009, SAI-0034, SAI-0035, SAI-0036, SAI-0037, SAI-0089, SAI-0109, SAI-0110, SAI-0111, SAI-0112, SAI-0113, SAI-0114, SAI-0115, SAI-0116, SAI-0117, SAI-0118, SAI-0119, SAI-0120, SAI-0129, OLD-FIX-003, OLD-FIX-004, OLD-FIX-005, OLD-FIX-006, OLD-FIX-015, OLD-FIX-016, OLD-FIX-044, OLD-FIX-045, OLD-FIX-127, OLD-FIX-128, OLD-FIX-135, OLD-FIX-136, OLD-FIX-138, OLD-FIX-139, OLD-FIX-140, OLD-FIX-141, OLD-FIX-153, ... (81 total; full mapping in source-finding-coverage.md)
- Affected units: components/API; components/DeveloperTools
- Affected files: multiple
- Problem: API/DeveloperTools PublicSurface classes directly instantiate collaborators.
- Why it matters: Public API classes should receive/delegate and remain stable.
- Target state: Assembly moves to Configuration/ServiceProvider/builder with behavior preserved.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: API + DeveloperTools PublicSurface batch only.
- Safe batch boundary: API + DeveloperTools PublicSurface batch only.
- Tests required: public surface behavior tests for GraphQL/OpenAPI/Contracts/ApiBlueprint/Diagnostics.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-010: Reduce Application component PublicSurface construction pressure
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0047, SCR-0048, SCR-0049, SCR-0050, SCR-0051, SCR-0052, SCR-0053, SCR-0054, SCR-0097, SCR-0098, SCR-0099, SCR-0100, SCR-0102, SCR-0118, SCR-0119, HTD-0047, HTD-0048, HTD-0049, HTD-0050, HTD-0051, HTD-0052, HTD-0053, HTD-0054, HTD-0097, HTD-0098, HTD-0099, HTD-0100, HTD-0102, HTD-0118, HTD-0119, OLD-FIX-007, OLD-FIX-008, OLD-FIX-009, OLD-FIX-010, OLD-FIX-011, OLD-FIX-129, OLD-FIX-130, OLD-FIX-131, OLD-FIX-132, OLD-FIX-133, OLD-FIX-134
- Affected units: components/Application/*
- Affected files: multiple
- Problem: Application components such as Cache, Storage, Filesystem, Validation, Text construct collaborators from PublicSurface.
- Why it matters: Application facades become hidden composition roots and hard to test.
- Target state: Configuration owns assembly; PublicSurface receives/delegates.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Split into Cache first, then Storage/Filesystem/Text/Validation.
- Safe batch boundary: Split into Cache first, then Storage/Filesystem/Text/Validation.
- Tests required: focused public behavior tests per touched component.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-011: Reduce HTTP PublicSurface construction pressure
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0121, SCR-0122, SCR-0123, SCR-0125, SCR-0127, SCR-0134, SCR-0139, SCR-0141, SCR-0142, SCR-0143, SCR-0145, HTD-0121, HTD-0122, HTD-0123, HTD-0125, HTD-0127, HTD-0134, HTD-0139, HTD-0141, HTD-0142, HTD-0143, HTD-0145, OLD-FIX-017, OLD-FIX-018, OLD-FIX-019, OLD-FIX-020, OLD-FIX-021, OLD-FIX-022, OLD-FIX-142, OLD-FIX-143, OLD-FIX-144, OLD-FIX-145, OLD-FIX-146, OLD-FIX-147, OLD-FIX-148, OLD-FIX-149
- Affected units: components/HTTP/*
- Affected files: multiple
- Problem: HTTP public surfaces directly construct request/client/session/router/response collaborators.
- Why it matters: HTTP is runtime-sensitive and public API construction hides failure modes.
- Target state: HTTP assembly is explicit and testable outside public entrypoints.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: HTTP Client/Session/Router/Request/Response/SecureRequest by safe slice.
- Safe batch boundary: HTTP Client/Session/Router/Request/Response/SecureRequest by safe slice.
- Tests required: HTTP public behavior and negative boundary tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php && vendor/bin/phpunit --filter "HTTP|Http" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-012: Reduce Operations PublicSurface construction pressure
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0149, SCR-0150, SCR-0157, SCR-0158, SCR-0159, SCR-0163, SCR-0164, SCR-0168, SCR-0173, SCR-0174, SCR-0176, SCR-0178, SCR-0181, SCR-0182, SCR-0183, SCR-0184, SCR-0188, SCR-0189, SCR-0191, SCR-0192, SCR-0194, SCR-0195, SCR-0197, SCR-0198, SCR-0200, SCR-0202, SCR-0204, SCR-0205, HTD-0149, HTD-0150, HTD-0157, HTD-0158, HTD-0159, HTD-0163, HTD-0164, HTD-0168, HTD-0173, HTD-0174, HTD-0176, HTD-0178, HTD-0181, HTD-0182, HTD-0183, HTD-0184, HTD-0188, HTD-0189, HTD-0191, HTD-0192, HTD-0194, HTD-0195, HTD-0197, HTD-0198, HTD-0200, HTD-0202, HTD-0204, HTD-0205, OLD-FIX-026, OLD-FIX-027, OLD-FIX-028, OLD-FIX-029, OLD-FIX-030, OLD-FIX-031, OLD-FIX-032, OLD-FIX-033, OLD-FIX-034, OLD-FIX-035, OLD-FIX-036, OLD-FIX-037, OLD-FIX-038, OLD-FIX-039, OLD-FIX-040, OLD-FIX-154, OLD-FIX-155, OLD-FIX-156, OLD-FIX-157, OLD-FIX-158
- Affected units: components/Operations/*
- Affected files: multiple
- Problem: Operations public surfaces construct tasks, queue, events, scheduler, observability, filesystem, and runtime collaborators.
- Why it matters: Operations components are worker/runtime-sensitive; hidden assembly raises lifecycle risk.
- Target state: Operations assembly flows through configuration and explicit runtimes.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Queue/Events/Scheduler first, then remaining Operations public surfaces.
- Safe batch boundary: Queue/Events/Scheduler first, then remaining Operations public surfaces.
- Tests required: queue/events/scheduler/observability behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-013: Reduce Security/Identity/DataStack PublicSurface construction pressure
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-009
- Source finding IDs: SCR-0239, SCR-0240, SCR-0241, SCR-0242, SCR-0243, SCR-0244, SCR-0245, SCR-0246, SCR-0247, SCR-0248, SCR-0249, SCR-0250, SCR-0251, SCR-0265, SCR-0266, SCR-0267, SCR-0272, SCR-0273, SCR-0286, SCR-0296, SCR-0305, SCR-0306, SCR-0311, SCR-0356, SCR-0357, SCR-0377, SCR-0381, SCR-0387, HTD-0239, HTD-0240, HTD-0241, HTD-0242, HTD-0243, HTD-0244, HTD-0245, HTD-0246, HTD-0247, HTD-0248, HTD-0249, HTD-0250, HTD-0251, HTD-0265, HTD-0266, HTD-0267, HTD-0272, HTD-0273, HTD-0286, HTD-0296, HTD-0305, HTD-0306, HTD-0311, HTD-0356, HTD-0357, HTD-0377, HTD-0381, HTD-0387, OLD-FIX-012, OLD-FIX-013, OLD-FIX-014, OLD-FIX-024, OLD-FIX-041, OLD-FIX-042, OLD-FIX-043, OLD-FIX-137, OLD-FIX-150, OLD-FIX-151, OLD-FIX-152, OLD-FIX-160
- Affected units: components/Security; components/Identity; components/DataStack
- Affected files: multiple
- Problem: Security/Identity/DataStack public surfaces and data boundaries construct collaborators internally.
- Why it matters: Security and data boundaries need explicit composition and negative tests.
- Target state: No security/data public surface creates mutable collaborator graphs.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Security Redaction/Secrets first, then Identity/DataStack.
- Safe batch boundary: Security Redaction/Secrets first, then Identity/DataStack.
- Tests required: negative security/data behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-014: Move constructor default dependency creation into approved configuration owners
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-010
- Source finding IDs: SCR-0046, SCR-0055, SCR-0057, SCR-0058, SCR-0059, SCR-0061, SCR-0062, SCR-0063, SCR-0064, SCR-0065, SCR-0066, SCR-0067, SCR-0068, SCR-0069, SCR-0070, SCR-0071, SCR-0072, SCR-0073, SCR-0074, SCR-0075, SCR-0076, SCR-0077, SCR-0078, SCR-0080, SCR-0081, SCR-0082, SCR-0083, SCR-0085, SCR-0086, SCR-0087, SCR-0088, SCR-0089, SCR-0090, SCR-0091, SCR-0092, SCR-0093, SCR-0094, SCR-0095, SCR-0096, SCR-0101, SCR-0104, SCR-0106, SCR-0111, SCR-0112, SCR-0113, SCR-0115, SCR-0117, SCR-0120, SCR-0124, SCR-0126, SCR-0128, SCR-0129, SCR-0130, SCR-0131, SCR-0132, SCR-0133, SCR-0135, SCR-0136, SCR-0137, SCR-0138, SCR-0140, SCR-0144, SCR-0147, SCR-0148, SCR-0151, SCR-0152, SCR-0153, SCR-0154, SCR-0155, SCR-0156, SCR-0160, SCR-0161, SCR-0162, SCR-0165, SCR-0166, SCR-0167, SCR-0169, SCR-0170, SCR-0171, SCR-0172, ... (529 total; full mapping in source-finding-coverage.md)
- Affected units: `components/Operations/RuntimeSupervision`, `components/Operations/MemoryLifecycle`, `components/DeveloperTools/Documentation/Api`, `components/Identity/ExternalIdentity`, `components/HTTP/URI`, `components/Application/Filesystem`, `components/API/OpenAPI`, `components/Application/Config`, `components/Operations/Events`, `components/Operations/ApplicationWorkflow`, `components/CLI/Console`, `components/HTTP/Client`, ... (53 units total)
- Affected files: multiple
- Problem: Many constructors create dependencies in default parameters or hidden fallbacks.
- Why it matters: Default construction weakens DI, hides failure behavior, and complicates tests.
- Target state: Required dependencies fail fast or are assembled explicitly in Configuration.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Per component owner; do not sweep unrelated files.
- Safe batch boundary: Per component owner; do not sweep unrelated files.
- Tests required: focused constructor/assembly tests for each touched owner.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-015: Add missing ServiceProvider assembly owners without creating new behavior
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-011
- Source finding IDs: SCR-0014, SCR-0015, SCR-0016, SCR-0017, SCR-0018, SCR-0019, SCR-0020, SCR-0021, SCR-0022, SCR-0023, SCR-0024, SCR-0025, SCR-0026, SCR-0027, SCR-0028, SCR-0029, SCR-0030, SCR-0031, SCR-0032, SCR-0033, SCR-0034, SCR-0035, SCR-0036, SCR-0037, SCR-0038, HTD-0014, HTD-0015, HTD-0016, HTD-0017, HTD-0018, HTD-0019, HTD-0020, HTD-0021, HTD-0022, HTD-0023, HTD-0024, HTD-0025, HTD-0026, HTD-0027, HTD-0028, HTD-0029, HTD-0030, HTD-0031, HTD-0032, HTD-0033, HTD-0034, HTD-0035, HTD-0036, HTD-0037, HTD-0038, OLD-FIX-055, OLD-FIX-056, OLD-FIX-063, OLD-FIX-069, OLD-FIX-072, OLD-FIX-075, OLD-FIX-084, OLD-FIX-167, OLD-FIX-168, OLD-FIX-173, OLD-FIX-180, OLD-FIX-182, OLD-FIX-186, OLD-FIX-189, OLD-FIX-194, OLD-FIX-195, OLD-FIX-196, OLD-FIX-198, OLD-FIX-200, OLD-FIX-201, OLD-FIX-204, OLD-FIX-207, OLD-FIX-208, OLD-FIX-214, OLD-FIX-222
- Affected units: `components/HTTP/Dispatcher`, `components/HTTP/URI`, `components/API/OpenAPI`, `components/Application/FeatureFlags`, `components/DeveloperTools/Dx`, `components/HTTP/Context`, `components/CLI/Console`, `components/HTTP/AfterResponse`, `components/API/Contracts`, `components/API/GraphQL`, `components/Identity/Security`, `components/DataStack/DataTransfer`, ... (25 units total)
- Affected files: multiple
- Problem: Real components are reported without ServiceProvider coverage.
- Why it matters: Components with real code need an explicit assembly owner.
- Target state: Each affected component has precise System/Configuration ServiceProvider registration.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: ServiceProvider coverage batch by component group.
- Safe batch boundary: ServiceProvider coverage batch by component group.
- Tests required: registration/boot tests per provider.
- Validation commands: `php tooling/refactor/check-service-provider-coverage.php && vendor/bin/phpunit --filter "ServiceProvider|Provider" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-016: Fix broken reference semantics in public/runtime namespaces
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-012
- Source finding IDs: SCR-0039, SCR-0040, SCR-0041, SCR-0042, SCR-0665, HTD-0039, HTD-0040, HTD-0041, HTD-0042, HTD-0668, OLD-FIX-023, OLD-FIX-025
- Affected units: `components/Operations/ApplicationWorkflow`, `components/HTTP`, `components/HTTP/System`
- Affected files: multiple
- Problem: Broken references remain in HTTP and Operations/ApplicationWorkflow evidence.
- Why it matters: Broken public/runtime references become autoload/runtime failures.
- Target state: All referenced classes resolve or references are removed with compatible behavior.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Broken reference semantic batch only.
- Safe batch boundary: Broken reference semantic batch only.
- Tests required: autoload/reference regression tests for affected units.
- Validation commands: `php tooling/refactor/check-broken-reference-semantics.php && composer dump-autoload -o`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-017: Route raw filesystem and path operations through approved first-party boundaries
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-013
- Source finding IDs: SCR-0043, SCR-0044, SCR-0045, HTD-0043, HTD-0044, HTD-0045, SAI-0141, SAI-0142, SAI-0160, OLD-FIX-002, OLD-FIX-118, OLD-FIX-121
- Affected units: `components/DataStack/DataTransfer`, `framework/System/Configuration/Builders`, `framework/System/Capabilities/FailureBoundary`, `Doctor/CheckAutoload.php`, `Runtime/Capabilities/RunApplicationOnPhpBuiltInServer.php`, `Doctor/*.php`
- Affected files: multiple
- Problem: Raw filesystem checks/path traversal appear in framework route/doctor/runtime areas.
- Why it matters: Dogfooding and security rules require one owner for filesystem/path behavior.
- Target state: Approved Filesystem/Path capabilities own file checks and path decisions.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Start with route dispatch builder raw `is_file()`, then Doctor/runtime path checks.
- Safe batch boundary: Start with route dispatch builder raw `is_file()`, then Doctor/runtime path checks.
- Tests required: path failure/negative tests.
- Validation commands: `php tooling/refactor/check-raw-file-operations.php || true; php tooling/refactor/check-public-surface.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-018: Harden security logging, redaction, and secret parameter handling
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-019, CLUSTER-016
- Source finding IDs: SAI-0093, SAI-0097, SAI-0098, SAI-0122, SAI-0124, SAI-0126, SAI-0144, SAI-0145, SAI-0158
- Affected units: `tests/Unit/Components/Security/Cryptography/CryptographyTest.php`, `CryptographyTest`, `SecretsCapabilitiesTest`, `Logging`, `Security/RequestSigning/VerifyInternalRequestSignature.php`, `Security/RequestSigning/SignInternalRequest.php`, `VerifyInternalRequestSignature.php, SignInternalRequest.php`
- Affected files: multiple
- Problem: Findings include unredacted logging, missing `#[SensitiveParameter]`, and missing negative security tests.
- Why it matters: Secrets must not leak through logs, stack traces, or weak tests.
- Target state: Sensitive inputs are annotated/redacted and abuse cases are tested.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Logging/redaction/signature/crypto security hardening only.
- Safe batch boundary: Logging/redaction/signature/crypto security hardening only.
- Tests required: negative tests for secret leakage, tampered ciphertext, invalid key, nested redaction.
- Validation commands: `vendor/bin/phpunit --filter "Redaction|Secrets|Cryptography|RequestSignature|Logging" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-019: Replace global helper service-locator shortcuts with testable boundaries
- Status: OPEN
- Priority: P1 HIGH
- Normalized severity: HIGH
- Source clusters: CLUSTER-027
- Source finding IDs: SCR-0435, HTD-0435, SAI-0065, SAI-0080
- Affected units: `components/Application/Text`, `All shortcuts.php (13 files)`, `HTTP/Security`
- Affected files: multiple
- Problem: Shortcut files call `app()` at runtime and duplicate helper names.
- Why it matters: Global service locators hide dependencies and are hard to isolate in tests.
- Target state: Helpers are thin compatibility shims over explicit public boundaries or are documented as temporary.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: HTTP/security shortcuts first because of CSRF duplication.
- Safe batch boundary: HTTP/security shortcuts first because of CSRF duplication.
- Tests required: helper load-order and behavior tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "shortcuts|csrf" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-020: Classify and reduce constructor bloat by owner
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-014, CLUSTER-015
- Source finding IDs: SCR-0421, SCR-0422, SCR-0424, SCR-0425, SCR-0427, SCR-0428, SCR-0429, SCR-0430, SCR-0431, SCR-0432, SCR-0433, SCR-0436, SCR-0437, SCR-0438, SCR-0439, SCR-0440, SCR-0441, SCR-0442, SCR-0443, SCR-0444, SCR-0445, SCR-0446, SCR-0447, SCR-0448, SCR-0449, SCR-0450, SCR-0453, SCR-0454, SCR-0455, SCR-0456, SCR-0457, SCR-0458, SCR-0459, SCR-0461, SCR-0462, SCR-0463, SCR-0464, SCR-0465, SCR-0466, SCR-0468, SCR-0469, SCR-0470, SCR-0471, SCR-0472, SCR-0473, SCR-0474, SCR-0475, SCR-0477, SCR-0478, SCR-0479, SCR-0480, SCR-0482, SCR-0484, SCR-0485, SCR-0486, SCR-0487, SCR-0488, SCR-0489, SCR-0491, SCR-0492, SCR-0493, SCR-0494, SCR-0495, SCR-0496, SCR-0497, SCR-0501, SCR-0502, SCR-0503, SCR-0504, SCR-0505, SCR-0506, SCR-0507, SCR-0508, SCR-0509, SCR-0510, SCR-0511, SCR-0512, SCR-0513, SCR-0514, SCR-0515, ... (483 total; full mapping in source-finding-coverage.md)
- Affected units: `components/Operations/RuntimeSupervision`, `components/Application/DateTime`, `components/Identity/ExternalIdentity`, `components/Operations/MessageBus`, `components/Operations/ApplicationWorkflow`, `components/HTTP/Client`, `components/Operations/Mail`, `components/API/GraphQL`, `components/HTTP/Router`, `components/DataStack/DataTransfer`, `components/Identity/Credentials`, `components/SystemDesign`, ... (41 units total)
- Affected files: multiple
- Problem: Large constructors and large classes exceed governance thresholds.
- Why it matters: High arity and large files hide multiple responsibilities.
- Target state: Each large unit is split, justified, or accepted with owner/expiry.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Identity/Auth and DataStack first after P0, then remaining large-unit report.
- Safe batch boundary: Identity/Auth and DataStack first after P0, then remaining large-unit report.
- Tests required: behavior-preserving tests for each touched split.
- Validation commands: `php tooling/refactor/check-constructor-bloat.php && php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-021: Add missing behavior proof and negative tests by risk slice
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-028, CLUSTER-016
- Source finding IDs: SCR-0001, SCR-0003, SCR-0004, SCR-0005, SCR-0006, SCR-0007, SCR-0008, SCR-0009, SCR-0010, SCR-0011, SCR-0012, SCR-0013, HTD-0001, HTD-0003, HTD-0004, HTD-0005, HTD-0006, HTD-0007, HTD-0008, HTD-0009, HTD-0010, HTD-0011, HTD-0012, HTD-0013, SAI-0241, OLD-FIX-169, OLD-FIX-199, OLD-FIX-206, OLD-FIX-217, OLD-FIX-218, OLD-FIX-219, OLD-FIX-220, OLD-FIX-226, OLD-FIX-230, OLD-FIX-234, OLD-FIX-235, OLD-FIX-236
- Affected units: `components/Operations/RuntimeSupervision`, `components/Operations/MemoryLifecycle`, `components/HTTP/Client`, `components/API/Contracts`, `components/Security/Privacy`, `components/Identity/Security`, `components/Security/DataProtection`, `components/Operations/BackgroundProcesses`, `components/Integration/ObjectStorage`, `components/DeveloperTools/DumpDebugger`, `components/Operations/Delivery`, `components/Operations/Realtime`, ... (17 units total)
- Affected files: multiple
- Problem: Some units lack component-specific tests or have only weak/smoke proof.
- Why it matters: Cleanup without behavior proof is unsafe.
- Target state: Public behavior, failure modes, and security boundaries have focused tests.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Security/runtime first, then components with no detected tests.
- Safe batch boundary: Security/runtime first, then components with no detected tests.
- Tests required: new focused tests only for touched behavior.
- Validation commands: `vendor/bin/phpunit --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-022: Resolve forbidden concept folder names through approved governance decisions
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-017
- Source finding IDs: SAI-0010, SAI-0011, SAI-0075, SAI-0092, SAI-0123, SAI-0132, SAI-0154, SAI-0199, SAI-0240
- Affected units: `components/API/Contracts/`, `components/DeveloperTools/Diagnostics/`, `components/HTTP/Security/`, `components/Identity/Security/`, `components/Operations/Events/`, `framework`, `Security/ directory`, `CROSS_CUTTING`
- Affected files: multiple
- Problem: Folders such as Contracts, Diagnostics, Security, Events are flagged as concept-word names.
- Why it matters: AvaX folders must say flow/capability, and exceptions require explicit governance.
- Target state: Each flagged name is renamed by approved slice or documented as an exception with expiry.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Verification and decision doc first; no mechanical renames in same batch.
- Safe batch boundary: Verification and decision doc first; no mechanical renames in same batch.
- Tests required: namespace/autoload checks after any approved rename.
- Validation commands: `php tooling/refactor/check-component-suite-structure.php && php tooling/refactor/check-namespace-drift.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-023: Collapse duplicate ownership and duplicate class implementations
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-018
- Source finding IDs: SAI-0021, SAI-0026, SAI-0133, SAI-0155, SAI-0156, SAI-0210, SAI-0242
- Affected units: `Testing.php, ContractTesting.php`, `Container`, `ResourceGovernance/PublicSurface/ResourceGovernor.php + ResourceGovernance`, `ResourceGovernance/`, `Runtime/GracefulShutdown/`, `framework`, `CROSS_CUTTING`
- Affected files: multiple
- Problem: Duplicate/near-duplicate classes and functions are reported across container, runtime, logging, CSRF, ResourceGovernance, and GracefulShutdown.
- Why it matters: Duplicate owners drift and make public/runtime behavior ambiguous.
- Target state: One owner per behavior, with compatibility handled explicitly.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Verify duplicates, choose owner, then remove or bridge one pair at a time.
- Safe batch boundary: Verify duplicates, choose owner, then remove or bridge one pair at a time.
- Tests required: regression tests for selected owner and compatibility bridge.
- Validation commands: `php tooling/refactor/check-duplicate-owners.php && php tooling/refactor/check-namespace-drift.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-024: Replace hidden superglobal/env/IO access in runtime flows
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-020
- Source finding IDs: SAI-0016, SAI-0017, SAI-0018, SAI-0082, SAI-0086, SAI-0139, SAI-0159, SAI-0161, SAI-0183, SAI-0190, SAI-0224
- Affected units: `DeveloperTools/Diagnostics`, `DeveloperTools/CodeGeneration/Capabilities/Generators/CodeGenerator.php`, `DeveloperTools/DumpDebugger/Capabilities/Formatters/VariableFormatter.php`, `HTTP/AfterResponse`, `HTTP`, `Runtime/GracefulShutdown/Capabilities/ShutdownSequence.php`, `GracefulShutdown/.../ShutdownSequence.php`, `PreCommit/PreCommitValidator.php`, `framework`, `ReportRuntimeFailure.php`
- Affected files: multiple
- Problem: Runtime code reads `$_SERVER`, `$_SESSION`, env vars, STDERR, headers/echo, or exits directly.
- Why it matters: Hidden I/O weakens portability and long-lived runtime safety.
- Target state: Runtime context, request objects, or configured capabilities provide all environment and I/O access.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: ReportRuntimeFailure/ExternalState/Doctor/PreCommit/SendResponse by slice.
- Safe batch boundary: ReportRuntimeFailure/ExternalState/Doctor/PreCommit/SendResponse by slice.
- Tests required: runtime context and CLI/non-CLI tests.
- Validation commands: `php tooling/refactor/check-runtime-composition-leaks.php && vendor/bin/phpunit --filter "Runtime|Doctor|PreCommit|SendResponse" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-025: Make error handling explicit where catch-and-continue hides failures
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-024
- Source finding IDs: SAI-0012, SAI-0047, SAI-0049, SAI-0050, SAI-0083, SAI-0149, SAI-0166, SAI-0197
- Affected units: `API/ApiBlueprint`, `FeatureFlags/PublicSurface/FeatureFlags.php`, `Config`, `Text`, `HTTP`, `PreCommit/PreCommit.php`, `PreCommit.php`, `framework`
- Affected files: multiple
- Problem: Several flows catch broad exceptions and continue/log without structured failure behavior.
- Why it matters: Silent failure obscures production incidents and can weaken security boundaries.
- Target state: Errors are fail-closed, reported through approved channels, or explicitly documented.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Rpc/AfterResponse/PreCommit/Cryptography fallback paths by slice.
- Safe batch boundary: Rpc/AfterResponse/PreCommit/Cryptography fallback paths by slice.
- Tests required: failure-mode tests proving expected behavior.
- Validation commands: `vendor/bin/phpunit --filter "Failure|Exception|AfterResponse|PreCommit|Cryptography" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-026: Verify SQL/CSV injection and table-name interpolation findings before remediation
- Status: NEEDS_VERIFICATION
- Priority: NEEDS_VERIFICATION
- Normalized severity: NEEDS_VERIFICATION
- Source clusters: CLUSTER-021
- Source finding IDs: SAI-0076, SAI-0077, SAI-0078, SAI-0085, SAI-0087
- Affected units: `HTTP/Session`, `DataStack/Database`, `DataStack/Persistence`, `HTTP/ContentNegotiation`
- Affected files: multiple
- Problem: Supplemental findings identify SQL/table interpolation and CSV injection risks, but exploitability depends on validation/quoting paths.
- Why it matters: Security findings must not be downgraded without proof, but remediation needs exact threat shape.
- Target state: Each suspected injection surface is classified confirmed P1, false-positive candidate, or accepted exception with proof.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Verification-only search/test batch; no code remediation.
- Safe batch boundary: Verification-only search/test batch; no code remediation.
- Tests required: targeted negative tests only after confirming exploitability.
- Validation commands: `rg -n "sprintf|CSV|fputcsv|wrap\(|table" components/DataStack components/HTTP && vendor/bin/phpunit --filter "Grammar|SessionStore|CsvFormat" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/security-runtime-escalation.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-027: Document public interface contracts and failure modes
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-022
- Source finding IDs: SCR-0002, HTD-0002, SAI-0019, SAI-0178, SAI-0179, SAI-0180, SAI-0181, SAI-0215, SAI-0216, SAI-0217, SAI-0243, OLD-FIX-197
- Affected units: `components/DeveloperTools/Documentation/Api`, `Multiple files`, `framework`, `HttpKernelInterface.php`, `RuntimeKernelInterface.php`, `CROSS_CUTTING`
- Affected files: multiple
- Problem: Public/kernel interfaces and some contracts lack semantic PHPDoc and `@throws` documentation.
- Why it matters: Public APIs need stable semantics and failure contracts before cleanup can be trusted.
- Target state: Interfaces explain intent, returns, invariants, and failure modes.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Framework kernel/public interfaces first, then security/runtime public contracts.
- Safe batch boundary: Framework kernel/public interfaces first, then security/runtime public contracts.
- Tests required: documentation gate plus existing interface tests.
- Validation commands: `php tooling/governance/check-semantic-phpdoc.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-028: Replace empty stubs/no-op methods with explicit behavior or explicit unsupported failure
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-023
- Source finding IDs: SAI-0020, SAI-0191, SAI-0192, SAI-0193, SAI-0196, SAI-0227, SAI-0228, SAI-0229
- Affected units: `API/DeveloperTools/`, `framework`, `ResetApplicationState.php`, `ShutdownRuntime.php`, `ConfigureRuntime.php`
- Affected files: multiple
- Problem: Empty classes and no-op methods hide incomplete runtime behavior.
- Why it matters: Silent no-ops create false readiness and make tests lie.
- Target state: Every incomplete behavior either does real work or fails explicitly with documented status.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: ResetApplicationState, ShutdownRuntime, ConfigureRuntime, empty exception markers by slice.
- Safe batch boundary: ResetApplicationState, ShutdownRuntime, ConfigureRuntime, empty exception markers by slice.
- Tests required: tests proving explicit behavior/failure.
- Validation commands: `vendor/bin/phpunit --filter "ResetApplicationState|ShutdownRuntime|ConfigureRuntime" --no-coverage`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-029: Measure and reduce DI/container object graph performance pressure
- Status: OPEN
- Priority: P2 MEDIUM
- Normalized severity: MEDIUM
- Source clusters: CLUSTER-026
- Source finding IDs: See source-finding-coverage.md
- Affected units: multiple
- Affected files: multiple
- Problem: DIContainer and related runtime paths create repeated short-lived object graphs and serialize large dependency graphs.
- Why it matters: Hot-path container churn can become worker memory/latency risk.
- Target state: Object creation is bounded or moved to compile/boot paths with measurements.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Measure first; change only after benchmark/test proof.
- Safe batch boundary: Measure first; change only after benchmark/test proof.
- Tests required: focused microbenchmark or existing performance gate plus behavior tests.
- Validation commands: `php tooling/refactor/check-direct-instantiation.php && php tooling/governance/check-large-unit-thresholds.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-030: Close low-risk compat/version/style cleanup with evidence
- Status: OPEN
- Priority: P3 LOW
- Normalized severity: LOW
- Source clusters: CLUSTER-025, CLUSTER-031
- Source finding IDs: SAI-0127, SAI-0200, SAI-0234, SAI-0235
- Affected units: `ApplicationWorkflow`, `framework`, `compat.php`, `AvaxVersion.php`
- Affected files: multiple
- Problem: Low-severity findings cover compat expiry, version string confusion, IDE annotations, optional extension fallback, and governance proof hygiene.
- Why it matters: Low-noise cleanup prevents future confusion but must not displace P0/P1.
- Target state: Each low item is either fixed, documented with expiry, or archived as obsolete.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Low-risk docs/style cleanup only after P0/P1.
- Safe batch boundary: Low-risk docs/style cleanup only after P0/P1.
- Tests required: focused lint/governance checks only.
- Validation commands: `composer validate --no-check-publish && php tooling/governance/check-root-evidence-hygiene.php`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/finding-clusters.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-031: Verify supplemental-only scan claims before promoting or dropping them
- Status: NEEDS_VERIFICATION
- Priority: NEEDS_VERIFICATION
- Normalized severity: NEEDS_VERIFICATION
- Source clusters: CLUSTER-030, CLUSTER-032
- Source finding IDs: DR-0667, DR-0668, SCR-0056, SCR-0060, SCR-0103, SCR-0105, SCR-0107, SCR-0108, SCR-0109, SCR-0110, SCR-0114, SCR-0116, SCR-0262, SCR-0359, SCR-0361, SCR-0362, SCR-0363, SCR-0364, SCR-0365, SCR-0366, SCR-0367, SCR-0392, SCR-0394, SCR-0423, SCR-0434, SCR-0460, SCR-0467, SCR-0476, SCR-0481, SCR-0483, SCR-0490, SCR-0498, SCR-0499, SCR-0500, SCR-0550, SCR-0637, SCR-0638, SCR-0639, SCR-0658, SCR-0659, SCR-0660, SCR-0661, SCR-0662, SCR-0663, HTD-0056, HTD-0060, HTD-0103, HTD-0105, HTD-0107, HTD-0108, HTD-0109, HTD-0110, HTD-0114, HTD-0116, HTD-0262, HTD-0359, HTD-0361, HTD-0362, HTD-0363, HTD-0364, HTD-0365, HTD-0366, HTD-0367, HTD-0423, HTD-0434, HTD-0460, HTD-0467, HTD-0476, HTD-0481, HTD-0483, HTD-0490, HTD-0498, HTD-0499, HTD-0500, HTD-0550, HTD-0637, HTD-0638, HTD-0639, HTD-0658, HTD-0659, ... (141 total; full mapping in source-finding-coverage.md)
- Affected units: `tooling/governance/check-serviceprovider-governance-consistency.php output`, `tooling/governance/check-security-governance.php`, `components/Application/Config`, `components/Application/Pipeline`, `components/Operations/ApplicationWorkflow`, `components/CLI/Console`, `components/API/GraphQL`, `components/HTTP/Router`, `components/SystemDesign`, `components/Application/Cache`, `components/Application/Text`, `components/HTTP/Session`, ... (56 units total)
- Affected files: multiple
- Problem: Some supplemental strict-review claims were scan-only or explicitly listed as known gaps, including zero-test claims and clean-unit claims.
- Why it matters: Review discipline forbids false positives and fake clean status.
- Target state: Every supplemental-only claim is confirmed, demoted to false-positive candidate with evidence, or merged into an active TODO.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Verification-only batch.
- Safe batch boundary: Verification-only batch.
- Tests required: none unless verification creates confirmed remediation TODO.
- Validation commands: `rg -n "<claim-specific pattern>" components framework tests && find tests -type f | sort`
- Evidence required: `.agents/management/evidence/generated/review-reconciliation/source-finding-coverage.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: next cleanup batch unless explicitly deferred

## TODO-032: Maintain semantic PHPDoc legacy ratchet while cleaning touched files
- Status: ACCEPTED_YELLOW
- Priority: ACCEPTED_YELLOW
- Normalized severity: ACCEPTED_YELLOW
- Source clusters: CLUSTER-029
- Source finding IDs: DR-0666
- Affected units: `components/ and framework/`
- Affected files: multiple
- Problem: Semantic PHPDoc gate reports 9810 legacy ratchet violations with 0 touched/new blockers in discipline evidence.
- Why it matters: The debt is broad and should not block every cleanup, but touched public/runtime/security files must not add noise.
- Target state: Ratchet remains accepted yellow with owner, risk, expiry, and touched-file cleanup rule.
- Non-goals: no feature work; no roadmap work; no public API changes unless explicitly approved; no mechanical renames; no unrelated cleanup; no fake GREEN
- Required remediation shape: Apply touched-file semantic PHPDoc cleanup inside each remediation batch.
- Safe batch boundary: Apply touched-file semantic PHPDoc cleanup inside each remediation batch.
- Tests required: semantic PHPDoc gate after each cleanup batch.
- Validation commands: `php tooling/governance/check-semantic-phpdoc.php`
- Evidence required: `.agents/management/evidence/generated/discipline-review/global-governance-heatmap.md`
- Commit gate: no production/test files outside the prompt scope; no unrelated generated/dump/local files staged; validation evidence captured; source coverage updated.
- Done when: source IDs mapped to this TODO are closed or reclassified with evidence, validation passes, and cleanup evidence records the exact command output.
- Owner: AvaX maintainer
- Expiry/target: review at next cleanup milestone

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

