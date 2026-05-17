# AvaX V4 Product Muscle Roadmap

Status: planning
Type: complementary roadmap document
Depends on: V1 Production Kernel GREEN, V2 Enterprise Platform at least YELLOW/GREEN, V3 SystemDesign validation layer
has real proof

---

## 0. Purpose

This document defines concrete product-grade capabilities that make AvaX stronger, more useful, and competitive with
top-tier frameworks.

This document complements the existing `EVIDENCE/plans/v4-intelligence-governance-observability-plan.md`.

This document does NOT replace any existing plan.

---

## 1. V4 Thesis Reminder

V4 is the layer where AvaX becomes self-aware enough to help maintain, govern, explain, and evolve itself.

```text
V1  = makes AvaX real.
V2  = makes AvaX enterprise-useful.
V3  = makes AvaX system-design-intelligent.
V4  = makes AvaX self-governing, observable, recoverable, and AI-assisted.
```

---

## 2. Execution Order (Recommended)

Product muscles are numbered in the order they should be implemented:

```text
01  Runtime Worker Safety Doctor
02  Architecture Doctor
03  Cache Intelligence
04  Database Intelligence
05  Security Certification Suite
06  Observability / OpenTelemetry Suite
07  Plugin Capability Security
08  Template Compiler / View Doctor
```

Rationale:

```text
Worker Safety first: long-lived workers are the highest-risk runtime.
Architecture Doctor second: validates that all other muscles follow the architecture.
Cache and Database Intelligence: these are the most-used diagnostics in production.
Security comes before Observability: security is a property of every boundary.
Plugin Security comes before Template: plugins are a runtime extension point.
Template last: view rendering is a delivery concern, not a platform concern.
```

---

## 3. Product Muscle Definitions

---

### 3.01 Runtime Worker Safety Doctor

#### Purpose

Ensure that long-lived worker runtimes (RoadRunner, Swoole, Workerman, FrankenPHP, PHP-FPM workers) do not leak state,
grow memory unboundedly, or fail silently.

#### Problem Solved

Workers that run forever accumulate memory, leak state between requests, miss signal handling, and fail in ways that are
invisible to operators. Without a worker safety doctor, production workers silently degrade until they crash.

#### Owner

```text
components/Runtime/WorkerSafetyDoctor/
System/
  Capabilities/
    DetectMemoryGrowth/
    DetectStateLeak/
    ValidateResetHooks/
    ValidateSignalHandling/
    ValidateWorkerLifecycle/
  Flows/
    RunWorkerHealthCheck/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax worker:doctor
php avax worker:doctor --runtime=roadrunner
php avax worker:doctor --runtime=swoole
php avax worker:inspect-memory
php avax worker:inspect-state-leaks
php avax worker:validate-reset-hooks
```

#### Required Tests

```text
memory growth detection tests
state leak detection tests
reset hook validation tests
signal handling tests
lifecycle stage tests
integration tests with each runtime
```

#### Validation Gates

```text
[ ] Worker Doctor CLI command exists and returns structured output
[ ] Memory growth detection produces actionable report
[ ] State leak detection identifies at least 3 common leak patterns
[ ] Reset hook validation checks all lifecycle stages
[ ] Signal handling tests cover SIGTERM, SIGINT, SIGUSR1
[ ] Integration tests pass for RoadRunner, Swoole, Workerman, FrankenPHP
[ ] PHPStan clean for WorkerSafetyDoctor component
[ ] Runtime Doctor passes for golden path worker startup
```

#### Promotion Criteria

```text
Must promote to production only after:
- All 4 runtimes tested (RoadRunner, Swoole, Workerman, FrankenPHP)
- Memory growth false-positive rate below 5%
- State leak detection catches real leaks in at least one reference architecture
- Reset hook validation is integrated with Component Readiness Kit
```

#### Non-Goals

```text
- This is NOT a performance profiler (use xdebug/pdo profiler for that)
- This is NOT a code coverage tool
- This does NOT fix leaks; it only detects and reports them
- This does NOT replace runtime-specific health checks from RoadRunner/Swoole vendors
```

#### Security Considerations

```text
Worker state dumps must not expose secrets.
Memory reports must not leak query parameters or payload content.
Signal handling must not create race conditions.
```

#### Observability Hooks

```text
worker.memory.growth bytes
worker.state.leak.detected
worker.reset.hook.failed
worker.signal.missed
worker.lifecycle.stage_transition
```

