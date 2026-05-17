Da. Postojeći V4 ostaje osnova, ali ga dopunjavamo konkretnim “product muscle” slojem: Runtime Worker Safety, Database
Intelligence, Cache Intelligence, Security Certification, OpenTelemetry, Plugin Capability Security, Template
Compiler/View Doctor, Architecture Doctor i Reference Architecture Products. Time V4 prestaje da bude samo
governance/intelligence plan i postaje proizvodni sloj. Postojeći V4 već dobro pokriva self-governance, evidence graph,
architecture memory, runtime observability, diagnostics i safe recovery. Dodatni mišići su direktno inspirisani onim što
smo izvukli iz SystemDRD kursa: persistent runtime discipline, EAV/JSONB/indexing, cache hierarchy,
OpenTelemetry/Prometheus/SLO, plugin security i template compiler.

Sačuvao bih ovo kao:

```txt
EVIDENCE/plans/avax-v4-product-intelligence-master-plan.md
```

````md
# AvaX V4 Master Plan: Product Intelligence, Governance, Observability and Production Muscles

## Status

Future roadmap.

V4 is locked until:

- [ ] V1 Production Kernel is GREEN.
- [ ] V2 Enterprise Platform baseline is GREEN.
- [ ] V3 SystemDesignKit has real validation proof.
- [ ] At least two reference architectures validate through V3.
- [ ] Architecture tests catch at least one real design violation.
- [ ] Golden Path Runtime Proof exists.
- [ ] Security blockers are classified and either fixed or explicitly marked as production blockers.

V4 can be planned earlier.

V4 must not be implemented as production code until V1, V2, and V3 gates allow it.

V4 may start experimentally in `labs/` only after V3 has a stable validation foundation.

---

## 0. V4 Thesis

V1 makes AvaX real.

V2 makes AvaX enterprise-useful.

V3 makes AvaX system-design-intelligent.

V4 makes AvaX product-ready, self-governing, observable, diagnosable, secure, recoverable, and AI-assisted.

V4 turns AvaX from:

```text
a framework that validates architecture
````

into:

```text
a framework that understands, observes, explains, protects, diagnoses, and safely evolves architecture.
```

V4 is not another feature dump.

V4 is not infrastructure replacement.

V4 is not custom Kafka, Redis Cluster, RocksDB, Elasticsearch, FFmpeg, CDN, Raft, video transcoder, distributed
database, or low-level networking stack.

V4 is the product intelligence layer around AvaX.

---

## 1. One-Line Definition

```text
V4 makes AvaX self-governing, observable, diagnosable, secure, and product-ready.
```

Long form:

```text
AvaX V4 is a product intelligence, governance, observability, diagnostics,
security, recovery, and developer-experience layer for safely building,
validating, explaining, operating, and evolving large PHP systems.
```

---

## 2. Hard Boundary

AvaX does not replace infrastructure.

AvaX disciplines infrastructure usage.

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
custom low-level networking stack
```

V4 may implement:

```text
architecture intelligence
evidence graph
architecture memory
governance enforcement
AI delivery governance
runtime observability
operator diagnostics
security certification
database intelligence
cache intelligence
worker safety doctor
plugin capability security
template compiler / view doctor
reference architecture products
safe recovery planning
```

Correct mental model:

```text
AvaX does not become the infrastructure.
AvaX becomes the framework that helps teams use infrastructure safely.
```

---

## 3. V4 Positioning

V3 answers:

```text
Is this architecture valid?
Can it handle load?
What happens when dependencies fail?
Are commands idempotent?
Are projections eventually consistent?
Does every external call have timeout/retry/circuit policy?
Does the system have a capacity, consistency, and messaging model?
```

V4 answers:

```text
What state is this system currently in?
What changed?
What degraded?
What architectural rule was violated?
What runtime signal proves or disproves our design assumption?
What old muscle exists in backups or history?
What should be restored?
What must not be restored?
What evidence proves this slice is GREEN?
What should the AI agent do next?
What stage is allowed right now?
What risk should an operator fix first?
```

---

# PART I: V4 Intelligence Core

## 4. Architecture Intelligence

### Purpose

Give AvaX a machine-readable understanding of its own architecture.

