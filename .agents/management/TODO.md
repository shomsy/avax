# TODO

Canonical active implementation queue.

## Rules

- keep newest items first
- keep each item outcome-oriented
- include acceptance criteria
- include owner only when needed
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | blocked | done
- `estimate`:
- `actual`:
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: POST-ROUND-002-EVIDENCE-REPAIRED-MERGES
- `created_at`: 2026-05-20
- `updated_at`: 2026-05-20
- `status`: done
- `estimate`: small
- `actual`: 4 branches merged into main sequentially with focused validation after each merge
- `outcome`: Four evidence-repaired branches merged into main — TODO-017 filesystem boundary routing, TODO-019 global helper shortcut hardening, TODO-018 security logging and redaction, TODO-005 static secret state reset. All with tests, evidence, and governance review. Accepted YELLOW from review preserved.
- `acceptance`: 4 branches merged in approved order with validation after each. Full test suite: 8853 tests (12 pre-existing ProcessPoolParallelismProofTest failures, 1 risky — all pre-existing). PHPStan: 66 findings (all pre-existing, none from merged files). Governance gates: GREEN. Runtime composition leaks: 4 HIGH pre-existing (unrelated Migrations/Container files). Direct instantiation: pre-existing constructor default parameter findings. Broken references: PASS. Namespace drift: PASS. Accepted YELLOW: (1) BuildDispatchConfiguredRoute fallback new Filesystem() — LOW, (2) stream wrapper / PSR-7 upload / path parsing exceptions — ACCEPTED_EXCEPTION, (3) 15 other shortcuts.php files still use app() — MEDIUM, (4) CSRF behavior tests need container bootstrap — LOW.
- `links`: `.agents/management/evidence/generated/post-round-002-evidence-repaired-merge/main-merge-validation.md`, `dd3c53936 docs(governance): review evidence-repaired post-round-002 branches`

- `id`: POST-ROUND-002-MERGES
- `created_at`: 2026-05-20
- `updated_at`: 2026-05-20
- `status`: done
- `estimate`: small
- `actual`: 3 branches merged into main sequentially with validation
- `outcome`: Post-Round-002 branches merged into main — Phase0 truth reconciliation, TODO-004 dynamic class-loading hardening, TODO-004-b container migration hardening. All focused security improvements with tests and evidence.
- `acceptance`: 3 branches merged in order with validation after each. Phase0 / CURRENT_TRUTH reconciliation: DONE. TODO-004 dynamic class-loading boundaries: DONE. TODO-004-b: integrated as TODO-004 sub-slice. Accepted LOW: TODO-004-b missing test-proof.md summary file (tests exist in diff). Pre-existing YELLOW: check-direct-instantiation.php (163 constructor default parameter findings across framework), check-runtime-composition-leaks.php (4 HIGH findings for guarded class_exists calls in TODO-004-b hardened paths — expected for dynamic class-loading security).
- `links`: `.agents/management/evidence/generated/post-round-002-ready-merge/main-merge-validation.md`, `.agents/management/evidence/generated/post-round-002-ready-review/summary.md`, `.agents/management/evidence/generated/post-round-002-ready-review/todo-004-review.md`, `.agents/management/evidence/generated/post-round-002-ready-review/todo-004-b-review.md`, `.agents/management/evidence/generated/post-round-002-ready-review/phase0-review.md`

- `id`: V5.9-AUTHBUILDER-SPLIT-FIRST-SLICE
- `created_at`: 2026-05-16
- `updated_at`: 2026-05-16
- `status`: todo
- `estimate`: large
- `actual`: not started
- `outcome`: Execute the first safe AuthBuilder split slice before Boot DSL work continues.
- `acceptance`: Extract container default dependency resolution from `AuthBuilder::withContainer()` into a focused
  configuration builder with tests for explicit override precedence, named throttle bindings, missing dependency
  failure,
  `DefaultAuth::configuration($container)`, and `RegisterAuthDependencies` resolution. Rerun full validation, governance
  gates, security review, performance review, and recursive governance review.
- `links`: `EVIDENCE/v5.9-codex/06-large-unit-gate-classification.md`,
  `EVIDENCE/v5.9-codex/07-authbuilder-split-plan.md`