#### Relationship to V1/V2/V3

```text
V1: Worker safety is a production concern. V1 Kernel must have a worker-safe runtime baseline.
V2: Runtime Doctor uses V2 RuntimeKit, ConfigKit, HttpKernel.
V3: Worker safety is validated through V3 SystemDesignKit workload simulations.
```

---

### 3.02 Architecture Doctor

#### Purpose

Validate that the codebase follows AvaX architecture laws, detect drift, enforce component canonical shapes, and produce
evidence-backed GREEN/YELLOW/RED reports.

#### Problem Solved

Architecture drift happens silently. Components grow forbidden folders, namespaces drift, public surfaces leak
internals, and ownership becomes unclear. Without an architecture doctor, the codebase gradually becomes a warehouse of
technical categories.

#### Owner

```text
tooling/ArchitectureDoctor/
System/
  Capabilities/
    DetectArchitectureDrift/
    CheckComponentCanonicalShape/
    CheckPublicSurfaceBoundary/
    CheckNamespaceDrift/
    CheckForbiddenFolderViolation/
    CheckDuplicateOwnership/
    CheckRuntimeLeaks/
    CheckComponentCompleteness/
  Flows/
    RunArchitectureAudit/
    GenerateArchitectureReport/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax architecture:doctor
php avax architecture:doctor --component=DataStack/Database
php avax architecture:doctor --format=json
php avax architecture:doctor --severity=critical
php avax architecture:explain <component>
```

#### Required Tests

```text
forbidden folder detection tests
namespace drift tests
public surface boundary tests
duplicate ownership tests
runtime leak tests
canonical shape validation tests
GREEN/YELLOW/RED classification tests
evidence generation tests
```

#### Validation Gates

```text
[ ] Architecture Doctor CLI command exists
[ ] Detects forbidden folder violations (Services, Helpers, Utils, Common, Core, etc.)
[ ] Detects namespace drift against PSR-4 autoload
[ ] Detects public surface leaks (internals exposed through PublicSurface)
[ ] Detects duplicate ownership (same class in multiple components)
[ ] Produces structured GREEN/YELLOW/RED report
[ ] Report includes evidence (file paths, line numbers, violation types)
[ ] PHPStan clean for ArchitectureDoctor component
[ ] Integration with Component Readiness Kit works
```

#### Promotion Criteria

```text
Must promote to production only after:
- All existing tooling/refactor/check-*.php scripts are absorbed into the doctor
- Doctor is tested against at least one real architecture violation
- GREEN/YELLOW/RED classification matches manual review in at least 3 cases
- Evidence format is approved by governance rules
```

#### Non-Goals

```text
- This is NOT a code formatter (use PHP-CS-Fixer for that)
- This is NOT a linter (use PHPStan for that)
- This does NOT auto-fix violations
- This does NOT replace Component Readiness Kit ownership tracking
```

#### Security Considerations

```text
Architecture reports must not expose internal security vulnerabilities.
Report output must not create new attack surface.
```

#### Observability Hooks

```text
architecture.drift.detected
architecture.violation.count
architecture.component.score
architecture.green_path.passed
architecture.yellow_path.warning
architecture.red_path.critical
```

#### Relationship to V1/V2/V3

```text
V1: Architecture Doctor validates V1 Kernel canonical shape.
V2: Uses V2 naming conventions and API vocabulary frozen in V2.
V3: Architecture drift is a system design concern. V3 validates that architecture laws hold under large-system simulations.
```

---

### 3.03 Cache Intelligence

#### Purpose

Provide deep visibility into the cache layer: hit/miss metrics, stampede detection, cache warming, hierarchy
diagnostics, key explanation, and node health checks.

#### Problem Solved

Cache problems are invisible until they cause production incidents. N+1 queries hide behind caches. Cache stampedes
cause thundering herd. Cold cache on deploy causes latency spikes. Without cache intelligence, cache is a black box.

#### Owner

```text
components/Intelligence/CacheIntelligence/
System/
  Capabilities/
    MeasureCacheHitMiss/
    DetectCacheStampede/
    DiagnoseStaleWhileRevalidate/
    WarmCache/
    ExplainCacheKey/
    CheckCacheNodeHealth/
    TrackCacheHierarchy/
  Flows/
    RunCacheDiagnostics/
    WarmCacheDeployment/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax cache:doctor
php avax cache:explain <key>
php avax cache:warm --prefix=user.profile
php avax cache:inspect-hierarchy
php avax cache:node-health
php avax cache:metrics --backend=redis
```

