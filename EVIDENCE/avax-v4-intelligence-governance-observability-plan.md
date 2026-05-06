Da. Evo ga kao **save-ready `.md` plan**. Ja bih ga sačuvao kao:

```text
Code-Review-And-ToDo/avax-v4-intelligence-governance-observability-plan.md
```

Plan se oslanja na postojeći V3 smer: AvaX ne treba da bude infrastruktura, nego framework koji disciplinuje korišćenje infrastrukture kroz contracts, ports, adapters, orchestration, failure policy, diagnostics, testing harness, simulation model, reference architecture i operator visibility.  Takođe se naslanja na cache roadmap gde se V4 već prirodno pojavljuje kao distributed cache promotion, cache warming i metrics backends, plus princip “observability as first-class concern”.

# AvaX V4 Master Plan: Intelligence, Governance, Observability

## Status

Future roadmap.

V4 is locked until:

```text
[ ] V1 Production Kernel is GREEN.
[ ] V2 Enterprise Platform baseline is at least YELLOW/GREEN.
[ ] V3 SystemDesign validation layer has real proof.
[ ] At least two reference architectures validate through V3.
[ ] Architecture tests catch at least one real design violation.
```

V4 must not be implemented before V1, V2, and V3 are proven enough.

V4 can be planned earlier.

V4 can be documented earlier.

V4 can be prepared as experimental labs work only after V3 has a working validation foundation.

---

## 0. V4 thesis

V4 is the layer where AvaX becomes self-aware enough to help maintain, govern, explain, and evolve itself.

V1 makes AvaX real.

V2 makes AvaX enterprise-useful.

V3 makes AvaX system-design-intelligent.

V4 makes AvaX self-governing, observable, recoverable, and AI-assisted.

```text
V4 turns AvaX from a framework that validates architecture
into a framework that continuously understands, explains,
observes, and protects architecture.
```

V4 is not a feature dump.

V4 is not another infrastructure fantasy layer.

V4 is not custom Kafka, custom RocksDB, custom Raft, custom CDN, custom transcoder, custom search engine, custom storage engine, or low-level networking.

V4 is the intelligence and governance layer around the platform.

---

## 1. One-line definition

```text
V4 governs, observes, explains, and safely evolves AvaX systems.
```

Long form:

```text
AvaX V4 is an intelligence, governance, observability, and recovery layer
for continuously understanding framework state, validating architectural drift,
guiding AI-assisted development, tracking runtime signals, and safely evolving
large application systems.
```

---

## 2. Hard boundary

AvaX V4 must not become infrastructure.

V4 must not implement:

```text
custom Kafka
custom Redis Cluster
custom RocksDB
custom Elasticsearch
custom FFmpeg
custom CDN
custom ML recommendation engine
custom Raft/Paxos consensus
custom high-frequency matching engine
custom video transcoding engine
custom distributed database
low-level networking stack
```

V4 may implement:

```text
intelligence layer
governance layer
evidence graph
architecture memory
drift detection
recovery planning
AI prompt generation
validation evidence tracking
runtime observability
SLO/SLA monitoring models
operator diagnostics
safe evolution workflows
```

Correct mental model:

```text
AvaX does not replace infrastructure.
AvaX disciplines, observes, validates, and governs infrastructure usage.
```

---

## 3. V4 positioning

V3 answers:

```text
Is this architecture valid?
Can it handle load?
What happens when dependencies fail?
Are commands idempotent?
Are projections eventually consistent?
Does every external call have timeout/retry/circuit policy?
```

V4 answers:

```text
What state is this system currently in?
What changed?
What degraded?
What architectural rule was violated?
What old muscle exists in backups or history?
What should be restored?
What must not be restored?
What evidence proves this slice is green?
What should the AI agent do next?
What stage is allowed right now?
What runtime signals prove or disprove our design assumptions?
```

---

## 4. V4 core product idea

```text
AvaX Intelligence Recovery Engine
```

This is the most original and useful V4 idea.

It formalizes the workflow already happening manually:

```text
scan current tree
scan avax-backup.txt
scan Framework.txt
scan Components.txt
scan git history
classify old muscle
map old behavior to V1/V2/V3/V4
detect skeleton-only components
detect missing behavior
detect broken references
detect architecture drift
generate safe recovery plan
generate focused tests
require proof report
block promotion without evidence
```