- `id`: V5.9-CODEX-BASELINE-RED
- `created_at`: 2026-05-16
- `updated_at`: 2026-05-16
- `status`: done
- `estimate`: large
- `actual`: preflight, baseline, gate classification, ratchet correction, how-to structure fix, AuthBuilder split plan
- `outcome`: V5.9 governance baseline is no longer fake RED. Semantic PHPDoc legacy debt is YELLOW_WITH_RATCHET
  (9823 findings, touched/new scope blocking), how-to document structure is PASS, and the large-unit gate is classified
  with one exact real blocker: `AuthBuilder.php`.
- `acceptance`: Semantic PHPDoc ratchet baseline exists, touched/new violations remain blocking, how-to gate passes,
  large-unit gate excludes local dot-worktrees and still catches AuthBuilder, AuthBuilder split first-slice plan exists,
  and truth/backlog files name the next allowed action.
- `links`: `EVIDENCE/v5.9-codex/00-preflight.md`, `EVIDENCE/v5.9-codex/01-baseline-validation.md`,
  `EVIDENCE/v5.9-codex/04-semantic-phpdoc-ratchet-correction.md`,
  `EVIDENCE/v5.9-codex/05-how-to-document-structure-correction.md`,
  `EVIDENCE/v5.9-codex/06-large-unit-gate-classification.md`,
  `EVIDENCE/v5.9-codex/07-authbuilder-split-plan.md`

- `id`: V5.8.9
- `created_at`: 2026-05-15
- `updated_at`: 2026-05-15
- `status`: done
- `estimate`: small
- `outcome`: V5.8.9 Governance Documentation Update — COMPLETE / GREEN. Root Application Container, Boot-time Dependency
  Verification, Git Workflow, and Mandatory Recursive Governance Review Before Commit added to how-to documents. 5 files
  modified/created.
- `acceptance`: how-to-dependency-injection.md contains Root Application Container and Section 3.7 rules.
  how-to-runtime-composition.md cross-references Root Application Container. how-to-git.md created with commit/review
  workflow. how-to-code-review.md contains the full Mandatory Recursive Governance Review Before Commit rule. No broken
  links.
- `links`: `.agents/how-to/how-to-dependency-injection.md`, `.agents/how-to/how-to-runtime-composition.md`,
  `.agents/how-to/how-to-git.md`, `.agents/how-to/how-to-code-review.md`,
  `.agents/how-to/how-to-production-readiness.md`

- `id`: V5.8.8
- `created_at`: 2026-05-15
- `updated_at`: 2026-05-15
- `status`: done
- `estimate`: large
- `outcome`: V5.8.8 PHPStan, Runtime Gate & Truth Integrity Closure — COMPLETE / YELLOW_WITH_EXACT_BLOCKERS. PHPUnit
  8351 tests GREEN. PHPStan reduced 308 → 253 (55 fixed). 15 files changed. Runtime gate FAIL (163 findings, 3 new from
  V5.8.7). Truth files updated to honest state. V5.9 BLOCKED by AuthBuilder ~170 errors + 3 runtime leaks.
- `acceptance`: PHPStan inventory created. 55 PHPStan errors fixed from root cause. Runtime gate findings classified.
  Truth files updated to match validation. Evidence files created. No tests deleted or weakened. No broad suppressions
  added.
- `links`: `EVIDENCE/hardening/33-v5-8-8-preflight.md` through `EVIDENCE/hardening/42-v5-8-8-truth-reconciliation.md`

- `id`: V5.8.6
- `created_at`: 2026-05-15
- `updated_at`: 2026-05-15
- `status`: done
- `estimate`: medium
- `outcome`: V5.8.6 HTTP Response Layer Convergence — COMPLETE / GREEN. ResponseServiceProvider registers
  CreateHttpResponse, Responses, ResponseFactoryInterface alias. 13 provider tests GREEN. Pre-existing errors
  classified (116 errors + 37 failures, all pre-existing, none caused by response refactor). GoldenPathRuntime
  responseFactory named parameter bug fixed (54 errors resolved).
- `acceptance`: ResponseServiceProvider exists and registers CreateHttpResponse + Responses + PSR-17
  ResponseFactoryInterface binding. Responses delegates to CreateHttpResponse. No legacy ResponseFactory in main tree.
  Truth files updated. Evidence files created.