#### Required Tests

```text
cache hit/miss metric tests
cache stampede detection tests
cache warming tests
cache key explanation tests
cache hierarchy diagnostics tests
stale-while-revalidate tests
node health check tests
integration tests with Redis, Memcached, FileCache backends
```

#### Validation Gates

```text
[ ] Cache Doctor CLI command exists
[ ] Cache hit/miss metrics are measurable per backend
[ ] Cache stampede detection identifies at least 3 common patterns
[ ] Cache warming produces actionable warm-up plan
[ ] Cache key explanation maps key to usage context
[ ] Cache hierarchy diagnostics shows layered cache relationships
[ ] Node health checks test connectivity, memory, eviction policy
[ ] PHPStan clean for CacheIntelligence component
[ ] Integration with existing CacheKit works
```

#### Promotion Criteria

```text
Must promote to production only after:
- Tested with at least 2 cache backends (Redis + Memcached)
- Cache stampede detection has < 5% false-positive rate
- Cache warming reduces cold-start latency by measurable amount
- Cache hierarchy diagnostics is tested against at least one real architecture
```

#### Non-Goals

```text
- This is NOT a cache backend implementation (use existing CacheKit for that)
- This does NOT replace Redis/Memcached native monitoring tools
- This does NOT auto-tune cache TTLs without evidence
- This does NOT implement distributed cache consensus (V5 concern)
```

#### Security Considerations

```text
Cache key explanations must not expose sensitive data.
Cache warming must not trigger by itself without explicit command.
Cache node health must not expose internal infrastructure topology.
```

#### Observability Hooks

```text
cache.hit rate
cache.miss rate
cache.stampede.detected
cache.warm.requested
cache.key.explained
cache.node.health.ok
cache.node.health.degraded
cache.hierarchy.level
```

#### Relationship to V1/V2/V3

```text
V1: Cache is part of the production kernel. V1 must have at least one working cache backend.
V2: Cache Intelligence uses V2 CacheKit abstractions.
V3: Cache stampede detection is a system design validation concern. V3 simulates cache stampede scenarios.
```

---

### 3.04 Database Intelligence

#### Purpose

Provide deep visibility into database operations: query timeline, slow query detection, N+1 detection, query
fingerprinting, transaction audit, schema drift detection, index recommendations, and read/write split diagnostics.

#### Problem Solved

Database is the most common bottleneck. Slow queries hide in business logic. N+1 queries cause exponential slowdowns.
Schema drift causes silent failures. Without database intelligence, developers discover performance problems in
production.

#### Owner

```text
components/Intelligence/DatabaseIntelligence/
System/
  Capabilities/
    BuildQueryTimeline/
    DetectSlowQuery/
    DetectNPlusOne/
    FingerprintQuery/
    AuditTransaction/
    DetectSchemaDrift/
    RecommendIndex/
    DiagnoseReadWriteSplit/
  Flows/
    RunDatabaseDiagnostics/
    GenerateQueryReport/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax database:doctor
php avax database:explain-query <sql>
php avax database:detect-n-plus-one --flow=UserRegistration
php avax database:schema-drift-report
php avax database:recommend-index --table=orders
php avax database:audit-transactions
php avax database:read-write-diagnostics
```

#### Required Tests

```text
query timeline build tests
slow query detection tests (with configurable threshold)
N+1 detection tests (at least 3 patterns)
query fingerprinting tests
transaction audit tests
schema drift detection tests
index recommendation tests (explain-based)
read/write split diagnostics tests
```

#### Validation Gates

```text
[ ] Database Doctor CLI command exists
[ ] Query timeline builds from at least one database driver
[ ] Slow query detection produces actionable slow query report
[ ] N+1 detection identifies at least 3 common patterns
[ ] Query fingerprinting normalizes query shapes
[ ] Transaction audit logs transaction boundaries
[ ] Schema drift detection compares current schema to declared schema
[ ] Index recommendations use EXPLAIN output
[ ] PHPStan clean for DatabaseIntelligence component
[ ] Integration with existing DataStack/Database works
```

#### Promotion Criteria

```text
Must promote to production only after:
- Tested with at least 2 database backends (MySQL + PostgreSQL)
- N+1 detection catches at least 3 real N+1 patterns in reference architectures
- Schema drift detection is tested against at least one real migration
- Query timeline accuracy is validated against query log
```