### Capabilities

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
ExplainOwnershipBoundary
ExplainPromotionRisk
```

### Example Output

```text
Component Application/Cache is muscular.
Component DataStack/Database is proven-partial.
Component Identity/Auth is partial.
Component X is skeleton-only.
Component Y has old behavior in avax-backup.txt.
Component Z violates PublicSurface boundary.
```

---

## 5. Evidence Graph

### Purpose

Track what is actually proven, not what documents merely claim.

### Core Concepts

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
ComponentState
EvidenceTimeline
```

### Required Behavior

AvaX must know:

```text
which tests prove which behavior
which reports prove which stage
which commands were run
which static checks passed
which component is GREEN/YELLOW/RED
which proof slice unlocked the next stage
which claims are stale
which claims are contradicted by current evidence
```

### Example

```text
DatabaseBuilder is GREEN because:
- 12 tests pass
- 72 assertions pass
- focused PHPStan is clean
- architecture checks pass
- SQLite schema runtime works through real PDOExecutor
```

### Rule

```text
No proof, no GREEN.
```

---

## 6. AI Delivery Governance

### Purpose

Make AI-assisted development staged, safe, narrow, and evidence-driven.

### Capabilities

```text
ResolveActiveStage
RejectOutOfScopeWork
GenerateCodexPrompt
GenerateAgentTask
ReviewAgentOutput
RequireProofBeforePromotion
EnforceNoV2BeforeV1Green
EnforceNoV3BeforeV2Baseline
EnforceNoV4BeforeV3Proof
EnforceHowToRules
DetectBroadRefactorRisk
DetectSkeletonRecovery
DetectUnsafeRestore
WriteCompletionReport
WriteNextAllowedAction
```

### Required Agent Workflow

```text
1. Read CURRENT_TRUTH.md.
2. Read AGENTS.md.
3. Read EXECUTION.md.
4. Read TODO.md.
5. Read relevant .agents/how-to/*.md.
6. Resolve active stage.
7. Refuse out-of-stage work.
8. Work only on allowed slice.
9. Run required validation.
10. Write proof report.
11. Update truth documents.
12. State next allowed action.
```

---

## 7. Safe Recovery Engine

### Purpose

Recover old behavior without resurrecting old architecture.