The point is not to auto-generate random code.

The point is to guide recovery and evolution safely.

---

## 5. V4 major pillars

## 5.1 Architecture Intelligence

Purpose:

```text
Give AvaX a machine-readable understanding of its own architecture.
```

Capabilities:

```text
IndexProjectKnowledge
ReadGovernanceRules
ReadHowToRules
MapComponentOwnership
ClassifyComponentMuscle
DetectArchitectureDrift
DetectPublicSurfaceViolation
DetectRuntimeLeak
DetectGenericNamingViolation
CompareCurrentTreeWithBackup
CompareCurrentTreeWithGitHistory
ClassifyMissingBehavior
ClassifySkeletonComponent
ExplainComponentState
```

Output examples:

```text
Component Application/Cache is muscular.
Component DataStack/Database is proven-partial.
Component Identity/Auth is partial.
Component X is skeleton-only.
Component Y has old behavior in avax-backup.txt.
Component Z violates PublicSurface boundary.
```

---

## 5.2 Evidence Graph

Purpose:

```text
Track what is actually proven, not what documents claim.
```

AvaX should know:

```text
which tests prove which behavior
which reports prove which stage
which commands were run
which static checks passed
which component is green/yellow/red
which proof slice unlocked the next stage
which claims are stale
which claims are contradicted by current evidence
```

Core concepts:

```text
EvidenceNode
EvidenceSource
ValidationCommand
ValidationResult
ProofSlice
BehaviorClaim
Contradiction
StageGate
PromotionDecision
```

Example:

```text
DatabaseBuilder assembly is GREEN because:
- 12 tests pass
- 72 assertions pass
- focused PHPStan is green
- architecture checks pass
- SQLite schema runtime works through real PDOExecutor
```

This prevents “documentation optimism”.

---

## 5.3 AI Delivery Governance

Purpose:

```text
Make AI-assisted development safe, staged, and evidence-driven.
```

Capabilities:

```text
ResolveActiveStage
RejectOutOfScopeWork
GenerateCodexPrompt
GenerateAgentTask
ReviewAgentOutput
RequireProofBeforePromotion
EnforceNoV2BeforeV1Green
EnforceNoV3BeforeV2Baseline
EnforceHowToRules
DetectBroadRefactorRisk
DetectSkeletonRecovery
DetectUnsafeRestore
WriteCompletionReport
WriteNextAllowedAction
```

Agent workflow:

```text
1. Read EXECUTION.md.
2. Read CURRENT_TRUTH.md.
3. Read relevant how-to-*.md.
4. Resolve active stage.
5. Refuse out-of-stage work.
6. Work only on allowed slice.
7. Run required validation.
8. Write proof report.
9. Update truth documents.
10. State next allowed action.
```

V4 should make this automatic.

---

## 5.4 Runtime Observability

Purpose:

```text
Expose operational truth from running systems.
```

This generalizes the cache V4 idea of metrics backends and observability into the whole framework. Cache already points toward distributed cache promotion, cache warming, metrics backends, and observability as a first-class concern.

Global observability capabilities:

```text
RecordLatency
RecordFailure
RecordRetry
RecordTimeout
RecordCircuitOpen
RecordBulkheadReject
RecordQueueDepth
RecordProjectionLag
RecordCacheHitRatio
RecordCacheMiss
RecordStaleServed
RecordLockWait
RecordDeadLetter
RecordOutboxLag
RecordConnectionPoolPressure
RecordMemoryGrowth
RecordWorkerRestart
RecordSloViolation
```

Backends:

```text
InMemoryMetricsBackend
FileMetricsBackend
PrometheusMetricsBackend
OpenTelemetryTraceBackend
NullMetricsBackend
```

Important rule:

```text
Observability must not leak sensitive payloads.
```

Payload logging should be opt-in, redacted by default, and policy-governed.

---

## 5.5 Runtime Diagnostics

Purpose:

```text
Turn runtime signals into actionable diagnosis.
```

Diagnostics should answer:

```text
Why is this route slow?
Why is this queue growing?
Why is cache hit ratio dropping?
Which projection is lagging?
Which external dependency is causing retries?
Which hot path violates latency budget?
Which component is creating static state risk?
Which worker is not reset-safe?
```