#### Non-Goals

```text
- This is NOT a query builder (use QueryKit for that)
- This is NOT a migration tool (use MigrationKit for that)
- This does NOT auto-create indexes
- This does NOT replace database-specific performance tools (pt-query-digest, pg_stat_statements)
```

#### Security Considerations

```text
Query explanations must not expose sensitive column names or data.
Schema drift reports must not expose internal table naming conventions.
Index recommendations must not create security-sensitive indexes without review.
```

#### Observability Hooks

```text
database.slow_query.detected
database.n_plus_one.detected
database.transaction.audit
database.schema.drift
database.index.recommended
database.query.timeline
database.read_write.imbalance
```

#### Relationship to V1/V2/V3

```text
V1: Database is part of DataStack. V1 must have working ORM and migrations.
V2: Database Intelligence uses V2 DataStack/Database abstractions.
V3: Query performance is a system design validation concern. V3 simulates query load scenarios.
```

---

### 3.05 Security Certification Suite

#### Purpose

Detect security vulnerabilities in the codebase: unsafe encryption, unserialize, secrets in logs, unsafe SQL, insecure
sessions/cookies, missing CSRF/signature checks. Produce actionable security reports and a security doctor command.

#### Problem Solved

Security vulnerabilities are discovered in production. Unsafe unserialize leads to RCE. Secrets are logged raw. Unsafe
SQL leads to injection. Missing CSRF checks lead to CSRF attacks. Without a security certification suite, security is
discovered by attackers, not developers.

#### Owner

```text
components/Security/SecurityCertification/
System/
  Capabilities/
    DetectUnsafeEncryption/
    DetectUnserialize/
    DetectSecretLeak/
    DetectUnsafeSql/
    DetectInsecureSession/
    DetectMissingCsrf/
    DetectMissingSignature/
    RunSecurityScan/
  Flows/
    RunSecurityCertification/
    GenerateSecurityReport/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax security:doctor
php avax security:doctor --severity=critical
php avax security:scan --component=DataStack/Database
php avax security:report --format=json
php avax security:explain <vulnerability-type>
```

#### Required Tests

```text
unsafe encryption detection tests
unserialize detection tests
secret leak detection tests (negative tests with real secrets in test fixtures)
unsafe SQL detection tests
insecure session/cookie tests
missing CSRF check tests
missing signature check tests
false-positive rate tests
```

#### Validation Gates

```text
[ ] Security Doctor CLI command exists
[ ] Unsafe encryption detection identifies known weak ciphers
[ ] Unserialize detection identifies all unserialize() calls
[ ] Secret leak detection produces actionable report
[ ] Unsafe SQL detection identifies raw SQL with user input
[ ] Insecure session/cookie checks validate configuration
[ ] Missing CSRF check detection validates business logic flows
[ ] Missing signature check detection validates signed request flows
[ ] PHPStan clean for SecurityCertification component
[ ] No false positives on golden path components
```

#### Promotion Criteria

```text
Must promote to production only after:
- Security Certification Suite is reviewed by at least one security expert
- All critical vulnerabilities are actionable (not just flags)
- False-positive rate is below 10% on existing codebase
- Security doctor integrates with CI/CD pipeline
```

#### Non-Goals

```text
- This is NOT a penetration testing tool
- This is NOT a dependency vulnerability scanner (use Composer Audit for that)
- This does NOT auto-fix vulnerabilities
- This does NOT replace professional security audits
- This does NOT cover network-level security
```

#### Security Considerations

```text
Security reports must not be publicly accessible.
Secret leak detection must not itself log secrets.
Security doctor must not create new attack surface.
Vulnerability explanations must not teach attackers how to exploit.
```

#### Observability Hooks

```text
security.vulnerability.critical
security.vulnerability.high
security.vulnerability.medium
security.vulnerability.low
security.scan.completed
security.certification.passed
security.certification.failed
```

#### Relationship to V1/V2/V3

```text
V1: Security is a property of every boundary. V1 Kernel must have security baseline documentation.
V2: Security Certification uses V2 HttpKernel, Session, AuthKit.
V3: Security is a system design concern. V3 validates security boundaries through threat modeling.
```

---

### 3.06 Observability / OpenTelemetry Suite

#### Purpose