### Inputs

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
TODO.md
```

### Recovery Rules

```text
Old behavior is valuable.
Old structure is not automatically valuable.
Skeleton code is not acceptable.
Recovered code must fit current architecture.
Recovered code must have tests.
Recovered code must pass static validation.
Recovered code must not unlock V2/V3/V4 prematurely.
Recovered code must not reintroduce forbidden naming.
```

### Recovery Stages

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

## 8. Architecture Memory

### Purpose

Preserve why the system is shaped the way it is.

### Artifacts

```text
ArchitectureDecision
ComponentHistory
RecoveryDecision
NamingDecision
BoundaryDecision
PromotionDecision
DeprecationDecision
StageDecision
RuntimeDecision
SecurityDecision
```

### AvaX Must Be Able To Explain

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

---

# PART II: V4 Product Muscles

## 9. Runtime Worker Safety Doctor

### Purpose

Prove that AvaX is safe in long-lived PHP runtimes.

This is central to AvaX identity because AvaX targets PHP-FPM, FrankenPHP, RoadRunner, Swoole, Workerman, ReactPHP, Amp,
Fibers, and worker-style runtimes.

### Problems Solved

```text
request state leaks
static state leaks
memory growth
unclosed resources
missing reset hooks
unsafe singletons
worker lifecycle drift
signal handling gaps
runtime mode confusion
```

### Owner

```text
framework/System/Capabilities/RuntimeSafety/
framework/System/Capabilities/StateReset/
framework/System/Capabilities/Diagnostics/
components/Operations/RuntimeSupervision/
```

### Product Commands

```text
php avax runtime:doctor
php avax worker:doctor
php avax memory:doctor
php avax reset:doctor
php avax state:leaks
```

### Capabilities

```text
DetectStaticStateLeak
DetectRequestStateLeak
DetectWorkerMemoryGrowth
VerifyResetHooks
VerifyWorkerSafeComponent
CheckSignalHandling
CheckResourceCleanup
ExplainRuntimeSafety
```

### Required Tests

```text
worker request loop does not leak request state
reset hooks run after request
memory growth is detected
static state risk is reported
unsafe singleton is flagged
runtime doctor reports UNKNOWN instead of fake GREEN
```

### Non-Goals

```text
Do not implement custom Swoole/RoadRunner.
Do not replace runtime servers.
Do not hide state leaks.
```

---

## 10. Database Intelligence

### Purpose

Make AvaX able to detect and explain database risks before production pain.

### Problems Solved

```text
slow queries
N+1 queries
unsafe raw SQL
missing bindings
schema drift
missing indexes
transaction leaks
deadlock-prone flows
read/write split mistakes
JSONB/EAV query risks
connection pool pressure
```

### Owner

```text
components/DataStack/Database/
components/DataStack/Persistence/
components/DeveloperTools/Diagnostics/
```

### Product Commands

```text
php avax db:doctor
php avax db:slow
php avax db:n-plus-one
php avax db:schema:diff
php avax db:index:advise
php avax db:transactions:audit
php avax db:jsonb:audit
php avax db:eav:audit
php avax db:pool
```

### Capabilities

```text
QueryTimeline
SlowQueryDetection
NPlusOneDetection
QueryFingerprint
TransactionAudit
SchemaDriftDetection
IndexRecommendations
ReadWriteSplitDiagnostics
ConnectionPoolPressure
JsonbStorageAudit
EavModelingAudit
QueryShapeAnalysis
ShardingAdvice
```

### Required Tests

```text
slow query is detected
N+1 pattern is detected
unsafe SQL interpolation is flagged
missing binding is flagged
schema drift is detected
index recommendation is generated
transaction leak is detected
JSONB/EAV risk is reported
```

### Non-Goals

```text
Do not build a custom database.
Do not replace PostgreSQL/MySQL.
Do not auto-create production indexes without explicit approval.
Do not pretend all query optimization can be automated.
```

---

## 11. Cache Intelligence

### Purpose

Make AvaX cache behavior observable, explainable, and safe.

### Problems Solved

```text
low hit ratio
cache stampede
hot keys
stale data risk
bad TTL choices
cache node failure
missing warming
unclear invalidation
write-through/write-behind confusion
```

### Owner

```text
components/Application/Cache/
components/Operations/Observability/
components/DeveloperTools/Diagnostics/
```

### Product Commands

```text
php avax cache:doctor
php avax cache:warm
php avax cache:explain <key>
php avax cache:hot-keys
php avax cache:stampede
php avax cache:hit-ratio
```

### Capabilities

```text
CacheHierarchy
CacheHitRatio
CacheMissTracking
CacheStampedeDetection
CacheWarming
TagInvalidation
StaleWhileRevalidateDiagnostics
ConsistentHashing
CacheNodeHealth
HotKeyDetection
CacheKeyExplanation
```

### Required Tests

```text
cache hit/miss metrics are recorded
stampede risk is detected
stale-while-revalidate behavior is explained
cache warming path is validated
hot key is detected
cache node health is reported
```

### Non-Goals

```text
Do not build Redis Cluster.
Do not build Memcached.
Do not hide stale data risks.
```

---

## 12. Security Certification Suite

### Purpose

Make production security measurable.

Security is not decoration.
Security is a production gate.

### Problems Solved

```text
unsafe encryption
AES-CBC without authentication
unserialize on user/session data
@unserialize suppression
unsafe SQL interpolation
weak cookies
missing CSRF
missing signed request checks
secret leakage
missing redaction
unsafe plugin permissions
field encryption gaps
```

### Owner

```text
components/Security/
components/HTTP/Security/
components/Identity/
components/DeveloperTools/Diagnostics/
```

### Product Commands

```text
php avax security:doctor
php avax security:audit
php avax crypto:doctor
php avax secrets:doctor
php avax sessions:doctor
php avax sql:safety
```

### Capabilities

```text
DetectUnsafeEncryption
DetectUnserializeUsage
DetectSuppressedSecurityFailure
DetectUnsafeSql
DetectSecretLeak
CheckCookieSecurity
CheckCsrfCoverage
CheckSignedRequestCoverage
CheckRedactionCoverage
CheckFieldEncryption
SecurityFinding
SecurityCertificationReport
```

### Required Tests

```text
unsafe encryption is flagged
unserialize usage is flagged
@ suppression is flagged
unsafe SQL interpolation is flagged
missing CSRF is detected
secret leak is detected
redaction prevents sensitive payload logging
```

### Final Rule

```text
AvaX cannot claim production-ready while Security Certification is RED.
```

---

## 13. Observability / OpenTelemetry Suite

### Purpose

Expose operational truth from running systems.

### Problems Solved

```text
unknown latency
unknown queue depth
unknown retry storms
unknown projection lag
unknown cache hit ratio
unknown SLO violations
untraceable request flow
missing correlation IDs
payload leakage in logs
```

### Owner

```text
components/Operations/Observability/
components/Operations/Logging/
components/DeveloperTools/Diagnostics/
```

### Product Commands

```text
php avax observability:doctor
php avax observability:summary
php avax trace:explain
php avax metrics:export
php avax slo:check
php avax profile:hot-path
```

### Capabilities

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
OpenTelemetryTraceBackend
PrometheusMetricsBackend
InMemoryMetricsBackend
FileMetricsBackend
NullMetricsBackend
TraceCorrelation
RuntimeTimeline
```