Capabilities:

```text
DiagnoseLatencyBudget
DiagnoseQueueBacklog
DiagnoseProjectionLag
DiagnoseCacheStampedeRisk
DiagnoseExternalDependencyFailure
DiagnoseRuntimeStateLeak
DiagnoseWorkerMemoryGrowth
DiagnoseRetryStorm
DiagnoseHotKeyRisk
```

---

## 5.6 Safe Recovery Engine

Purpose:

```text
Recover old behavior without resurrecting old architecture.
```

Inputs:

```text
avax-backup.txt
Framework.txt
Components.txt
git history
current source tree
current tests
how-to-*.md rules
CURRENT_TRUTH.md
EXECUTION.md
```

Recovery rules:

```text
Old behavior is valuable.
Old structure is not automatically valuable.
Skeleton code is not acceptable.
Recovered code must fit current architecture.
Recovered code must have tests.
Recovered code must pass static validation.
Recovered code must not unlock V2/V3 prematurely.
```

Recovery stages:

```text
Recovery-00: Inventory old muscle
Recovery-01: Classify V1/V2/V3/V4 target
Recovery-02: Compare with current tree
Recovery-03: Detect missing behavior
Recovery-04: Generate safe restore plan
Recovery-05: Restore one proof slice
Recovery-06: Add behavior tests
Recovery-07: Run validation
Recovery-08: Write proof report
Recovery-09: Update truth documents
```

---

## 5.7 Architecture Memory

Purpose:

```text
Preserve why the system is shaped the way it is.
```

AvaX should know:

```text
why a folder exists
why a public surface exists
why an adapter boundary exists
why a component is experimental
why a legacy alias exists
why a feature is V2 and not V1
why a V3 model is validation-only
why a V4 intelligence feature is not runtime infrastructure
```

Artifacts:

```text
ArchitectureDecision
ComponentHistory
RecoveryDecision
NamingDecision
BoundaryDecision
PromotionDecision
DeprecationDecision
```

This should reduce repeated debates and accidental regressions.

---

## 5.8 Operator Console

Purpose:

```text
Give humans a clear operational view of framework and application state.
```

Possible CLI commands:

```text
avax intelligence:state
avax intelligence:drift
avax intelligence:recovery-plan
avax intelligence:explain-component Database
avax intelligence:proof DatabaseBuilder
avax governance:stage
avax governance:validate
avax governance:next
avax observability:summary
avax observability:latency
avax observability:queues
avax observability:projections
avax observability:slo
avax diagnostics:runtime
avax diagnostics:hot-path
```

The console should be blunt:

```text
GREEN means proven.
YELLOW means partial.
RED means blocked.
UNKNOWN means not measured.
```

---

## 6. Suggested V4 project tree

Start experimental:

```text
labs/IntelligenceKit/
  README.md
  experiments/
  fixtures/
  reports/
  scenarios/
  spikes/
```

Promote only after proof:

```text
components/Intelligence/
  System/
    PublicSurface/
      SystemIntelligence.php
      ArchitectureMemory.php
      EvidenceGraph.php
      RecoveryPlanner.php
      GovernanceAdvisor.php
    Capabilities/
      IndexProjectKnowledge/
      ReadGovernanceRules/
      DetectArchitectureDrift/
      ClassifyComponentMuscle/
      CompareBackupWithCurrentTree/
      CompareGitHistoryWithCurrentTree/
      ProposeSafeRecoveryPlan/
      ExplainSystemState/
      RecordValidationEvidence/
      TrackStageLock/
      GenerateAgentBrief/
      ReviewAgentOutput/
    Foundation/
      EvidenceNode.php
      EvidenceSource.php
      BehaviorClaim.php
      ProofSlice.php
      StageGate.php
      ComponentState.php
      RecoveryDecision.php
    Configuration/
      BuildSystemIntelligence.php
```

```text
components/Operations/Observability/
  System/
    PublicSurface/
      Observability.php
      Metrics.php
      Traces.php
      RuntimeEvents.php
      Diagnostics.php
    Capabilities/
      RecordLatency/
      RecordFailure/
      RecordRetry/
      RecordTimeout/
      RecordQueueDepth/
      RecordProjectionLag/
      RecordCacheHitRatio/
      RecordSloViolation/
      DiagnoseHotPath/
      DiagnoseRuntimeState/
    Foundation/
      MetricSample.php
      TraceSpan.php
      RuntimeEvent.php
      DiagnosticFinding.php
      SloBudget.php
    Configuration/
      BuildObservability.php
```