Provide first-class observability for AvaX: traces, metrics, logs, request ID, correlation ID, trace ID, runtime
timeline, SLO/SLA monitoring, with Prometheus and OpenTelemetry backends.

#### Problem Solved

AvaX is invisible without observability. Logs without correlation are useless. Traces without context are noise. Metrics
without SLOs are data without meaning. Without observability, production debugging is guessing.

#### Owner

```text
components/Observability/OpenTelemetrySuite/
System/
  Capabilities/
    EmitTraces/
    EmitMetrics/
    EmitLogs/
    ManageRequestId/
    ManageCorrelationId/
    ManageTraceId/
    BuildRuntimeTimeline/
    MonitorSloSla/
    SupportPrometheusBackend/
    SupportOpenTelemetryBackend/
  Flows/
    InstrumentHttpRequest/
    InstrumentDatabaseQuery/
    InstrumentCacheOperation/
    InstrumentQueueMessage/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax observability:doctor
php avax observability:export-traces --backend=otlp
php avax observability:export-metrics --backend=prometheus
php avax observability:check-slo --service=api
php avax observability:timeline --request-id=abc123
php avax observability:configure
```

#### Required Tests

```text
trace emission tests
metric emission tests
log emission tests
request ID propagation tests
correlation ID propagation tests
trace ID propagation tests
SLO/SLA monitoring tests
Prometheus backend export tests
OpenTelemetry backend export tests
```

#### Validation Gates

```text
[ ] Observability Doctor CLI command exists
[ ] Traces are emitted for HTTP requests, database queries, cache operations, queue messages
[ ] Metrics are emitted with correct labels and cardinalities
[ ] Logs include request ID, correlation ID, trace ID
[ ] SLO/SLA monitoring produces actionable report
[ ] Prometheus backend exports correct format
[ ] OpenTelemetry backend exports correct OTLP format
[ ] PHPStan clean for OpenTelemetrySuite component
[ ] Integration with HttpKernel works
```

#### Promotion Criteria

```text
Must promote to production only after:
- Tested with at least one real observability backend
- Trace context propagation works across all V2 platform engines
- SLO/SLA monitoring is validated against real error budgets
- Performance overhead is measured and acceptable (< 5% latency impact)
```

#### Non-Goals

```text
- This is NOT a log storage system (use ELK, Loki, or similar)
- This is NOT a metrics storage system (use Prometheus, Datadog, or similar)
- This does NOT replace distributed tracing platforms (use Jaeger, Zipkin, or similar)
- This does NOT implement alerting (use AlertManager, PagerDuty, or similar)
```

#### Security Considerations

```text
Observability data must not include raw secrets or sensitive personal data.
Trace context must not leak internal security boundaries.
Metric cardinality must not create memory exhaustion risk.
```

#### Observability Hooks

```text
observability.trace.emitted
observability.metric.emitted
observability.log.emitted
observability.slo.violation
observability.sla.violation
observability.backend.connected
observability.backend.error
```

#### Relationship to V1/V2/V3

```text
V1: Observability is part of production readiness. V1 Kernel must have at least basic logging.
V2: OpenTelemetry Suite uses V2 HttpKernel, RuntimeKit.
V3: Observability is a system design concern. V3 validates SLO/SLA behavior under load.
```

---

### 3.07 Plugin Capability Security

#### Purpose

Provide a secure plugin extension model: plugin manifest validation, permission model, lifecycle management, dependency
graph, safety checks, and a plugin doctor command.

#### Problem Solved

Plugins are the most dangerous extension point. Plugins can read any file, execute arbitrary code, access secrets, and
break the framework. Without plugin security, AvaX plugins are a remote code execution risk.

#### Owner

```text
components/Security/PluginCapabilitySecurity/
System/
  Capabilities/
    ValidatePluginManifest/
    EnforcePluginPermissionModel/
    ManagePluginLifecycle/
    BuildPluginDependencyGraph/
    RunPluginSafetyChecks/
    RunPluginDoctor/
  Flows/
    InstallPlugin/
    UninstallPlugin/
    UpdatePlugin/
    AuditPluginPermissions/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax plugin:doctor
php avax plugin:install <plugin-name>
php avax plugin:uninstall <plugin-name>
php avax plugin:audit <plugin-name>
php avax plugin:permissions <plugin-name>
php avax plugin:dependency-graph
php avax plugin:safety-check <plugin-name>
```

#### Required Tests