- `links`: `EVIDENCE/hardening/21-response-final-validation.md`,
  `EVIDENCE/hardening/22-response-truth-reconciliation.md`, `EVIDENCE/hardening/23-response-layer-final-audit.md`

- `id`: CLEANUP-ENTERPRISE-001
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: blocked
- `estimate`: large
- `outcome`: AvaX Full Enterprise Cleanup Program is YELLOW_WITH_EXACT_BLOCKERS. Stage A
  validation/PHPStan/PHPUnit/autoload are green, but V5.9 remains blocked.
- `acceptance`: Health/doctor gate green with real checks, component status lock current, broken-ref audit
  resolved/classified, missing gates implemented or formally accepted, raw-file/performance warnings classified, truth
  consistency green.
- `links`: `EVIDENCE/cleanup/13-final-whole-system-acceptance-audit.md`, `EVIDENCE/cleanup/skipped-work-ledger.md`

- `id`: V5.8-12
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: large
- `outcome`: V5.8 Database Lifecycle Events — COMPLETE / GREEN. Foundation enums, registration objects, 19 event
  objects, compiled registry, fluent DSL (onEntity/onQuery/onTransaction), transaction afterCommit/afterRollback safety,
  outbox groundwork. 8203 tests GREEN, PHPStan 0 errors.
- `acceptance`: All 12 implementation stages (V5.8-01 through V5.8-12) GREEN. Entity/query/transaction lifecycle phases
  defined. DSL registers into frozen compiled registry. Transaction callbacks run only on outermost commit, discard on
  rollback. Outbox groundwork is design-only. Evidence: EVIDENCE/v5.8/01 through EVIDENCE/v5.8/50.
- `links`: `EVIDENCE/v5.8/v5.8-stage-ledger.md`, `EVIDENCE/v5.8/50-v5.8-final-acceptance-audit.md`

- `id`: V5.7-13
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-13 Final Acceptance Audit — GREEN. 8069 tests GREEN, PHPStan 0 errors, 10 event gates PASS, 7
  governance gates PASS. V5.7 COMPLETE / GREEN.
- `acceptance`: All 14 V5.7 stages (V5.7-00 through V5.7-13) GREEN. Events Fluent DSL, Runtime, PSR-14 Interop, Real
  Dogfooding, CQRS Projection, Event-History Reference — all GREEN. Evidence: EVIDENCE/v5.7/43 through EVIDENCE/v5.7/54.
- `links`: `EVIDENCE/v5.7/54-v5.7-final-acceptance-audit.md`

- `id`: V5.7-12
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: large
- `outcome`: V5.7-12 Tooling Gates — GREEN. 10 event gates implemented (137 checks total), all PASS.
- `acceptance`: All event gates PASS. Evidence: EVIDENCE/v5.7/49, EVIDENCE/v5.7/50.
- `links`: `EVIDENCE/v5.7/49-event-gates-implementation.md`, `EVIDENCE/v5.7/50-event-gates-proof.md`

- `id`: V5.7-11
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-11 Event-History Reference Proof — GREEN_REFERENCE_ONLY. ReferenceEventHistoryStore +
  ReplayEventHistory proven in SecureRegistrationApi.
- `acceptance`: Event-history stores UserRegistered. Replay rebuilds RegisteredUserView. Explicitly marked as
  reference/proof only. Evidence: EVIDENCE/v5.7/45.
- `links`: `EVIDENCE/v5.7/45-event-history-reference-proof.md`

- `id`: V5.7-10
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-10 CQRS Projection Dogfooding — GREEN. ProjectRegisteredUser builds RegisteredUserView from
  UserRegistered events. ReadRegisteredUser queries read model.
- `acceptance`: Projection created through listener. Read model queryable. No generic CQRS folder. Evidence:
  EVIDENCE/v5.7/44.
- `links`: `EVIDENCE/v5.7/44-cqrs-projection-dogfooding.md`

- `id`: V5.7-09
- `created_at`: 2026-05-13
- `updated_at`: 2026-05-13
- `status`: done
- `estimate`: large
- `outcome`: V5.7-09 Real Event Dogfooding — GREEN. SecureRegistrationApi emits UserRegistered through canonical AvaX
  Events runtime. 3 listeners: audit, projection, event-history. 12 new tests pass.