### Required Tests

```text
request id is recorded
correlation id is propagated
trace id is propagated
latency sample is recorded
queue depth sample is recorded
SLO violation is detected
redaction prevents sensitive logs
OpenTelemetry export shape is valid
Prometheus metrics shape is valid
```

### Non-Goals

```text
Do not build Grafana.
Do not build Prometheus.
Do not log sensitive payloads by default.
```

---

## 14. Plugin Capability Security

### Purpose

Allow extension/plugin ecosystems without letting plugins destroy the framework boundary.

### Problems Solved

```text
unsafe plugins
unbounded plugin permissions
hidden dependencies
unsafe boot lifecycle
plugin namespace drift
plugin version incompatibility
plugin access to forbidden capabilities
plugin supply-chain risk
```

### Owner

```text
components/DeveloperTools/Extensions/
components/Security/
components/Intelligence/
```

### Product Commands

```text
php avax plugin:doctor
php avax plugin:permissions
php avax plugin:explain vendor/package
php avax plugin:graph
php avax plugin:security
```

### Capabilities

```text
PluginManifest
PluginCapabilityGrant
PluginPermission
PluginSandboxPolicy
PluginBootLifecycle
PluginDependencyGraph
VerifyPluginSafety
VerifyPluginCompatibility
ExplainPluginPermissions
DetectUnsafePluginAccess
```

### Required Tests

```text
plugin manifest is validated
plugin permission violation is detected
plugin dependency graph is built
unsafe plugin access is blocked
plugin lifecycle order is verified
```

### Non-Goals

```text
Do not build a marketplace in V4.
Do not implement WebAssembly sandboxing yet.
Do not allow plugins to bypass PublicSurface.
```

---

## 15. Template Compiler / View Doctor

### Purpose

Make the Presentation/View layer fast, safe, inspectable, and cacheable.

### Problems Solved

```text
unsafe escaping
slow view rendering
unclear compiled templates
template cache drift
missing sandboxing
XSS risk
hard-to-debug view logic
```

### Owner

```text
components/Presentation/View/
components/DeveloperTools/Diagnostics/
```

### Product Commands

```text
php avax view:compile
php avax view:cache
php avax view:clear
php avax view:explain home.index
php avax view:doctor
```

### Capabilities

```text
TemplateLexer
TemplateParser
TemplateAst
CompileTemplate
CacheCompiledView
EscapeTemplateOutput
ContextualEscaping
TemplateSandbox
ExplainTemplateAst
DetectUnsafeTemplateOutput
```

### Required Tests

```text
template compiles to cached PHP
escaped output is safe
raw output requires explicit marker
template AST is explainable
view cache invalidates correctly
unsafe output is flagged
```

### Non-Goals

```text
Do not build a full frontend framework.
Do not allow unsafe raw output by default.
Do not make template compiler part of V3.
```

---

## 16. Architecture Doctor Productization

### Purpose

Turn governance checks into a usable product command.

### Product Commands

```text
php avax architecture:doctor
php avax architecture:drift
php avax architecture:explain components/DataStack/Database
php avax architecture:why-green DataStack/Database
php avax architecture:next
```