```text
plugin manifest validation tests
permission model enforcement tests
plugin lifecycle tests (install, uninstall, update)
plugin dependency graph tests
plugin safety check tests (sandboxing, capability isolation)
plugin doctor command tests
```

#### Validation Gates

```text
[ ] Plugin Doctor CLI command exists
[ ] Plugin manifest schema is validated
[ ] Permission model enforces at least: filesystem, network, exec, env, secrets
[ ] Plugin lifecycle stages are validated
[ ] Dependency graph detects circular dependencies
[ ] Safety checks prevent sandbox escape
[ ] PHPStan clean for PluginCapabilitySecurity component
[ ] Integration with FrameworkLoader works
```

#### Promotion Criteria

```text
Must promote to production only after:
- Plugin permission model is reviewed by security expert
- Sandbox escape is prevented in at least 5 known attack patterns
- Plugin lifecycle is tested against at least one real plugin
- Dependency graph detects all known circular dependency patterns
```

#### Non-Goals

```text
- This is NOT a plugin marketplace or distribution system
- This is NOT a plugin packaging format (use existing manifest schema)
- This does NOT auto-upgrade plugins
- This does NOT replace PHP native sandboxing extensions
```

#### Security Considerations

```text
Plugin permission model must be enforced at the OS level where possible.
Plugin manifest must be signed and verified.
Plugin dependency graph must not expose internal plugin security architecture.
Plugin sandbox must prevent code execution outside granted permissions.
```

#### Observability Hooks

```text
plugin.installed
plugin.uninstalled
plugin.permission.violation
plugin.lifecycle.failed
plugin.dependency.cycle
plugin.safety.failed
plugin.doctor.completed
```

#### Relationship to V1/V2/V3

```text
V1: Plugin security is a framework boundary concern. V1 Kernel must not allow arbitrary code execution.
V2: Plugin Capability Security uses V2 FrameworkLoader, CapabilityKit.
V3: Plugin security is a system design concern. V3 validates plugin isolation under load.
```

---

### 3.08 Template Compiler / View Doctor

#### Purpose

Provide deep visibility into template compilation and rendering: lexer/parser/AST model, compiled views, contextual
escaping, sandboxing, view cache management, and a view explanation command.

#### Problem Solved

Template rendering is invisible until it causes XSS or performance problems. Compiled views are a black box. Escaping
context is misunderstood. Without a template compiler view doctor, template problems are discovered in production or by
security scanners.

#### Owner

```text
components/Intelligence/TemplateCompiler/
System/
  Capabilities/
    ModelLexer/
    ModelParser/
    ModelAst/
    TrackCompiledViews/
    ValidateContextualEscaping/
    EnforceTemplateSandbox/
    ManageViewCache/
    ExplainView/
  Flows/
    CompileTemplate/
    RenderTemplate/
    ValidateViewSecurity/
  Configuration/
  Foundation/
```

#### CLI Commands

```bash
php avax view:doctor
php avax view:explain <template-path>
php avax view:compile --force
php avax view:cache-clear
php avax view:audit-security <template-path>
php avax view:inspect-compiled <template-path>
```

#### Required Tests

```text
lexer model tests
parser model tests
AST model tests
compiled view tracking tests
contextual escaping validation tests
template sandbox enforcement tests
view cache management tests
view explanation tests
```

#### Validation Gates

```text
[ ] View Doctor CLI command exists
[ ] Lexer model produces correct token stream
[ ] Parser model produces correct AST
[ ] Compiled views are tracked and inspectable
[ ] Contextual escaping detects XSS-risk patterns
[ ] Template sandbox prevents dangerous functions
[ ] View cache management works
[ ] View explanation maps compiled output to source template
[ ] PHPStan clean for TemplateCompiler component
[ ] Integration with existing ViewKit works
```

#### Promotion Criteria

```text
Must promote to production only after:
- Contextual escaping is validated against OWASP XSS test patterns
- Template sandbox is tested against at least 5 dangerous template patterns
- Compiled view inspection is tested against at least 3 real templates
- View explanation produces accurate source-to-compiled mapping
```

#### Non-Goals

```text
- This is NOT a new template engine
- This is NOT a view storage system
- This does NOT replace existing ViewKit
- This does NOT auto-fix XSS vulnerabilities
- This does NOT implement server-side rendering for JavaScript frameworks
```

#### Security Considerations