- `acceptance:` Registration emits event. Listeners invoked. Projection built. Event replay works. No
  EventInterface/ListenerInterface required. Evidence: EVIDENCE/v5.7/43.
- `links`: `EVIDENCE/v5.7/43-real-event-dogfooding.md`

- `id`: V5.7-08
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-08 PSR-14 Adapter — GREEN. Psr14EventDispatcherAdapter + Psr14ListenerProviderAdapter created.
  psr/event-dispatcher added to composer.json require. PSR dispatch delegates to AvaX. Stoppable events work. User API
  remains AvaX DSL. 49 new tests pass.
- `acceptance`: PSR dispatch($event) returns event. PSR listener provider returns AvaX listeners. PSR stoppable stops
  propagation. AvaX works without PSR-14 (now installed). User DSL does not require PSR types. Evidence:
  EVIDENCE/v5.7/34.
- `links`: `EVIDENCE/v5.7/34-events-runtime-closure-final-report.md`

- `id`: V5.7-07
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: V5.7-07 Dispatch Runtime — GREEN. EventEmitter + ResolveEventListeners + InvokeEventListener created. Full
  dispatch path: emit → EventEmitter → CompiledListenerRegistry → ResolveEventListeners → InvokeEventListener → return
  event. Stoppable events work. Listener failure bubbles. 49 new tests pass.
- `acceptance`: emit(new Event()) invokes registered listeners. Priority ordering works. Returns same event. No-listener
  behavior works. Listener return values ignored. Listener failure bubbles. Stoppable events stop. No
  EventInterface/ListenerInterface required. No runtime reflection. Evidence: EVIDENCE/v5.7/34.
- `links`: `EVIDENCE/v5.7/34-events-runtime-closure-final-report.md`

- `id`: V5.7-06
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: V5.7-06 Compiled Listener Registry — GREEN. CompiledListenerRegistry + CompileEventListeners created. DSL
  and attribute declarations converge into one frozen registry. Priority ordering, source tracking, registration order
  preserved. 49 new tests pass.
- `acceptance`: DSL registration compiles into registry. ListensTo attribute compiles into registry. Both share one
  registry. Unknown event returns empty list. Priority descending sort. Same priority preserves registration order.
  Source tracking works. No runtime reflection. Registry freezes. Evidence: EVIDENCE/v5.7/34.
- `links`: `EVIDENCE/v5.7/34-events-runtime-closure-final-report.md`

- `id`: V5.7-05
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: small
- `outcome`: V5.7-05 ListensTo Attribute — GREEN. #[ListensTo(EventClass::class, priority: N)] attribute created.
  Declaration-only, attribute reflection at compile-time only.
- `acceptance`: ListensTo attribute stores event class and priority. Default priority is 0. Targets classes only.
  Compiles into CompiledListenerRegistry. Evidence: EVIDENCE/v5.7/34.
- `links`: `EVIDENCE/v5.7/34-events-runtime-closure-final-report.md`

- `id`: V5.7-04
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: small
- `outcome`: V5.7-04 emit() Surface — GREEN. emit(object $event): object global function created. Object-only public
  API. EmitEvent flow created. Returns same event object.
- `acceptance`: emit(new Event()) dispatches through EventEmitter. Returns the dispatched event. No-listener behavior
  returns event unchanged. Does not register listeners. Does not mutate GlobalEventListenerState beyond dispatch.
  Delegates to canonical EventEmitter. Does not require EventInterface/ListenerInterface. Evidence: EVIDENCE/v5.7/34.
- `links`: `EVIDENCE/v5.7/34-events-runtime-closure-final-report.md`

- `id`: V5.7-03
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: small
- `outcome`: V5.7-03 Fluent Event DSL — GREEN. EventListenerDsl with chainable do() method. Global onEvent() and
  onEventSetRegistry() functions. GlobalEventListenerState boot-time singleton. Class-string resolution for invokable
  listeners. 12 new tests. 7947 tests pass, PHPStan 0 errors, event owner gate 7/7 PASS.
- `acceptance`: onEvent(Event::class)->do(Listener::class) registers listener. Priority works (higher = earlier).
  Multiple do() calls chain correctly. Shared registry used across calls. Evidence: EVIDENCE/v5.7/22.
- `links`: `EVIDENCE/v5.7/22-fluent-dsl-implementation.md`