### Checks

```text
component shape
duplicate ownership
namespace drift
public surface leaks
runtime leaks
forbidden names
empty folders
one-class-per-file
stale docs
stale evidence
unproven completion
skeleton components
missing tests
```

### Output Rules

```text
GREEN means proven.
YELLOW means partial.
RED means blocked.
UNKNOWN means not measured.
```

---

## 17. Reference Architecture Products

### Purpose

Turn V3 modeling into concrete, runnable, teachable product examples.

### Reference Products

```text
reference-architectures/url-shortener/
reference-architectures/distributed-rate-limiter/
reference-architectures/webhook-ingestion/
reference-architectures/notification-delivery/
reference-architectures/parking-lot/
reference-architectures/news-feed/
reference-architectures/payment-workflow/
```

### Each Reference Architecture Must Include

```text
runtime implementation
capacity model
consistency model
messaging model
failure model
observability report
security checklist
benchmark report
architecture doctor output
deployment assumptions
operator guide
```

### Required Commands

```text
php avax system-design:check reference-architectures/url-shortener
php avax architecture:doctor reference-architectures/url-shortener
php avax observability:summary reference-architectures/url-shortener
php avax security:doctor reference-architectures/url-shortener
```

---

# PART III: Suggested V4 Project Shape

## 18. Experimental First

```text
labs/IntelligenceKit/
  README.md
  fixtures/
  reports/
  scenarios/
  experiments/
```

```text
labs/ProductMuscles/
  RuntimeWorkerSafety/
  DatabaseIntelligence/
  CacheIntelligence/
  SecurityCertification/
  ObservabilitySuite/
  PluginCapabilitySecurity/
  TemplateCompiler/
```

No production promotion before proof.

---

## 19. Production Promotion Targets

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
components/DeveloperTools/Doctor/
  System/
    PublicSurface/
      Doctor.php
      DoctorReport.php
    Flows/
      RunArchitectureDoctor/
      RunRuntimeDoctor/
      RunSecurityDoctor/
      RunDatabaseDoctor/
      RunCacheDoctor/
      RunObservabilityDoctor/
    Capabilities/
      ExplainFinding/
      RankRisks/
      LinkEvidence/
      SuggestNextAction/
    Foundation/
      DoctorFinding.php
      DoctorStatus.php
      DoctorSeverity.php
```

---

# PART IV: V4 Staged Execution

## V4-00: V4 Product Charter

```text
[ ] Create V4 charter.
[ ] Define V4 hard boundary.
[ ] Mark V4 locked.
[ ] Add product muscle layer.
[ ] Define non-goals.
[ ] Define promotion rules.
```

Acceptance:

```text
[ ] V4 cannot bypass V1/V2/V3 gates.
[ ] V4 cannot become infrastructure replacement.
[ ] V4 has product muscles explicitly documented.
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

---

## V4-06: Runtime Worker Safety Doctor

```text
[ ] Detect static state leaks.
[ ] Detect request state leaks.
[ ] Detect worker memory growth.
[ ] Verify reset hooks.
[ ] Verify resource cleanup.
[ ] Verify signal handling assumptions.
[ ] Add runtime:doctor / worker:doctor / memory:doctor commands.
```

---

## V4-07: Database Intelligence

```text
[ ] Add query timeline model.
[ ] Add slow query detection.
[ ] Add N+1 detection.
[ ] Add query fingerprinting.
[ ] Add transaction audit.
[ ] Add schema drift detection.
[ ] Add index recommendation model.
[ ] Add JSONB/EAV audit.
[ ] Add db:doctor command family.
```

---

## V4-08: Cache Intelligence

```text
[ ] Add cache hit/miss metrics.
[ ] Add cache stampede detection.
[ ] Add hot key detection.
[ ] Add cache warming diagnostics.
[ ] Add stale-while-revalidate diagnostics.
[ ] Add cache:doctor command family.
```

---

## V4-09: Security Certification Suite

```text
[ ] Detect unsafe encryption.
[ ] Detect unserialize usage.
[ ] Detect @ suppression in sensitive code.
[ ] Detect unsafe SQL interpolation.
[ ] Detect secret leakage.
[ ] Detect missing CSRF/signed request protection.
[ ] Detect insecure cookies/sessions.
[ ] Add security:doctor command family.
```