```text
Template sandbox must prevent RCE through dangerous functions.
View explanation must not expose internal template architecture.
Compiled view inspection must not expose business logic in templates.
Contextual escaping validation must not produce false negatives on real XSS.
```

#### Observability Hooks

```text
template.compiled
template.xss.risk.detected
template.sandbox.violation
template.cache.hit
template.cache.miss
template.unsafe.function.called
view.doctor.completed
```

#### Relationship to V1/V2/V3

```text
V1: Template rendering is part of the view delivery layer. V1 Kernel must have a working view system.
V2: TemplateCompiler uses V2 ViewKit.
V3: Template security and performance are system design concerns. V3 validates template behavior under load.
```

---

## 4. Non-Goals (Shared Across All Muscles)

```text
- No muscle implements infrastructure (Redis, MySQL, Kafka, S3, etc.)
- No muscle auto-scales or auto-heals production systems
- No muscle replaces professional APM tools (Datadog, New Relic, etc.)
- No muscle provides a hosted SaaS or cloud offering
- No muscle is a code generator that produces production code without review
- No muscle implements V5 distributed consensus concerns
```

---

## 5. Risks

| Risk                                                            | Severity | Mitigation                                                                      |
|-----------------------------------------------------------------|----------|---------------------------------------------------------------------------------|
| Scope creep across 8 muscles                                    | HIGH     | Strict promotion gates. No implementation until plan is locked.                 |
| V4 muscles overlap with V3 SystemDesignKit                      | HIGH     | Defined explicit non-overlap boundaries in each muscle.                         |
| Worker safety tests require real runtimes                       | MEDIUM   | Use containerized test environments for RoadRunner/Swoole/Workerman/FrankenPHP. |
| Database intelligence requires real database                    | MEDIUM   | Use Docker containers for MySQL and PostgreSQL test instances.                  |
| Security certification must not produce false sense of security | HIGH     | Clearly label that this is a static analysis tool, not a penetration test.      |
| OpenTelemetry overhead impacts latency                          | MEDIUM   | Measure overhead and make tracing sampling configurable.                        |
| Plugin sandboxing requires OS-level primitives                  | HIGH     | Use PHP native sandboxing extensions. Falls back to warn-only if unavailable.   |
| Template AST model duplicates existing lexer work               | MEDIUM   | Reuse existing framework lexer primitives if available.                         |

---

## 6. Summary

```text
Total product muscles:      8
Execution order:            01 Worker Safety → 02 Architecture Doctor → 03 Cache → 04 Database → 05 Security → 06 Observability → 07 Plugin Security → 08 Template
Total CLI commands:          ~50 new commands
Total new capabilities:     ~80 capability classes
Estimated new tests:        ~400 test cases
Estimated PHPStan rules:    ~20 new rules
Stage gate:                 V4 locked until V1/V2/V3 GREEN
Implementation mode:        Plan only, no production code yet
```

---

## 7. How This Complements the Existing V4 Plan

The existing `v4-intelligence-governance-observability-plan.md` focuses on:

```text
- V4 thesis and philosophy
- Intelligence, governance, and observability principles
- AI-assisted maintenance
- Memory and context lifecycle
- Agent-friendly codebase evolution
- Learning and instinct policy
```

This `avax-v4-product-muscle-roadmap.md` focuses on:

```text
- Concrete product-grade capabilities
- CLI commands for each muscle
- Specific diagnostic capabilities
- Required tests and validation gates
- Promotion criteria for each muscle
- Security, observability, and performance considerations
- Execution order rationale
```

Together they form the complete V4 roadmap:

```text
v4-intelligence-governance-observability-plan.md  →  WHY and WHAT (philosophy, principles, AI-assistance)
avax-v4-product-muscle-roadmap.md                   →  HOW and WHEN (concrete muscles, CLI, gates, order)
```

---

## 8. Governance Compliance

This document follows:

```text
[OK] AGENTS.md section 17 Stage Lock
[OK] AGENTS.md section 18 V1 Kernel Green Definition
[OK] AGENTS.md section 19 Required Validation
[OK] AGENTS.md section 21 Agent Output Contract
[OK] .agents/how-to/how-to-governance rules
[OK] .agents/how-to/how-to-document.md documentation rules
[OK] Screaming Architecture naming conventions
[OK] Forbidden folder law (no Services, Helpers, Utils, Common, Core, etc.)
[OK] Evidence-driven governance
[OK] No marketing language or optimism
```