```text
components/Delivery/Governance/
  System/
    PublicSurface/
      DeliveryGate.php
      StageLock.php
      ValidationEvidence.php
      AgentBrief.php
    Capabilities/
      ResolveActiveStage/
      EnforceHowToRules/
      EnforceNoV2BeforeV1Green/
      EnforceNoV3BeforeV2Baseline/
      RequireProofBeforePromotion/
      GenerateCodexPrompt/
      ReviewAgentOutput/
      WriteCompletionReport/
      WriteNextAllowedAction/
    Foundation/
      Stage.php
      StageStatus.php
      ValidationCommand.php
      ValidationResult.php
      AgentTask.php
    Configuration/
      BuildDeliveryGovernance.php
```

```text
components/SystemDesign/RuntimeValidation/
  System/
    PublicSurface/
      RuntimeScenario.php
      RuntimeValidator.php
      ArchitectureHealth.php
    Capabilities/
      ValidateHotPathBudget/
      ValidateRetryPolicy/
      ValidateIdempotency/
      ValidateOutboxPresence/
      ValidateDeadLetterPolicy/
      ValidateProjectionLag/
      ValidateExternalPortTimeout/
      ValidateCacheStampedeProtection/
      ValidateSloBudget/
    Foundation/
      RuntimeScenarioResult.php
      ArchitectureViolation.php
      ValidationRule.php
      HealthScore.php
```

---

## 7. V4 staged execution

## V4-00: V4 Lock and Charter

```text
[ ] Create V4 charter document.
[ ] Define hard boundary.
[ ] Mark V4 as locked.
[ ] Explicitly forbid infrastructure implementation.
[ ] Define dependency on V1/V2/V3.
[ ] Define promotion rules from labs to components.
```

Acceptance:

```text
[ ] V4 cannot be implemented while V1 is RED.
[ ] V4 cannot bypass V3 validation layer.
[ ] V4 has documented non-goals.
```

---

## V4-01: IntelligenceKit Labs Foundation

```text
[ ] Create labs/IntelligenceKit.
[ ] Add README.md.
[ ] Add experimental warning.
[ ] Add first project-index fixture.
[ ] Add first evidence graph fixture.
[ ] Add first recovery-plan fixture.
```

Acceptance:

```text
[ ] No production component code.
[ ] No runtime dependency.
[ ] Pure experiments and fixtures.
```

---

## V4-02: Evidence Graph Prototype

```text
[ ] Model EvidenceNode.
[ ] Model EvidenceSource.
[ ] Model ValidationCommand.
[ ] Model ValidationResult.
[ ] Model BehaviorClaim.
[ ] Model ProofSlice.
[ ] Model Contradiction.
[ ] Generate report from existing recovery reports.
```

Acceptance:

```text
[ ] Can represent DatabaseBuilder proof.
[ ] Can represent QueryBuilder proof.
[ ] Can represent a contradiction between CURRENT_TRUTH and validation.
```

---

## V4-03: Architecture Memory Prototype

```text
[ ] Index how-to-*.md.
[ ] Index EXECUTION.md.
[ ] Index CURRENT_TRUTH.md.
[ ] Index recovery reports.
[ ] Index component AGENTS.md files.
[ ] Build component history map.
[ ] Build stage history map.
```

Acceptance:

```text
[ ] Can explain why DatabaseBuilder was repaired.
[ ] Can explain why V2 is locked.
[ ] Can explain why old Framework.txt code is archaeology, not direct restore.
```

---

## V4-04: Recovery Planner Prototype

```text
[ ] Parse avax-backup.txt.
[ ] Parse Framework.txt.
[ ] Parse Components.txt.
[ ] Parse git history snapshots.
[ ] Classify old behavior by component.
[ ] Classify old behavior by V1/V2/V3/V4 target.
[ ] Detect missing behavior in current tree.
[ ] Generate safe recovery plan.
```

Acceptance:

```text
[ ] Does not write production code.
[ ] Produces report only.
[ ] Rejects skeleton-only recovery.
[ ] Marks risky restore candidates.
```

---

## V4-05: Agent Governance Prototype

```text
[ ] Read active stage from EXECUTION.md.
[ ] Read project truth from CURRENT_TRUTH.md.
[ ] Read how-to-*.md rules.
[ ] Generate Codex prompt for one allowed slice.
[ ] Reject out-of-stage tasks.
[ ] Require proof report.
[ ] Require validation command list.
```

Acceptance:

```text
[ ] Can generate a correct prompt for a V1 repair slice.
[ ] Refuses V2/V3 implementation while V1 is not green.
[ ] Includes exact validation commands.
```

---

## V4-06: Observability Model Prototype

```text
[ ] Define MetricSample.
[ ] Define RuntimeEvent.
[ ] Define TraceSpan.
[ ] Define SloBudget.
[ ] Define DiagnosticFinding.
[ ] Add InMemoryMetricsBackend.
[ ] Add NullMetricsBackend.
```

Acceptance:

```text
[ ] Cache can record hit/miss.
[ ] Database can record query latency.
[ ] Queue can record depth.
[ ] Projection can record lag.
[ ] No sensitive payloads are logged by default.
```

---

## V4-07: Diagnostics Prototype

```text
[ ] Diagnose latency budget violation.
[ ] Diagnose queue backlog.
[ ] Diagnose projection lag.
[ ] Diagnose retry storm.
[ ] Diagnose cache stampede risk.
[ ] Diagnose external dependency timeout.
```

Acceptance:

```text
[ ] A diagnostic report explains cause, evidence, and suggested action.
[ ] Diagnostics can distinguish unknown from healthy.
```

---

## V4-08: Operator Console Prototype

```text
[ ] Add avax intelligence:state.
[ ] Add avax intelligence:drift.
[ ] Add avax governance:stage.
[ ] Add avax governance:next.
[ ] Add avax observability:summary.
[ ] Add avax diagnostics:runtime.
```

Acceptance:

```text
[ ] CLI output is plain, strict, and evidence-based.
[ ] No fake green status.
[ ] Every green claim links to proof.
```

---

## V4-09: Promote IntelligenceKit to components/Intelligence

Promotion allowed only when:

```text
[ ] V1 is GREEN.
[ ] V2 baseline is YELLOW/GREEN.
[ ] V3 has at least one real architecture validation proof.
[ ] IntelligenceKit can represent real proof reports.
[ ] RecoveryPlanner produces useful plans.
[ ] Agent governance blocks out-of-stage work.
[ ] Observability model has at least two real component integrations.
```

---

## 8. V4 acceptance criteria

V4 is GREEN only when:

```text
[ ] AvaX can explain current system state.
[ ] AvaX can detect architecture drift.
[ ] AvaX can classify component muscle.
[ ] AvaX can compare backup/history against current tree.
[ ] AvaX can generate safe recovery plans.
[ ] AvaX can generate AI agent briefs from active stage.
[ ] AvaX can reject unsafe stage violations.
[ ] AvaX can track validation evidence.
[ ] AvaX can connect runtime metrics to architecture rules.
[ ] AvaX can produce operator diagnostics.
[ ] AvaX can prove all of the above with tests and reports.
```

---

## 9. V4 anti-goals

V4 must not:

```text
[ ] Auto-restore code without review.
[ ] Generate production code without tests.
[ ] Treat old code as automatically valid.
[ ] Treat new skeleton code as progress.
[ ] Unlock V2/V3/V4 while V1 is red.
[ ] Hide validation failures.
[ ] Mark unknown as green.
[ ] Replace real infrastructure.
[ ] Become a generic AI agent framework.
[ ] Become a dashboard-only observability toy.
```

---

## 10. Final V4 sentence

```text
AvaX V4 is the intelligence and governance layer that helps the framework
understand itself, observe itself, recover lost behavior safely, guide AI-assisted
development, and prevent architectural drift.
```

Even shorter:

```text
V4 makes AvaX self-governing.
```

My direct take: this is a strong V4. Not because it sounds futuristic, but because it solves your actual problem: keeping a large, AI-assisted, architecture-heavy framework from collapsing into either skeleton code or restored legacy chaos.