- `id`: V5.7-02
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-02 Event Contracts and Foundation — GREEN. Foundation types created (ListenerSource,
  ListenerExecutionMode, ListenerRegistration, CompiledListener). ListenerProvider capability. ListenerRegistry enhanced
  with register(), listenersFor(), registration order tracking. 12 new tests. No forced interfaces. 7923 tests pass,
  PHPStan 0 errors, event owner gate 7/7 PASS.
- `acceptance`: ListenerRegistration and CompiledListener hold all fields. ListenerRegistry returns sorted listeners by
  priority descending with deterministic tie-breaking. ListenerProvider delegates to registry. User events are plain
  objects, user listeners are plain callables. Sync is the only execution mode. Evidence: EVIDENCE/v5.7/20-21.
- `links`: `EVIDENCE/v5.7/20-event-contracts-foundation-implementation.md`,
  `EVIDENCE/v5.7/21-event-foundation-test-proof.md`

- `id`: V5.7-01
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: V5.7-01 Owner Convergence — GREEN. Duplicate ListenerRegistry removed. Canonical owner confirmed (
  Operations/Events). Gate created (tooling/events/check-canonical-event-owner.php, 7/7 PASS). 4 event systems
  classified. No breaking changes. 7899 tests pass.
- `acceptance`: Only one ListenerRegistry exists. No duplicate EventDispatcher outside Operations/Events. Event owner
  gate passes. All tests pass. Evidence: EVIDENCE/v5.7/13-17.
- `links`: `EVIDENCE/v5.7/13-owner-convergence-baseline.md`, `EVIDENCE/v5.7/14-owner-convergence-classification.md`,
  `EVIDENCE/v5.7/15-canonical-owner-enforcement.md`, `EVIDENCE/v5.7/16-duplicate-event-owner-scan.md`,
  `EVIDENCE/v5.7/17-owner-convergence-gate-readiness.md`

- `id`: V5.7-DESIGN
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: V5.7 Events Fluent DSL & PSR-14 Interop — Design Lock GREEN. 4 existing event systems audited. Canonical
  owner chosen (Operations/Events). DSL, attribute, compiled registry, PSR-14, and dispatch semantics designed. 13
  implementation stages defined. 0 human decisions required.
- `acceptance`: All 14 design criteria met. See `EVIDENCE/v5.7/v5.7-design-lock-report.md`,
  `EVIDENCE/v5.7/v5.7-stage-ledger.md`, `docs/events/fluent-events-dsl.md`.
- `links`: `EVIDENCE/v5.7/00-baseline-validation.md`, `EVIDENCE/v5.7/01-existing-event-system-audit.md`,
  `EVIDENCE/v5.7/02-events-owner-decision.md`, `EVIDENCE/v5.7/03-event-model-decision.md`,
  `EVIDENCE/v5.7/04-events-fluent-dsl-design.md`, `EVIDENCE/v5.7/05-listens-to-attribute-design.md`,
  `EVIDENCE/v5.7/06-compiled-listener-registry-design.md`, `EVIDENCE/v5.7/07-psr14-interop-design.md`,
  `EVIDENCE/v5.7/08-event-dispatch-semantics.md`, `EVIDENCE/v5.7/09-future-compatibility-design.md`,
  `EVIDENCE/v5.7/10-events-tooling-gates-design.md`, `EVIDENCE/v5.7/11-proposed-events-architecture-tree.md`,
  `EVIDENCE/v5.7/12-v5.7-implementation-stage-plan.md`, `docs/events/fluent-events-dsl.md`

- `id`: V5.6-PHASE
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: multi-session
- `outcome`: V5.6 Declarative Failure Boundary — Core production-ready, extended policies deferred (Overall YELLOW).
  ReportFailure upgraded to Observability Logger with structured context + redaction. DeadLetter produces structured
  envelope. 65 tests / 129 assertions. 14 evidence documents. 4 gates GREEN.
- `acceptance`: 65 tests pass, PHPStan clean on FailureBoundary scope (5 pre-existing test warnings unrelated).
  OnFailure + ReportFailure adopted in real flow with E2E proof. Retry/Fallback/DeadLetter functional (Retry standalone,
  DeadLetter NDJSON transport). Timeout/RecoverWith deferred (DEFERRED_NOT_ENFORCED). See
  `EVIDENCE/failure-boundary/00-14-final-production-closure-report.md`,
  `EVIDENCE/failure-boundary/15-final-status-normalization.md`, `EVIDENCE/failure-boundary/final-acceptance-audit.md`.