---

## V4-10: Observability / OpenTelemetry Suite

```text
[ ] Add MetricSample.
[ ] Add RuntimeEvent.
[ ] Add TraceSpan.
[ ] Add SloBudget.
[ ] Add DiagnosticFinding.
[ ] Add InMemoryMetricsBackend.
[ ] Add NullMetricsBackend.
[ ] Add PrometheusMetricsBackend.
[ ] Add OpenTelemetryTraceBackend.
[ ] Add observability:doctor command.
```

---

## V4-11: Plugin Capability Security

```text
[ ] Add PluginManifest.
[ ] Add PluginPermission.
[ ] Add PluginCapabilityGrant.
[ ] Add PluginSandboxPolicy.
[ ] Add PluginBootLifecycle.
[ ] Add PluginDependencyGraph.
[ ] Add plugin:doctor command.
```

---

## V4-12: Template Compiler / View Doctor

```text
[ ] Add TemplateLexer.
[ ] Add TemplateParser.
[ ] Add TemplateAst.
[ ] Add CompileTemplate.
[ ] Add ContextualEscaping.
[ ] Add TemplateSandbox.
[ ] Add CacheCompiledView.
[ ] Add view:doctor command.
```

---

## V4-13: Architecture Doctor Productization

```text
[ ] Add architecture:doctor.
[ ] Add architecture:drift.
[ ] Add architecture:why-green.
[ ] Add architecture:next.
[ ] Connect to Evidence Graph.
[ ] Connect to Architecture Memory.
[ ] Connect to Governance rules.
```

---

## V4-14: Reference Architecture Products

```text
[ ] Promote V3 reference architectures into product-grade examples.
[ ] Each reference architecture must include runtime implementation.
[ ] Each must include V3 models.
[ ] Each must include observability report.
[ ] Each must include security checklist.
[ ] Each must include architecture doctor output.
```

---

## V4-15: Production Certification Suite

```text
[ ] Combine architecture doctor.
[ ] Combine runtime doctor.
[ ] Combine security doctor.
[ ] Combine db doctor.
[ ] Combine cache doctor.
[ ] Combine observability doctor.
[ ] Combine evidence graph.
[ ] Produce one production readiness verdict.
```

Final command:

```text
php avax production:certify
```

Output:

```text
GREEN = production-ready
YELLOW = usable for internal pilot
RED = not production-ready
UNKNOWN = not measured
```

---

# PART V: V4 Acceptance Criteria

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
[ ] AvaX can diagnose worker safety.
[ ] AvaX can diagnose database risk.
[ ] AvaX can diagnose cache risk.
[ ] AvaX can certify security baseline.
[ ] AvaX can export useful observability signals.
[ ] AvaX can validate plugin capability safety.
[ ] AvaX can explain compiled views.
[ ] AvaX can produce product-grade reference architecture reports.
[ ] AvaX can prove all of the above with tests and evidence.
```

---

# PART VI: V4 Anti-Goals

V4 must not:

```text
[ ] Auto-restore code without review.
[ ] Generate production code without tests.
[ ] Treat old code as automatically valid.
[ ] Treat new skeleton code as progress.
[ ] Unlock V2/V3/V4 while previous gates are red.
[ ] Hide validation failures.
[ ] Mark UNKNOWN as GREEN.
[ ] Replace real infrastructure.
[ ] Become a generic AI agent framework.
[ ] Become a dashboard-only observability toy.
[ ] Become custom Kafka/Redis/CDN/DB/FFmpeg.
[ ] Promote labs code without proof.
```

---

# Final V4 Sentence

```text
AvaX V4 is the product intelligence layer that helps the framework
understand itself, observe itself, diagnose itself, secure itself,
recover lost behavior safely, guide AI-assisted development, and prove
whether a system is truly production-ready.
```

Even shorter:

```text
V4 makes AvaX self-governing and production-intelligent.
```

```

Moj sud: ovo je sada konkretno. Ima governance mozak, ali i proizvodne mišiće. Ne pravi novu infrastrukturu, nego dodaje ono što velikim frameworkovima daje ozbiljnost: doctor suite, database intelligence, cache intelligence, security certification, observability, plugin safety, view compiler i reference products.
```