- `links`: `EVIDENCE/failure-boundary/00-current-implementation-inventory.md`,
  `EVIDENCE/failure-boundary/01-ownership-and-duplication-audit.md`,
  `EVIDENCE/failure-boundary/02-try-catch-finally-inventory.md`,
  `EVIDENCE/failure-boundary/03-http-pipeline-integration.md`,
  `EVIDENCE/failure-boundary/04-attribute-adoption-proof.md`, `EVIDENCE/failure-boundary/05-compiled-metadata-proof.md`,
  `EVIDENCE/failure-boundary/06-dogfooding-proof.md`, `EVIDENCE/failure-boundary/07-mvp-production-gaps.md`,
  `EVIDENCE/failure-boundary/08-retry-decision.md`, `EVIDENCE/failure-boundary/09-timeout-recoverwith-deferred.md`,
  `EVIDENCE/failure-boundary/10-rethrow-cleanup-proof.md`, `EVIDENCE/failure-boundary/11-test-coverage-report.md`,
  `EVIDENCE/failure-boundary/12-tooling-gates-report.md`,
  `EVIDENCE/failure-boundary/13-production-readiness-assessment.md`,
  `EVIDENCE/failure-boundary/14-final-production-closure-report.md`,
  `EVIDENCE/failure-boundary/15-final-status-normalization.md`, `EVIDENCE/failure-boundary/final-acceptance-audit.md`,
  `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`, `docs/failure-boundary/declarative-failure-boundary.md`

- `id`: V5.6-Y7
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: small
- `outcome`: Fixed 5 pre-existing PHPStan test warnings — PHPStan 0 errors full scope
- `acceptance`: PHPStan 0 issues for full scope.
  `EVIDENCE/failure-boundary/deferred/V5.6-Y7-phpstan-warnings-resolution.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/deferred/V5.6-Y7-phpstan-warnings-resolution.md`

- `id`: V5.6-Y6
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: OnFailure + ReportFailure adopted in real reference flow (RegistrationController). 9 E2E tests, 28
  assertions. Domain exceptions mapped to HTTP status codes (422, 409, 503). FailureBoundary middleware wired into App
  pipeline.
- `acceptance`: Real route uses attributes with E2E proof. See
  `EVIDENCE/failure-boundary/deferred/V5.6-Y6-real-adoption.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/deferred/V5.6-Y6-real-adoption.md`

- `id`: V5.6-Y3
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: CleanupAfterFailure uses FailureCleanupRegistry with hook registration, execution, failure isolation
- `acceptance`: Cleanup guaranteed via finally block with FailureCleanupRegistry. See
  `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y1
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: medium
- `outcome`: Retry delegates to canonical Resilience RetryExecutor with backoff strategies + jitter
- `acceptance`: Retry dogfooded through Resilience. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y2
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: DeadLetter uses Queue FailedJobsStore as primary transport with error_log NDJSON fallback
- `acceptance`: DeadLetter uses canonical Queue transport. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y5
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: RecoverWith enforced through RunRecoveryAction with FailureDecision::Recover
- `acceptance`: Recovery handler contract + enforcement proven. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.6-Y4
- `created_at`: 2026-05-12
- `updated_at`: 2026-05-12
- `status`: done
- `estimate`: large
- `outcome`: Timeout enforced via Resilience Timeout at action level; elapsed mode by default
- `acceptance`: Timeout enforced through Resilience. See `EVIDENCE/failure-boundary/full-closure-evidence.md`
- `links`: `EVIDENCE/failure-boundary/v5.6-deferred-work-backlog.md`,
  `EVIDENCE/failure-boundary/full-closure-evidence.md`

- `id`: V5.5-PHASE
- `created_at`: 2026-05-11
- `updated_at`: 2026-05-11
- `status`: done
- `estimate`: multi-session
- `outcome`: V5.5 Benchmark Proof & World-Class Hardening — GREEN. All 13 stages (V5.5-00 through V5.5-12) completed
  with valid evidence. V5.5-05 Reference App Benchmarks implemented — all 13 reference apps benchmarked.
- `acceptance`: 13 GREEN, 0 YELLOW. Sub-millisecond request handling (0.011ms avg, 90K+ RPS). 13 reference apps
  benchmarked (avg 0.012-0.015ms, 66K-85K RPS). Zero memory growth after 10K iterations. Zero state leaks. PHPStan 0
  errors on benchmark code. Benchmark infrastructure tests: 35 tests covering edge cases. See
  `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`,
  `EVIDENCE/v5.5/v5.5-final-acceptance-audit.md`, `EVIDENCE/v5.5/reference-app-benchmarks.json`.
- `links`: `EVIDENCE/v5.5/v5.5-stage-ledger.md`, `EVIDENCE/v5.5/v5.5-12-final-readiness-report.md`,
  `EVIDENCE/v5.5/v5.5-11-optimization-pass.md`, `EVIDENCE/v5.5/v5.5-final-acceptance-audit.md`,
  `EVIDENCE/v5.5/reference-app-benchmarks.json`, `docs/benchmarks/benchmark-infrastructure.md`

- `id`: V5-PHASE
- `created_at`: 2026-05-10
- `updated_at`: 2026-05-11
- `status`: done
- `estimate`: multi-session
- `outcome`: V5 Internal Convergence — COMPLETE / GREEN. All 23 stages (V5-00 through V5-22) GREEN_BY_EVIDENCE. V5-23
  Final V5 Truth Report produced.
- `acceptance`: 7711 tests GREEN, PHPStan 0 errors, all governance gates PASS. E2E suite covers parameterized routes,
  405 behavior, compiled metadata pipeline. Reflection risks classified as non-hot-path ALLOWED. See
  `EVIDENCE/v5/v5-stage-ledger.md`, `EVIDENCE/v5/v5-23-final-truth-report.md`,
  `EVIDENCE/v5/v5-final-acceptance-audit.md`.
- `links`: `EVIDENCE/v5/v5-stage-ledger.md`, `EVIDENCE/v5/v5-23-final-truth-report.md`,
  `EVIDENCE/v5/v5-final-acceptance-audit.md`

## V5.5 Stage Ledger Status

**Date:** 2026-05-11
**Total:** 13 GREEN = 13 stages
**Overall: GREEN**

### GREEN_BY_EVIDENCE (13)

V5.5-00 Benchmark Methodology Lock, V5.5-01 Hardware/Environment Baseline, V5.5-02 Microbenchmarks, V5.5-03 Runtime
Benchmarks, V5.5-04 HTTP Throughput, V5.5-05 Reference App Benchmarks (13 apps), V5.5-06 Long-Running Worker Soak Tests,
V5.5-07 Memory Leak & State Leak Tests, V5.5-08 Database/Queue/Messaging Throughput, V5.5-09 Observability & Security
Overhead, V5.5-10 Framework Comparison Suite, V5.5-11 Optimization Pass, V5.5-12 Final World-Class Readiness

### Key Metrics

- Request handling: 0.011ms avg, 0.020ms p99, 90K+ RPS
- Reference apps: 13/13 benchmarked, avg 0.012-0.015ms, 66K-85K RPS
- Soak test: 10,000 iterations, 0 errors, 0KB memory growth
- Memory leak: stable, 0KB growth
- Overhead: logging +4.5%, signing +2.8%

## V5 Stage Ledger Status

**Date:** 2026-05-11
**Total:** 23 implementation stages + 1 final truth report = 24 entries
**Math:** 23 GREEN + 0 PARTIAL + 0 MISSING = 23 implementation stages

### GREEN_BY_EVIDENCE (23)

V5-00 Final V4 Truth Lock, V5-01 Governance Resolution, V5-02 Security Blocker Cleanup, V5-03 Capability Ownership Scan,
V5-04 Dogfooding Adoption Matrix, V5-05 Filesystem/Storage/Cache Adoption, V5-06 DataTransfer/SecureRequest/Schema
Metadata Compilation, V5-07 Modern PHP 8.x Language Adoption, V5-08 Attribute/Annotation Runtime, V5-09 DI & Autowiring
Clean Code, V5-10 Data Structures Adoption, V5-11 Arrhae/Collection/JSON Productization, V5-12 Enum & Domain Value
Cleanup, V5-13 Superglobal Isolation, V5-14 Router Completion, V5-15 Naming & Structure Convergence, V5-16
Traits/Multi-Class/Empty Classes Cleanup, V5-17 Serialization & Payload Safety, V5-18 Async/Concurrency/Parallelism
Adoption, V5-19 Pooling & Resource Lifecycle, V5-20 Hot Path Cache & Compiled Metadata, V5-21 Tooling Gates & Custom
Rector Rules, V5-22 E2E Tests / Reference Runtime Proof

### COMPLETE (1)

V5-23 Final V5 Truth Report — `EVIDENCE/v5/v5-23-final-truth-report.md`

## Completed

### V4 Final Closure Pass — COMPLETE / GREEN

**Date:** 2026-05-10

**V4-12 Security & Policy Runtime:** COMPLETE / GREEN
HMAC request signing, replay protection, default-deny policy engine, feature flags, service discovery.

**V4-13 System Design Runtime Kit:** COMPLETE / GREEN
Architecture reports, capacity estimation, failure simulation.

**V4-14 Runtime Doctor & Control Plane:** COMPLETE / GREEN
Liveness, readiness, health endpoints wired into HTTP runtime, doctor foundation.

**V4-15 Reference Applications:** COMPLETE / GREEN
13 reference apps with 36 smoke tests.

**V4-16 Benchmarks & Production Proof:** COMPLETE / GREEN
7 benchmark workloads, evidence report produced.

**V4-17 Optional Runtime Adapters:** COMPLETE / GREEN
Adapter interface + ReactPhpAdapter proved. RoadRunner/Swoole/FrankenPHP/Workerman ROADMAP.

**Final validation:** 7451 tests, 21635 assertions, 0 failures. PHPStan 0 errors. 7/7 governance GREEN.

### V4 Second-Half Execution Queue

**Date:** 2026-05-10

All V4-12 through V4-17 items completed. See V4 Final Closure Pass section above.

## Completed

### DataStack/Data Structure Universe Plan — CLOSED / PLANNED

**Date:** 2026-05-09
**Outcome:** Added the AvaX-normalized master plan for implementing the DataStack/Data structure universe without
creating production scaffolding, fake runtime guarantees, or forbidden generic folder buckets.
**Evidence:** `EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md`

### V2 Engine Implementation Phase — CLOSED / GREEN

**Date:** 2026-05-07
**Outcome:** All 72 components production-ready with canonical structure. All 4 previously LOCKED_NON_V1 components
promoted to COMPLETE.
**Validation:** composer (6887 classes), phpunit (598 tests, 2482 assertions), phpstan (0 errors), all governance checks
GREEN.

**Components completed:**

1. API/Surface — REST, JSON:API, Webhooks, RPC slices with canonical naming
2. Integration/ObjectStorage — promoted from labs with full canonical shape
3. Operations/Resilience — Timeout, Bulkhead, DeadLetter, Outbox, Backpressure, LoadShedding
4. Operations/Observability — Logging, Telemetry, Redaction, Tracing capabilities
5. Operations/RuntimeSupervision — Supervisor, WorkerLifecycle, WorkerRestart, Health (promoted from LOCKED_NON_V1)
6. Operations/MessageBus — Command, Query, Event, Transactional dispatch flows
7. Operations/Delivery — Build, Compile, SmokeChecks, Evidence, Rollback flows (promoted from LOCKED_NON_V1)
8. Operations/Realtime — ConnectClient, DisconnectClient, BroadcastToChannel, SubscribeToChannel flows + Configuration +
   Foundation/Failure (promoted from LOCKED_NON_V1)
9. Operations/MemoryLifecycle — AllocateMemory, ReleaseMemory, CheckMemoryHealth, RunGarbageCollection flows (promoted
   from LOCKED_NON_V1)
10. Operations/Tasks — TaskRunner, TaskQueue, TaskScheduler, TaskRetry capabilities
11. Operations/Filesystem — ReadFile, WriteFile, DeleteFile, ListDirectory flows
12. V2 API Engine — ApiBlueprint, OpenAPI, GraphQL (previously closed)

**Status:** All 72 components COMPLETE. LOCKED_NON_V1: 0. V2 Platform Baseline CLOSED / GREEN.

### V3 Implementation — LOCKED

V3 planning may continue. V3 production implementation remains locked until V2 platform baseline is proven GREEN and
SystemDesignKit scope is approved.
