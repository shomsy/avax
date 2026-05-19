# V5 + V5.5 Master Plan

## V5 Name

```txt
V5 — Internal Convergence, Modern PHP Clean Code & High-Performance Engine
```

Shorter:

```txt
V5 — AvaX Eats AvaX
```

## V5.5 Name

```txt
V5.5 — Benchmark Proof & World-Class Hardening
```

Shorter:

```txt
V5.5 — AvaX Proves AvaX
```

## Philosophy

V4 built the muscles. V5 makes AvaX use its own muscles. V5.5 measures how strong those muscles really are.

V5 is **not** a feature-expansion release. V5 is a **framework self-hardening release**.

```txt
AvaX must use AvaX.
AvaX must measure AvaX.
AvaX must clean AvaX.
AvaX must verify AvaX.
```

V5.5 is **not** marketing. V5.5 is **proof**.

---

# Mandatory Governance Set

Every V5 and V5.5 stage must obey all local AvaX how-to governance documents.

The mandatory governance set includes:

- `.agents/how-to/how-to-use-advanced-architecture-patterns.md`
- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-architecture-extension-with-ddd.md`
- `.agents/how-to/how-to-clean-code.md`
- `.agents/how-to/how-to-code-review.md`
- `.agents/how-to/how-to-code-style.md`
- `.agents/how-to/how-to-coding-standards.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-document.md`
- `.agents/how-to/how-to-dogfooding.md`
- `.agents/how-to/how-to-modern-php-attributes-di.md`
- `.agents/how-to/how-to-production-readiness.md`
- `.agents/how-to/how-to-system-performance.md`
- `.agents/how-to/how-to-system-security.md`
- `.agents/how-to/how-to-unit-test.md`

## Governance Rules

1. No V5 or V5.5 stage may be marked GREEN unless applicable rules from every how-to document have been checked.

2. Every stage report must include a governance compliance matrix:

```text
| Governance Document | Applies? | Rules Applied | Violations Found | Fixes Made | Remaining Risk | Status |
```

3. If a how-to document does not apply to a specific stage, the report must say why.

4. If a how-to document cannot be read, the stage is BLOCKED.

5. No agent may cherry-pick only convenient rules.

6. The local `AGENTS.md` root contract wins for precedence, stage lock, filesystem shape, naming, PublicSurface rules,
   Flow/Capability rules, and AvaX-specific architecture.

## Mandatory Stage-to-Document Mapping

### how-to-modern-php-attributes-di.md is mandatory for:

- V5-07 Modern PHP 8.x Language Adoption
- V5-08 Attribute / Annotation Runtime
- V5-09 Dependency Injection & Autowiring Clean Code
- V5-13 AvaX Request Object & Superglobal Isolation
- V5-20 Hot Path Cache & Compiled Metadata
- V5-21 Tooling Gates & Custom Rector Rules

### how-to-dogfooding.md is mandatory for:

- V5-03 Capability Ownership Scan
- V5-04 Dogfooding Adoption Matrix
- V5-05 Filesystem / Storage / Cache Adoption
- V5-10 Data Structures Adoption
- V5-18 Async / Concurrency / Parallelism Adoption
- V5-19 Pooling & Resource Lifecycle Optimization
- V5-20 Hot Path Cache & Compiled Metadata

### how-to-system-security.md is mandatory for:

- V5-02 Critical Security Blocker Cleanup
- V5-13 AvaX Request Object & Superglobal Isolation
- V5-17 Serialization & Payload Safety
- V5-22 E2E Tests / Reference Runtime Proof
- All work touching HTTP, sessions, tokens, secrets, queues, messages, filesystem, database, config, logs, telemetry, or
  runtime state

### how-to-system-performance.md is mandatory for:

- V5-06 Metadata Compilation
- V5-18 Async / Concurrency / Parallelism Adoption
- V5-19 Pooling & Resource Lifecycle Optimization
- V5-20 Hot Path Cache & Compiled Metadata
- All V5.5 benchmark/proof stages

### how-to-design-components.md is mandatory for:

- Every new or changed component

### how-to-unit-test.md is mandatory for:

- Every test added or changed

### how-to-document.md is mandatory for:

- Every documentation file added or changed

### how-to-code-review.md is mandatory for:

- Every final review and stage closure

### how-to-architecture.md, how-to-architecture-extension-with-ddd.md, how-to-use-advanced-architecture-patterns.md are mandatory for:

- Structure, naming, DDD concepts, CQRS/Event/Outbox/Inbox/Saga/Projection usage, and advanced architecture decisions

## Stage Report Contract

Every V5 and V5.5 stage final report must include:

```text
Stage:
Status:
Governance documents read:
Governance compliance matrix:
how-to rules applied:
how-to rules intentionally not applicable:
Violations found:
Fixes made:
Validation commands:
Evidence written:
Remaining risks:
Next allowed action:
```

## Final Governance Law

V5 is not complete because the code works.

V5 is complete only when the code, tests, documentation, evidence, tooling gates, and every mandatory how-to governance
document agree.

If validation is green but governance compliance is missing, status is YELLOW.

If a how-to document is ignored, status is RED/BLOCKED.

---

# V5 Hard Rules

```txt
- Follow every how-to-*.md rule.
- Follow TODO.md, EXECUTION.md, CURRENT_TRUTH.md.
- Follow every V1/V2/V3/V4 plan that is still active.
- If governance contradicts implementation, implementation loses.
- If report says GREEN but code disagrees, code wins.
- If component exists but AvaX bypasses it, status is YELLOW.
- If performance claim lacks measurement, claim is invalid.
- If class uses raw string for domain/system state, evaluate enum/value object.
- If hot path uses reflection repeatedly, compile/cache metadata.
- If public surface contains runtime behavior, fix it.
- If tooling can catch it, tooling must catch it.
- Do not claim PHP core has features it does not have. AvaX will have them.
- Do not use reflection in hot paths after metadata compilation.
- Do not use attributes without compiled metadata.
- Do not fake performance claims.
- Do not call anything GREEN without tests, PHPStan, governance and evidence.
```

---

# V5 Stage Map

```txt
V5-00  Final V4 Truth Lock
V5-01  Whole-Repo Governance Resolution
V5-02  Critical Security Blocker Cleanup
V5-03  Capability Ownership Scan
V5-04  Dogfooding Adoption Matrix
V5-05  Filesystem / Storage / Cache Adoption
V5-06  DataTransfer / SecureRequest / Schema Metadata Compilation
V5-07  Modern PHP 8.x Language Adoption
V5-08  Attribute / Annotation Runtime
V5-09  Dependency Injection & Autowiring Clean Code
V5-10  Data Structures Adoption
V5-11  Arrhae / Collection / JSON Productization
V5-12  Enum & Domain Value Cleanup
V5-13  AvaX Request Object & Superglobal Isolation
V5-14  Router Completion
V5-15  Naming & Structure Convergence
V5-16  Traits / Multi-Class Files / Empty Classes Cleanup
V5-17  Serialization & Payload Safety
V5-18  Async / Concurrency / Parallelism Adoption
V5-19  Pooling & Resource Lifecycle Optimization
V5-20  Hot Path Cache & Compiled Metadata
V5-21  Tooling Gates & Custom Rector Rules
V5-22  E2E Tests / Reference Runtime Proof
V5-23  Final V5 Truth Report
```

---

# V5.5 Stage Map

```txt
V5.5-00  Benchmark Methodology Lock
V5.5-01  Hardware / Environment Baseline
V5.5-02  Microbenchmarks
V5.5-03  Runtime Benchmarks
V5.5-04  HTTP Throughput Benchmarks
V5.5-05  Reference App Benchmarks
V5.5-06  Long-Running Worker Soak Tests
V5.5-07  Memory Leak & State Leak Tests
V5.5-08  Database / Queue / Messaging Throughput
V5.5-09  Observability & Security Overhead
V5.5-10  Framework Comparison Suite
V5.5-11  Optimization Pass
V5.5-12  Final World-Class Readiness Report
```

---

# V5.6 Stage Map

```txt
V5.6-00  Review Governance Lock
V5.6-01  Whole-System As-Built Execution Flow Review
V5.6-02  Whole-System Governance Compliance Review
V5.6-03  Runtime / Framework Core Review
V5.6-04  Component-by-Component Review
V5.6-05  PublicSurface Review
V5.6-06  Dogfooding Review
V5.6-07  Modern PHP / Attributes / DI Review
V5.6-08  Security Review
V5.6-09  Performance Review
V5.6-10  Testing Review
V5.6-11  Documentation Review
V5.6-12  Production Readiness Review
V5.6-13  Rewrite / Redesign / Keep Decision Report
V5.6-14  Final Review Closure Report
```

---

# Stage Definitions

## V5-00 Final V4 Truth Lock

Before V5 starts, V4 must be locked.

### Goal

No more truth drift.

### Must Verify

```txt
CURRENT_TRUTH.md — V4-00 through V4-17: GREEN
TODO.md — V4 items completed, V5-PHASE active
EXECUTION.md — V4 complete, V5 next
EVIDENCE/EXECUTION.md — V4 complete, V5 next
V4 final evidence reports — all present
V4 benchmark proof — EVIDENCE/v4-16-benchmark-proof.md
V4 adapter status — EVIDENCE/v4-17-adapter-status.md
Reference apps — all 13 exist with READMEs
Health endpoints — wired and tested
```

### Must Say Clearly

```txt
V4-00 through V4-16: GREEN
V4-17: GREEN for adapter readiness layer
ReactPHP adapter: GREEN
RoadRunner/Swoole/FrankenPHP/Workerman: ROADMAP
V5: NEXT PLANNED MAJOR PHASE
```

### Deliverables

```txt
EVIDENCE/v5/v4-final-truth-lock.md
CURRENT_TRUTH.md update
TODO.md update
EXECUTION.md update
EVIDENCE/EXECUTION.md update
```

---

## V5-01 Whole-Repo Governance Resolution

### Goal

Before any refactoring, agents must know which documents are source of truth.

### Must Scan

```txt
.agents/how-to/*.md (12 documents)
AGENTS.md (root contract)
CURRENT_TRUTH.md
TODO.md
EXECUTION.md
EVIDENCE/.PLANS/V4_PRODUCT_RUNTIME_AND_ENTERPRISE_MUSCLE.md
EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md
Old review/archive files for drift
```

### Must Resolve

```txt
Code-Review-And-ToDo — rename to EVIDENCE/reports/ or EVIDENCE/reviews/
Stale references in TODO.md (V3 still marked LOCKED despite being CLOSED/GREEN)
Contradictions between CURRENT_TRUTH.md and EXECUTION.md
Governance document precedence order confirmed
```

### Deliverables

```txt
EVIDENCE/v5/governance-resolution-map.md
EVIDENCE/v5/source-of-truth-order.md
```

---

## V5-02 Critical Security Blocker Cleanup

This cannot wait. If these vulnerabilities exist, they block production-ready claim.

### Findings from Current Codebase Analysis

**P0 — Session ID logged in regenerate():**
`components/HTTP/Session/System/PublicSurface/Session.php:173` logs session ID at INFO level. Violates "no secret
exposure" rule.

**P1 — SerializableClosure setSecretKey(null):**
`components/Foundation/CallableSerialization/System/Capabilities/SerializeCallable/SerializeClosureThroughLibrary.php` —
closure content not encrypted, only integrity-signed. No confidentiality.

**P1 — Service locator facade with global static state:**
`components/Application/Container/System/Container.php` + `shortcuts.php` — Laravel-style static facade. Risk in
long-lived runtimes.

### Required Actions

#### 1. Session ID Logging Fix

Replace session ID log with opaque token or remove log entirely:

```txt
Before: Log "Session regenerated: {sessionId}"
After:  Log "Session regenerated successfully" (no ID)
```

#### 2. Callable Encryption

If data-at-rest encryption is needed for V5:

```txt
- Implement AES-256-GCM encrypter following how-to-system-security.md section 19
- Or: CBC + HMAC encrypt-then-MAC if compatibility requires
- Never: encrypt-only CBC, silent decrypt failure, padding oracle risk
```

#### 3. Service Locator Discipline

Not removing the container facade (intentional Laravel-style architecture), but adding diagnostics:

```txt
- Tooling to detect Container::get() in business logic (allowed in bootstrap)
- Constructor bloat detection (8+ dependencies = architecture warning)
- Method injection for request-scoded dependencies
```

#### 4. Security Gate Tool

```bash
php tooling/refactor/check-security-blockers.php
```

Scans for:

```txt
openssl_decrypt CBC without auth
unserialize( on untrusted data
@unserialize suppression
raw SQL interpolation
$_SESSION direct unsafe decode
eval, shell_exec, proc_open outside approved boundaries
session ID exposure in logs
unsigned worker payloads
```

### Deliverables

```txt
EVIDENCE/v5/security-blocker-cleanup.md
tooling/refactor/check-security-blockers.php
Fixed session ID logging
```

---

## V5-03 Capability Ownership Scan

### Goal

Map: which component owns what. Find duplicates.

### Questions

```txt
Who owns filesystem? → components/Application/Filesystem/
Who owns storage? → components/Integration/ObjectStorage/
Who owns retry? → components/Operations/Resilience/
Who owns serialization? → components/Foundation/CallableSerialization/
Who owns validation? → components/DataStack/DataTransfer/ (attributes)
Who owns hydration? → components/HTTP/SecureRequest/ + DataTransfer
Who owns routing? → components/HTTP/Router/
Who owns observability? → components/Operations/Observability/
Who owns policy? → framework/System/Capabilities/Security/PolicyEngine/
Who owns database connection lifecycle? → components/DataStack/Database/
```

### Find Duplicates

```txt
file_get_contents/file_put_contents outside Filesystem → bypass
serialize/unserialize outside serializer owner → bypass
retry loop outside Reliability → bypass
manual route matching outside Router → bypass
manual reflection outside DataTransfer metadata → bypass
manual request parsing outside Request component → bypass
manual logging outside Observability → bypass
manual DB connections outside ConnectionPool → bypass
```

### Deliverables

```txt
EVIDENCE/v5/capability-ownership-map.md
EVIDENCE/v5/duplicate-capability-owners.md
```

---

## V5-04 Dogfooding Adoption Matrix

### Goal

AvaX uses AvaX.

### Matrix Format

```txt
Consumer | Should Use | Current Usage | Bypass Found | Action | Status
```

### Required Relationships

```txt
Storage → Filesystem
Cache → Filesystem/Storage
RouteCache → Filesystem/Storage
ConfigCache → Filesystem
Queue → Reliability
Queue → CallableSerialization
Queue → Observability
Messaging → Database
Messaging → Queue
Messaging → Reliability
Messaging → Observability
SchemaGeneration → DataTransfer
SchemaGeneration → SecureRequest
SchemaGeneration → Router
Runtime → Router
Runtime → Container
Runtime → ErrorHandling
Runtime → WarmSafety
Runtime → MemoryGuard
Runtime → Observability
Security → Redaction
Observability writers → Redaction + Filesystem
Benchmarks → Observability/Statistics/DataStack where useful
Doctor → all components
```

### Deliverables

```txt
EVIDENCE/v5/dogfooding-adoption-matrix.md
tooling/refactor/check-component-adoption.php
```

---

## V5-05 Filesystem / Storage / Cache Adoption

### Goal

Remove raw file I/O from runtime code.

### Forbidden Outside Filesystem/Tooling/Tests

```php
file_get_contents()
file_put_contents()
unlink()
mkdir()
rmdir()
rename()
copy()
chmod()
is_file()
file_exists()
is_writable()
```

### Required Rules

```txt
Storage uses Filesystem.
Route cache uses Filesystem/Storage.
Config cache uses Filesystem.
Observability file writers use Filesystem.
Benchmark reports use Filesystem or evidence writer.
Filesystem must not use Storage.
```

### Tooling Gate

```bash
php tooling/refactor/check-raw-file-operations.php
```

### Deliverables

```txt
EVIDENCE/v5/filesystem-storage-cache-adoption.md
```

---

## V5-06 DataTransfer / SecureRequest / Schema Metadata Compilation

### Goal

Reflection is not a hot path.

### Problem

If every validation or hydration runs reflection, performance will suffer.

### Rule

```txt
Reflection allowed:
- build phase
- compile phase
- first discovery
- tests
- tooling

Reflection forbidden:
- every request
- every SecureRequest hydration
- every controller invocation
- every queue job
- repeated schema generation
```

### Introduce

```txt
CompiledDataObjectShape
CompiledSecureRequestShape
CompiledValidationAttributeMap
CompiledCasterMap
CompiledSchemaMetadata
CompiledOpenApiMetadata
```

### Cache

```txt
DataTransferMetadataCache
SecureRequestMetadataCache
SchemaMetadataCache
```

### Tests

```txt
first discovery uses reflection
second hydration uses compiled metadata
metadata cache invalidates on changed source
schema generation uses compiled metadata
```

### Deliverables

```txt
EVIDENCE/v5/reflection-to-compiled-metadata.md
tooling/refactor/check-raw-reflection-hot-path.php
```

---

## V5-07 Modern PHP 8.x Language Adoption

### Goal

AvaX must look like a futuristic, modern PHP framework. Not PHP 8.5-only — all useful PHP 8.0 through 8.5 features.

### PHP 8.0 Foundation

```txt
attributes — routing, validation, DI, policy, audit, tracing
named arguments — explicit API calls
union types — precise type signatures
constructor property promotion — default for all classes
match — expressive branching
nullsafe operator — safe chaining
WeakMap — object metadata cache
JIT awareness — benchmark profile, not assumed speedup
```

### PHP 8.1 Framework Expressiveness

```txt
enums — domain/system state (HealthStatus, PolicyEffect, etc.)
readonly properties — immutable value objects
first-class callables — explicit function references
fibers — cooperative concurrency
intersection types — precise interface contracts
never return type — where applicable
```

### PHP 8.2 Immutable / Type Precision

```txt
readonly classes — immutable DTOs and value objects
DNF types — precise nullable/union combinations
standalone true/false/null — precise boolean/null typing
SensitiveParameter — secret protection in stack traces
Random extension — secure random generation
```

### PHP 8.3 Contract Strictness

```txt
typed class constants — framework contracts
#[Override] — strict inheritance discipline
dynamic class constant fetch — flexible constant resolution
readonly clone improvements — immutable cloning
```

### PHP 8.4 Object Model Upgrade

```txt
property hooks — value normalization, invariant enforcement
asymmetric visibility — public-read/private-write state
request_parse_body() — multipart parsing in AvaX Request
chaining new expressions — fluent construction
PDO driver-specific subclasses — type-safe database access where used
```

### PHP 8.5 Futuristic Syntax

```txt
pipe operator — pure data transformations (schema, config, normalization)
URI extension — URI parsing/manipulation
clone-with syntax — immutable object modification
#[NoDiscard] — important return values that must not be ignored
closures/first-class callables in constant expressions — static closures everywhere
static asymmetric visibility — class-level public-read/private-write
final constructor promotion — promoted properties that cannot be overridden
fatal error backtraces — better diagnostics
get_exception_handler()/get_error_handler() — handler inspection where useful
```

### Style Rules

```txt
constructor property promotion is default
readonly where possible
final by default unless extension point
typed constants
typed properties
named arguments where API allows
static closures where possible
pipe operator only for clear pure transformations
property hooks only where they reduce boilerplate without hiding lifecycle logic
asymmetric visibility for public-read/private-write state
NoDiscard for important return values
```

### Do Not Apply Mechanically

Pipe operator is not for everything. Use it for:

```txt
data transformations
normalization pipelines
schema transformations
string/path transformations
config processing
```

Do not use it where it kills debug clarity.

### Deliverables

```txt
EVIDENCE/v5/modern-php-8x-adoption-map.md
tooling/refactor/check-modern-php-style.php
```

---

## V5-08 Attribute / Annotation Runtime

### Goal

NestJS/Java/.NET inspired PHP. Attributes as declarative metadata.

### Attribute Areas

```php
#[Controller]
#[Route('/path')]
#[Get] / #[Post] / #[Put] / #[Patch] / #[Delete]
#[Middleware(MiddlewareClass::class)]
#[Inject(ServiceClass::class)]
#[Autowire]
#[Config('config.key')]
#[Validate]
#[Required]
#[StringType]
#[Min(N)]
#[Max(N)]
#[RegexPattern('/pattern/')]
#[Policy('policy.name')]
#[Can('ability')]
#[FeatureFlag('flag.name')]
#[RateLimit(N)]
#[Trace]
#[Audit('event.name')]
#[NoDiscard]
#[Cache(ttl: 3600)]
#[Transactional]
#[Queue('queue.name')]
#[MessageConsumer('channel')]
```

### Rule

Attributes are declaration. They must not be runtime chaos.

Flow:

```txt
Attributes → discovery → compiled metadata → runtime uses compiled metadata
```

### Never

```txt
scan attributes on every request
reflection on every controller call
magic autowiring without diagnostics
silent failure if attribute invalid
```

### Attribute Compilation

```txt
CompileControllerAttributes
CompileRouteAttributes
CompileValidationAttributes
CompilePolicyAttributes
CompileInjectionAttributes
CompileMessageConsumerAttributes
CompileAuditAttributes
```

### Deliverables

```txt
framework/System/Attributes/ or components/Foundation/Attributes/
EVIDENCE/v5/attribute-runtime-plan.md
EVIDENCE/v5/compiled-attribute-metadata.md
```

---

## V5-09 Dependency Injection & Autowiring Clean Code

### Goal

Maximally clean DI without constructor bloat.

### Principle

Constructor injection is default for stable dependencies.

Method injection/autowiring is allowed for:

```txt
request-scoped dependency
controller action parameters
command handler context
message consumer context
policy context
current user/session/request
transaction scope
```

### Do Not Allow

```txt
god constructors with 12 dependencies
service locator masked as DI
Container::get() everywhere
nullable dependencies that are not truly optional
hidden static dependencies
```

### Attribute-Driven DI

```php
final class RegisterUserController
{
    public function __invoke(
        #[Body] RegisterRequest $request,
        UserRepository $users,
        #[CurrentUser] User|null $user,
        #[Config('security.registration_enabled')] bool $enabled,
    ): Response {
        // ...
    }
}
```

### Autowiring Rules

```txt
constructor autowiring
method parameter autowiring
attribute-based source resolution
config value injection
request DTO injection
current user injection
policy context injection
```

### Constructor Bloat Rules

```txt
0–4 dependencies: normal
5–7: check responsibility
8+: architecture warning
```

### Tooling Gate

```bash
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-container-service-locator.php
```

### Deliverables

```txt
EVIDENCE/v5/di-autowiring-clean-code.md
```

---

## V5-10 Data Structures Adoption

### Goal

DataStack structures must not be decoration.

### Where to Use

```txt
Map → registries, service maps, route maps, command maps
Set → unique capability names, middleware names, tags
List → ordered middleware pipeline, validation violations
Queue → job queues, outbox relay, deferred work
PriorityQueue → scheduled jobs, retry priority, timeout ordering
Graph → dependency graph, capability ownership graph, route dependency graph
Trie/Tree → route matching, config hierarchy, path matching
WeakMap → object metadata cache, reflection metadata cache, request-local object cache
```

### WeakMap Cache

Use `WeakMap` for:

```txt
object metadata cache
reflection-to-object metadata
temporary runtime associations
per-object computed metadata
```

Do not use for:

```txt
persistent cache
cross-request cache
compiled metadata cache
```

### Deliverables

```txt
EVIDENCE/v5/data-structures-adoption-map.md
EVIDENCE/v5/weakmap-cache-policy.md
```

---

## V5-11 Arrhae / Collection / JSON Productization

### Goal

Clarify what is what.

### Proposal

```txt
Arrhae = fluent operations over PHP arrays and array-shapes
Collection = fluent operations over object/value collections
Json = fluent operations over JSON documents, JSON pointers, JSON paths, schemas
```

### Arrhae Solves

```txt
arrays
nested arrays
array shape transformations
safe get/set
flatten/unflatten
group/map/filter/reduce
path access
```

### Collection Solves

```txt
objects
DTO lists
DataObject lists
entity/value object collections
typed iteration
grouping/filtering/mapping with typed callbacks
```

### Json Solves

```txt
JsonDocument
JsonPointer
JsonPath
JsonSchema
JsonEncode/Decode
JsonPatch (maybe later)
```

### Deliverables

```txt
EVIDENCE/v5/arrhae-collection-json-product-boundary.md
```

---

## V5-12 Enum & Domain Value Cleanup

### Goal

No naked strings where they represent domain/system state.

### Candidates

```txt
DoctorStatus
HealthStatus
RuntimeName
QueueDriver
JobStatus
MessageStatus
MessageDirection
PolicyEffect
FeatureFlagState
StorageVisibility
ResponseFormat
ContentType
HttpMethod
RouteVerb
CacheState
BenchmarkStatus
ComponentStatus
FailureSeverity
RetryDecision
TimeoutMode
BackpressureDecision
```

### Deliverables

```txt
EVIDENCE/v5/enum-domain-value-cleanup.md
tooling/refactor/check-naked-domain-strings.php
```

---

## V5-13 AvaX Request Object & Superglobal Isolation

### Goal

Isolate all superglobals behind a single AvaX-owned Request object.

### Current State

`RuntimeRequest` exists at `framework/System/Capabilities/Runtime/RuntimeRequest.php` — a simple readonly class with
method, uri, headers, body, attributes. It does not provide typed access to query params, POST data, files, session, or
cookies.

PHP 8.5 does **not** have a core Request class. AvaX will implement its own.

### Required

```txt
AvaX Request class must provide:
- method, uri, headers (existing)
- query params ($_GET isolation)
- post data ($_POST isolation, using PHP 8.4 request_parse_body() where helpful)
- uploaded files ($_FILES isolation)
- cookies
- session access
- body (php://input)
- server params ($_SERVER isolation)
- typed accessors with proper types
- immutability where possible
- PSR-7 ServerRequestInterface compatibility
```

### Superglobal Owner

```txt
$_GET, $_POST, $_SERVER, $_FILES, php://input
```

Must be owned by exactly one component:

```txt
Avax\Framework\System\Capabilities\Http\ReadCurrentRequest
or
Avax\Framework\System\Capabilities\Http\AvaxRequestFactory
```

No other code may access superglobals directly.

### Tooling

```bash
php tooling/refactor/check-superglobal-usage.php
```

### Deliverables

```txt
framework/System/Capabilities/Http/Request/ (or similar canonical location)
EVIDENCE/v5/request-object-superglobal-isolation-plan.md
```

---

## V5-14 Router Completion

### Goal

Router must support serious HTTP.

### Required

```txt
GET, POST, PUT, PATCH, DELETE, OPTIONS, HEAD, ANY
method override if desired
route groups
route prefixes
middleware pipeline
named routes
route params with constraints
fallback routes
405 Method Not Allowed
404 Not Found
compiled route cache
health endpoint integration
attribute-based routes (if V5 attributes are in scope)
```

### Tests

```txt
every HTTP method
HEAD behavior
OPTIONS behavior
method not allowed
route constraints
group prefix
middleware order
named route generation
compiled route matching
```

### Deliverables

```txt
Enhanced components/HTTP/Router/ or framework router
EVIDENCE/v5/router-completion.md
```

---

## V5-15 Naming & Structure Convergence

### Goal

Clean names. No ambiguous or misleading names.

### Candidates to Rename or Justify

```txt
RouteRegistrarProxy → RegisterRoutesThrough... or RecordRouteDeclaration
Utils → concrete capability names
EntityManager → UnitOfWork / EntityMap / PersistEntities / TrackEntities (depends on what it does)
ExternalState → MountedRuntimeState / ImportedState / OutboundState / SharedBoundaryState (depends on meaning)
SchemaBuilder → BuildDatabaseSchema (if it builds DB schema)
Schema → DatabaseSchema (if it represents schema model)
BuildHtmlResponse → CreateHtmlResponse (if it creates, not sends)
```

### Rule

"Send" means side effect (transport). "Create" means object construction.

### Tooling

```bash
php tooling/refactor/check-forbidden-names.php
php tooling/refactor/check-ambiguous-names.php
```

### Deliverables

```txt
EVIDENCE/v5/naming-convergence.md
```

---

## V5-16 Traits / Multi-Class Files / Empty Classes Cleanup

### Goal

Clean code and structure hygiene.

### Multi-Class Files

Rule:

```txt
one production class per file
```

Exceptions:

```txt
test fixtures
tiny anonymous classes
```

Tool:

```bash
php tooling/refactor/check-one-class-per-file.php
```

### Empty "Silence the Tool" Classes

```txt
fill with real code
or delete
or mark as ROADMAP fixture if justified
```

Tool:

```bash
php tooling/refactor/check-empty-production-classes.php
```

### Traits

Traits are not illegal. But they are often a smell.

Allowed for:

```txt
small language-level reuse
test helpers
pure behavior mixins without state
```

Suspect for:

```txt
business logic
runtime state
large behavior
hidden dependencies
lifecycle methods
```

Plan:

```txt
Audit traits.
Keep only justified traits.
Move stateful/complex traits to explicit classes.
```

Tool:

```bash
php tooling/refactor/check-trait-overuse.php
```

### Deliverables

```txt
EVIDENCE/v5/traits-multiclass-empty-cleanup.md
```

---

## V5-17 Serialization & Payload Safety

### Goal

No unsafe payloads.

### Rules

```txt
Callable payload → CallableSerialization (existing)
Job payload → JobPayloadCodec
Message envelope → MessageSerializer
Session payload → SessionPayloadCodec
Cache payload → CacheValueCodec
Compiled metadata → CompiledMetadataSerializer
```

### Forbidden

```txt
serialize($closure) without signing
unserialize($payload) without verification
@unserialize suppression
unsigned worker payload
session unserialize
```

### Required

```txt
signature on all payloads
version discriminator
type discriminator
safe decoder
clear exception on failure
```

### Deliverables

```txt
EVIDENCE/v5/serialization-payload-safety.md
```

---

## V5-18 Async / Concurrency / Parallelism Adoption

### Goal

If AvaX already has async/concurrency/parallelism, it must use them.

### Candidates

```txt
parallel component doctor checks
parallel benchmark suites
parallel schema generation
parallel OpenAPI generation
parallel route cache warmup
parallel filesystem/project scans in tooling
async service-to-service calls (later)
async queue worker (later)
```

### Separation

```txt
Concurrency = Fibers/cooperative tasks (components/Operations/Concurrency/)
Parallelism = process-based isolated work (components/Operations/Parallelism/)
Async I/O = runtime-specific adapter capability (V4-17)
```

### Rule

Do not use Fiber if it does blocking I/O internally and then sells itself as async.

### Deliverables

```txt
EVIDENCE/v5/async-concurrency-parallelism-adoption.md
```

---

## V5-19 Pooling & Resource Lifecycle Optimization

### Goal

Long-lived runtime must have resource discipline.

### Pooling Candidates

```txt
DB connection pool (components/DataStack/Database/)
HTTP client/cURL share handle pool
worker process pool
queue worker pool
message consumer pool
compiled metadata cache pool
```

PHP 8.5 has persistent cURL share handles, useful for connection reuse in HTTP client.

### Tests

```txt
pool acquire/release
max limit enforcement
health check on acquire
reset on release
no request state leak
metrics
doctor check
```

### Deliverables

```txt
EVIDENCE/v5/pooling-resource-lifecycle.md
```

---

## V5-20 Hot Path Cache & Compiled Metadata

### Goal

Framework must be fast by design.

### Hot Path Metadata

```txt
routes
controller signatures
DI autowiring metadata
SecureRequest metadata
DataTransfer metadata
validation attributes
policy attributes
middleware pipeline
OpenAPI/schema
command map
queue consumer map
message handlers
```

### Cache Rules

```txt
cache has freshness check
cache has invalidation
cache has doctor check
cache has clear command
cache not used to hide bugs
```

### CLI

```txt
php avax cache:warm
php avax cache:clear
php avax metadata:compile
php avax metadata:clear
php avax route:cache
php avax config:cache
```

### Deliverables

```txt
EVIDENCE/v5/hot-path-cache-compiled-metadata.md
```

---

## V5-21 Tooling Gates & Custom Rector Rules

### Tooling as Gates

`tooling/` is not a random scripts folder. It is AvaX internal quality court.

### Gate Scripts

```bash
php tooling/refactor/check-component-adoption.php
php tooling/refactor/check-dogfooding-matrix.php
php tooling/refactor/check-raw-file-operations.php
php tooling/refactor/check-raw-reflection-hot-path.php
php tooling/refactor/check-superglobal-usage.php
php tooling/refactor/check-naked-domain-strings.php
php tooling/refactor/check-one-class-per-file.php
php tooling/refactor/check-empty-production-classes.php
php tooling/refactor/check-trait-overuse.php
php tooling/refactor/check-security-blockers.php
php tooling/refactor/check-constructor-bloat.php
php tooling/refactor/check-container-service-locator.php
php tooling/refactor/check-modern-php-style.php
php tooling/refactor/check-cacheable-metadata.php
```

### Custom Rector Rules

Rector rules:

```txt
Convert raw strings to enum candidates report
Convert simple constructor assignments to property promotion
Replace getter/setter with property hooks where safe
Replace raw file operation with Filesystem call where safe
Replace raw superglobal usage with Request object where safe
Split multi-class file where safe
Replace @unserialize with safe codec
Replace BuildHtmlResponse naming if rule can infer behavior
Report naked domain strings that should be enums
Report reflection in hot paths
Report constructor bloat (8+ dependencies)
```

Rector must not aggressively change semantics without tests.

### Deliverables

```txt
tooling/refactor/check-*.php (14+ gate scripts)
rector/rules/ (custom Rector rules)
EVIDENCE/v5/tooling-gates-final-report.md
```

---

## V5-22 E2E Tests / Reference Runtime Proof

### Goal

End-to-end proof that the framework works as a system.

### Approach

Start with PHPUnit E2E suite. Docker compose later.

### First Phase

```txt
PHPUnit E2E suite
examples/v4 apps as test subjects
runtime smoke server
SQLite
local filesystem
memory queue
database queue
```

### Docker Compose Later

```txt
PostgreSQL
Redis
RoadRunner
Swoole/OpenSwoole
FrankenPHP
```

### E2E Scenarios

```txt
secure registration flow
url shortener flow
webhook → inbox → queue → outbox → projection
file upload → storage validation
queue failure → retry → dead letter
doctor detects broken dependency
health endpoints respond correctly
request signing verification
policy deny/allow evaluation
feature flag toggle
```

### Deliverables

```txt
tests/E2E/ (new test directory)
EVIDENCE/v5/e2e-test-report.md
```

---

## V5-23 Final V5 Truth Report

### Deliverables

```txt
EVIDENCE/v5/final-v5-truth-report.md
EVIDENCE/v5/dogfooding-final-report.md
EVIDENCE/v5/modern-php-final-report.md
EVIDENCE/v5/security-final-report.md
EVIDENCE/v5/tooling-gates-final-report.md
```

### GREEN Criteria

```txt
- V4 truth locked
- capability ownership map complete
- dogfooding matrix complete
- critical security blockers removed
- raw superglobals isolated behind AvaX Request
- raw file operations controlled behind Filesystem
- raw reflection removed from hot paths
- enums/value objects used for domain/system states
- modern PHP 8.0-8.5 features applied where useful
- attribute runtime compiled, not reflection-per-request
- DI/autowiring clean, no constructor bloat without justification
- tooling gates pass
- custom Rector rules exist or are planned with examples
- E2E suite proves runtime scenarios
- full tests pass
- PHPStan clean
- governance pass
```

---

# V5.5 Stage Definitions

## V5.5-00 Benchmark Methodology Lock

### Rules

```txt
same machine
same PHP version
same opcache/JIT config
same request body
same response body
same database scenario
same concurrency
same warm/cold distinction
same iterations
same reporting format
```

Without this, no comparison is valid.

### Deliverables

```txt
EVIDENCE/v5.5/benchmark-methodology-lock.md
```

---

## V5.5-01 Hardware / Environment Baseline

### Record

```txt
CPU
RAM
disk
OS
kernel
PHP version
extensions
opcache config
JIT config
composer deps
server/runtime
```

### CLI

```bash
php avax benchmark:environment
```

### Deliverables

```txt
EVIDENCE/v5.5/environment-baseline.md
```

---

## V5.5-02 Microbenchmarks

### Measure

```txt
route match
compiled route match
container resolve
controller invoke
SecureRequest hydrate
DataTransfer validate
policy evaluate
feature flag evaluate
request sign/verify
schema metadata read
compiled metadata read
```

### Deliverables

```txt
EVIDENCE/v5.5/microbenchmark-results.md
```

---

## V5.5-03 Runtime Benchmarks

### Measure

```txt
cold boot time
warm boot time
single request latency
1000 warm requests latency
memory before/after
request reset cost
MemoryGuard overhead
Observability off vs on overhead
```

### Deliverables

```txt
EVIDENCE/v5.5/runtime-benchmark-results.md
```

---

## V5.5-04 HTTP Throughput Benchmarks

### Runtime Profiles

```txt
PHP-FPM baseline (if supported)
ReactPHP
RoadRunner (if implemented)
Swoole/OpenSwoole (if implemented)
FrankenPHP (if implemented)
```

### Metrics

```txt
RPS
latency avg
p50
p95
p99
memory
CPU
error rate
```

### Deliverables

```txt
EVIDENCE/v5.5/http-throughput-results.md
```

---

## V5.5-05 Reference App Benchmarks

### Benchmark Real Apps

```txt
hello-world
secure-registration-api
url-shortener
webhook-receiver
queue-worker-demo
outbox-messaging-demo
file-upload-storage-demo
runtime-doctor-demo
```

### Deliverables

```txt
EVIDENCE/v5.5/reference-app-benchmarks.md
```

---

## V5.5-06 Long-Running Worker Soak Tests

### Test

```txt
10k requests
100k requests if feasible
memory growth tracking
state leakage detection
response correctness verification
request scope reset verification
container scoped reset verification
correlation context reset verification
```

### Deliverables

```txt
EVIDENCE/v5.5/soak-test-results.md
```

---

## V5.5-07 Memory Leak & State Leak Tests

### Test

```txt
same app
many requests
different users
different correlation IDs
different sessions
prove no leakage
```

### Deliverables

```txt
EVIDENCE/v5.5/memory-leak-test-results.md
```

---

## V5.5-08 Database / Queue / Messaging Throughput

### Measure

```txt
DB acquire/release latency
pooled query throughput
transaction overhead
queue dispatch latency
queue worker throughput
failed job retry correctness
dead letter handling
outbox write latency
outbox relay throughput
inbox duplicate check correctness
projection update throughput
```

### Deliverables

```txt
EVIDENCE/v5.5/db-queue-messaging-throughput.md
```

---

## V5.5-09 Observability & Security Overhead

### Measure

```txt
request without observability (baseline)
request with in-memory metrics
request with file logs
request with traces
request with audit trail
request signing overhead
policy evaluation overhead
redaction overhead
```

### Deliverables

```txt
EVIDENCE/v5.5/observability-security-overhead.md
```

---

## V5.5-10 Framework Comparison Suite

### Compare by Segments

PHP-FPM style:

```txt
Laravel
Symfony
Slim
Mezzio
```

High-performance PHP:

```txt
Laravel Octane
Spiral / RoadRunner
Hyperf / Swoole
Framework X / ReactPHP
```

Micro:

```txt
Flight
Slim minimal
custom PSR-15 baseline
```

### Rule

No name-calling. No "we are fastest" until numbers show it.

### Deliverables

```txt
EVIDENCE/v5.5/framework-comparison-results.md
```

---

## V5.5-11 Optimization Pass

### Based on Benchmarks

```txt
optimize route matching
optimize container resolution
optimize metadata cache
optimize DataTransfer hydration
optimize validation attribute mapping
optimize observability overhead
optimize DB pool acquire/release
optimize queue worker loop
```

### Every Optimization Must Have

```txt
before measurement
after measurement
diff
risk assessment
test coverage
benchmark proof
```

### Deliverables

```txt
EVIDENCE/v5.5/optimization-results.md
```

---

## V5.5-12 Final World-Class Readiness Report

### Final Report Must Say

```txt
AvaX is:
- experimental
- strong internal framework
- production candidate
- high-performance candidate
- world-class candidate
- top-tier competitive
```

Must not say "top 5" if numbers do not support it.

### Deliverables

```txt
EVIDENCE/v5.5/final-world-class-readiness-report.md
EVIDENCE/v5.5/framework-comparison-results.md
EVIDENCE/v5.5/performance-regression-baseline.md
EVIDENCE/v5.5/optimization-results.md
```

### V5.5 GREEN Criteria

```txt
- benchmark methodology locked
- environment baseline recorded
- microbenchmarks run
- runtime benchmarks run
- reference app benchmarks run
- soak tests run
- memory leak tests run
- DB/Queue/Messaging throughput measured
- observability/security overhead measured
- comparison suite exists
- optimization pass done
- final report says honestly where AvaX stands
```

---

# V5.6 Stage Definitions

## V5.6 Name

```txt
V5.6 — System & Component Governance Code Review
```

Short name:

```txt
V5.6 — AvaX Judges AvaX
```

## Purpose

Review the whole AvaX system and every major component separately against every mandatory how-to governance document.

The review must tell us exactly:

```txt
- what is compliant
- what is partially compliant
- what is not compliant
- where it fails
- why it matters
- what must be fixed
- whether the system/component should be kept, redesigned, or rewritten
```

V5.6 is not a refactor phase.

V5.6 is review only.

```text
Do not fix during V5.6.
Do not start from individual files.
Do not optimize before reconstructing architecture.
Do not propose broad rewrites without evidence.
Do not call system sound without governance compliance.
```

The result of V5.6 is a decision and action map.

Fixes happen after V5.6, in a separate phase.

---

## V5.6-00 Review Governance Lock

### Goal

Make sure the review process itself is current.

### Required

```txt
- discover all how-to-*.md files
- compare discovered files with how-to-code-review.md checklist
- update stale checklist
- confirm AGENTS.md precedence
- confirm V5/V5.5 plan status
- confirm CURRENT_TRUTH.md state
- confirm all review output folders exist
```

### Deliverables

```txt
EVIDENCE/v5.6/review-governance-lock.md
EVIDENCE/v5.6/governance-inventory.md
```

GREEN only if review governance knows every mandatory how-to document.

---

## V5.6-01 Whole-System As-Built Execution Flow Review

### Required

```txt
- reconstruct real execution flow from public API to final effect
- identify primary axis (central abstraction)
- identify secondary axis if present
- build responsibility and boundary map
- perform mutability audit
- define system invariants (at least 5)
- performance-by-design sanity check
- failure mode sanity check
- security boundary sanity check
- dogfooding/composition map
```

### Deliverables

```txt
EVIDENCE/v5.6/whole-system-review.md
```

Final decision must be exactly one:

```txt
Keep and Improve
Redesign
Rewrite Candidate
```

No vague result.

---

## V5.6-02 Whole-System Governance Compliance Review

### Required

```txt
- check whole system against every mandatory how-to document
- produce governance compliance matrix
- record every Partial/Fail/Blocked with required action
- use mandatory governance finding template
```

### Deliverables

```txt
EVIDENCE/v5.6/system-governance-compliance-report.md
EVIDENCE/v5.6/system-findings.md
```

---

## V5.6-03 Runtime / Framework Core Review

### Scope

```txt
framework/System/
framework/System/PublicSurface/
framework/System/Flows/
framework/System/Capabilities/
framework/System/Configuration/
```

### Required

```txt
- runtime entry points
- request lifecycle
- response normalization
- error handling path
- middleware pipeline
- container wiring
- route compilation and matching
- warm worker safety
- memory guard behavior
```

### Deliverables

```txt
EVIDENCE/v5.6/runtime-core-review.md
```

---

## V5.6-04 Component-by-Component Review

Every major component must get its own review file.

### Output Location

```txt
EVIDENCE/v5.6/components/<component-name>-review.md
```

### Each Review Must Include

```txt
- component name
- component owner path
- current status
- public API
- as-built flow
- primary responsibility
- component invariants
- PublicSurface status
- Flows status
- Capabilities status
- Configuration status
- Foundation status
- dogfooding relationships
- security review
- performance review
- test review
- documentation review
- governance compliance matrix
- findings
- required actions
- final decision: Keep and Improve / Redesign / Rewrite Candidate
```

### Minimum Components to Review

```txt
Runtime
Container
Router
Request
Response
SecureRequest
DataTransfer
SchemaGeneration
Filesystem
Storage
Cache
Database
Queue
Messaging
Reliability
Observability
Security / Policy
SystemDesignKit
Benchmarks
CLI / Console
Serialization / CallableSerialization
Concurrency
Parallelism
```

### Deliverables

```txt
EVIDENCE/v5.6/components/<component-name>-review.md (one per component)
EVIDENCE/v5.6/component-review-index.md
```

---

## V5.6-05 PublicSurface Review

### Required

```txt
- every PublicSurface class must be thin
- no runtime behavior in PublicSurface
- no mutable state in PublicSurface
- delegation to flows/capabilities only
- no raw file/serialization/retry logic
```

### Deliverables

```txt
Included in EVIDENCE/v5.6/whole-system-review.md
and per-component reviews
```

---

## V5.6-06 Dogfooding Review

### Required

```txt
- verify every consumer uses the correct owner
- detect duplicate capability owners
- check dependency direction (no cycles, no reverse depends)
- verify PublicSurface thinness across all components
- check no raw file/process/serialization/retry logic outside owners
- verify dogfooding matrix from V5-04 is accurate
```

### Mandatory Governance

```txt
how-to-dogfooding.md
```

### Deliverables

```txt
Included in EVIDENCE/v5.6/system-governance-compliance-report.md
```

---

## V5.6-07 Modern PHP / Attributes / DI Review

### Required

```txt
- verify PHP 8.0-8.5 feature adoption
- verify attributes are compiled, not scanned per request
- verify compiled metadata exists and is cached
- verify no reflection in hot paths
- verify constructor dependencies are 0-4 (or justified 5-7)
- verify no Container::get() in business logic
- verify method injection used for runtime context only
- verify pipe operator used for pure transformations only
- verify superglobals isolated behind AvaX Request
- verify enums used for closed domain/state values
- verify readonly used for honestly immutable objects
- verify final by default unless extension point
- verify property hooks without side-effect lifecycle logic
- verify asymmetric visibility for public-read/private-write
- verify NoDiscard on important return values
- verify WeakMap for per-object cache, not persistent cache
```

### Mandatory Governance

```txt
how-to-modern-php-attributes-di.md
```

### Deliverables

```txt
Included in EVIDENCE/v5.6/system-governance-compliance-report.md
and per-component reviews
```

---

## V5.6-08 Security Review

### Required

```txt
- review all security-sensitive boundaries
- verify session ID not logged
- verify callable serialization is signed/encrypted
- verify no service locator in business logic
- verify no raw SQL interpolation
- verify no unserialize on untrusted data
- verify no secret exposure in logs/telemetry
- verify input validation at all boundaries
- verify output encoding
- verify authorization protects the object, not only the route
```

### Mandatory Governance

```txt
how-to-system-security.md
```

### Deliverables

```txt
EVIDENCE/v5.6/security-review.md
```

---

## V5.6-09 Performance Review

### Required

```txt
- identify hot paths
- verify no hidden I/O in hot paths
- verify no unbounded public operation
- verify no retry without limit
- verify no external call without timeout
- verify compiled metadata used in hot paths
- verify connection pooling where applicable
- verify no memory leak in long-lived workers
- verify V5.5 benchmark claims are consistent with code review
```

### Mandatory Governance

```txt
how-to-system-performance.md
```

### Deliverables

```txt
EVIDENCE/v5.6/performance-review.md
```

---

## V5.6-10 Testing Review

### Required

```txt
- verify tests prove behavior, not implementation trivia
- verify security/performance-sensitive behavior has negative/boundary tests
- verify no over-mocked tests that freeze internals
- verify test naming clarity
- verify Arrange/Act/Assert discipline
- verify one-act rule
- verify assertion precision
- verify test data clarity
```

### Mandatory Governance

```txt
how-to-unit-test.md
```

### Deliverables

```txt
EVIDENCE/v5.6/testing-review.md
```

---

## V5.6-11 Documentation Review

### Required

```txt
- verify docs explain why, not restate code
- verify component READMEs are ownership summaries only
- verify canonical docs explain architecture
- verify how-this-works.md files exist where needed
- verify mermaid diagrams are real (not generic placeholders)
- verify debug-first guidance exists
- verify documentation completeness for public APIs
```

### Mandatory Governance

```txt
how-to-document.md
```

### Deliverables

```txt
EVIDENCE/v5.6/documentation-review.md
```

---

## V5.6-12 Production Readiness Review

### Required

```txt
- verify architecture matches design
- verify taxonomy is canonical
- verify autoload is clean
- verify namespaces match ownership
- verify tests target canonical classes
- verify static analysis is clean
- verify runtime safety is proven
- verify public surface does not leak internals
- verify security baseline is documented and tested
- verify performance baseline is documented and measured
- verify observability baseline exists
- verify golden path app works through public APIs
- verify production-readiness report agrees with validation
```

### Mandatory Governance

```txt
how-to-production-readiness.md
```

### Deliverables

```txt
EVIDENCE/v5.6/production-readiness-review.md
```

---

## V5.6-13 Rewrite / Redesign / Keep Decision Report

### Required

For the whole system and every reviewed component, the report must state exactly one:

```txt
Keep and Improve — system/component is sound, proceed with incremental evolution
Redesign — core assumptions are stressed, targeted redesign required
Rewrite Candidate — foundational design is flawed, rewrite is rational
```

### Each Decision Must Include

```txt
- decision for whole system
- decision for every reviewed component
- justification referencing findings
- constraints (API stability, performance budget, security boundaries)
- kill criteria
- first 3 concrete actions
- migration strategy if Rewrite Candidate
```

### Deliverables

```txt
EVIDENCE/v5.6/final-review-decision.md
```

---

## V5.6-14 Final Review Closure Report

### Required

```txt
- all how-to documents discovered
- how-to-code-review.md checklist is current
- whole-system review exists
- every major component review exists
- every review includes governance compliance matrix
- every Partial/Fail/Blocked item has exact required action
- final decision exists for whole system
- final decision exists for every reviewed component
- no governance blocker remains unresolved or undocumented
- review output points to evidence
- final report tells exactly what next phase must fix
```

### Deliverables

```txt
EVIDENCE/v5.6/next-actions-after-review.md
EVIDENCE/v5.6/final-review-closure.md
```

---

## V5.6 Governance Compliance Matrix

Every system and component review must include this matrix:

```text
| Governance Document | Applies? | Status | Evidence | Missing / Weak Area | Required Action | Severity |
```

Status values:

```text
Pass
Partial
Fail
Not Applicable
Blocked
```

Severity:

```text
Low
Medium
High
Blocker
```

For every Partial, Fail, or Blocked: write a governance finding using the mandatory template.

---

## V5.6 Mandatory Finding Template

Every finding must include:

```md
### Governance Finding: <short title>

- **Governance Source:** `<how-to-file.md>` -> `<section/rule>`
- **Required Rule:** ...
- **Observed Gap:** ...
- **Where It Fails:** file/folder/class/method/test/doc path
- **Why It Matters:** ...
- **Required Action:** add / remove / rename / move / split / merge / simplify / document / test / harden / rewrite / deprecate
- **Suggested Fix:** concrete proposed change, rewrite, or replacement shape
- **Severity:** Low / Medium / High / Blocker
- **Evidence:** concrete pointers
```

---

## V5.6 Acceptance Criteria

V5.6 is GREEN only if:

```txt
- all how-to documents are discovered
- how-to-code-review.md checklist is current
- whole-system review exists
- every major component review exists
- every review includes governance compliance matrix
- every Partial/Fail/Blocked item has exact required action
- final decision exists for whole system
- final decision exists for every reviewed component
- no governance blocker remains unresolved or undocumented
- review output points to evidence
- final report tells exactly what V5.7 or next phase must fix
```

V5.6 is YELLOW if:

```txt
- reviews are complete but non-blocking gaps remain
- some components are Keep and Improve, some need Redesign
- no Rewrite Candidate exists
```

V5.6 is RED if:

```txt
- governance checklist is stale
- review skipped mandatory how-to documents
- as-built execution flow cannot be produced
- primary axis cannot be identified
- component boundaries cannot be mapped
- review findings lack evidence
```

---

## V5.6 Deliverables

```txt
EVIDENCE/v5.6/review-governance-lock.md
EVIDENCE/v5.6/governance-inventory.md
EVIDENCE/v5.6/whole-system-review.md
EVIDENCE/v5.6/system-governance-compliance-report.md
EVIDENCE/v5.6/system-findings.md
EVIDENCE/v5.6/component-review-index.md
EVIDENCE/v5.6/components/<component-name>-review.md
EVIDENCE/v5.6/final-review-decision.md
EVIDENCE/v5.6/next-actions-after-review.md
```

Updates required:

```txt
CURRENT_TRUTH.md
TODO.md
EVIDENCE/EXECUTION.md
V5/V5.5/V5.6 master plan
```

---

# What Goes Where

## V5

```txt
clean code
dogfooding
modern PHP 8.0-8.5
attributes/annotations
DI/autowiring
security cleanup
compiled metadata
cache strategy
data structures adoption
enum/domain value cleanup
superglobal isolation via AvaX Request
tooling gates
custom Rector rules
E2E foundation
router completion
naming convergence
traits/multiclass/empty cleanup
serialization safety
async/concurrency/parallelism adoption
pooling/resource lifecycle
hot path cache
```

## V5.5

```txt
benchmark methodology
hardware/environment baseline
microbenchmarks
runtime benchmarks
HTTP throughput
reference app benchmarks
long-running worker soak tests
memory leak tests
DB/Queue/Messaging throughput
observability/security overhead
framework comparison suite
optimization pass
world-class readiness report
```

---

# Key Design Decisions

## 1. Annotations/Attributes Are Central V5 Style

AvaX will look like:

```php
#[Controller]
#[Route('/users')]
final readonly class RegisterUserController
{
    public function __construct(
        private RegisterUser $registerUser,
    ) {}

    #[Post]
    #[Policy('user.register')]
    #[Audit('user.registered')]
    public function __invoke(
        #[Body] RegisterRequest $request,
    ): JsonResponse {
        return $request
            |> $this->registerUser(...)
            |> UserResource::from(...)
            |> JsonResponse::ok(...);
    }
}
```

But runtime must use compiled metadata, not reflection-per-request.

## 2. Constructor Property Promotion Is Default

```php
final readonly class CreateUser
{
    public function __construct(
        private UserRepository $users,
        private HashPassword $hashPassword,
    ) {}
}
```

## 3. Constructor Bloat Is Solved by Design

If a class has 12 dependencies, maybe it does too much.

```txt
0–4 dependencies: normal
5–7: check responsibility
8+: architecture warning
```

## 4. Method Injection Is for Runtime Context

```txt
Request
CurrentUser
PolicyContext
TraceContext
TransactionScope
QueueJobContext
```

Not for permanent domain dependencies.

## 5. Pipe Operator for Transformations Only

Not for side-effect heavy code.

Good:

```php
$schema = $class
    |> $this->readShape(...)
    |> $this->convertToJsonSchema(...)
    |> $this->normalizeSchema(...);
```

Bad:

```php
$request
    |> $this->save(...)
    |> $this->sendEmail(...)
    |> $this->chargeCard(...);
```

Use pipeline/flow there.

## 6. AvaX Request, Not PHP Core Request

PHP 8.5 does not have a core Request class. AvaX will implement its own framework-owned Request object that isolates
superglobals and uses modern PHP features internally.

## 7. PHP 8.x Features Are Tools, Not Decoration

Every PHP 8.x feature must have a clear use case:

```txt
Attributes → routing, DI, validation, policy, audit, tracing
Enums → domain/system state
Property hooks → value normalization, invariant enforcement
Asymmetric visibility → public-read/private-write state
Pipe operator → pure data transformations
WeakMap → object metadata caches
Fibers → real cooperative concurrency
JIT → benchmark profile, not assumed speedup
```

---

# V5 Acceptance Criteria

V5 is GREEN if:

```txt
- V4 truth lock clean
- governance resolution complete
- capability ownership map complete
- dogfooding matrix complete
- critical security blockers fixed
- raw superglobals isolated behind AvaX Request
- raw file operations controlled behind Filesystem
- raw reflection removed from hot paths
- enums/value objects replace system/domain strings
- modern PHP 8.0-8.5 features applied where useful
- attributes compile to metadata, not reflection-per-request
- DI/autowiring clean
- no constructor bloat without justification
- DataStack structures used where they improve correctness/performance
- Arrhae/Collection/Json boundaries clear
- cache strategy implemented for hot metadata
- tooling gates enforce rules
- custom Rector rules exist or are planned with examples
- E2E suite proves runtime scenarios
- full validation green: tests, PHPStan, governance
- every mandatory how-to document was checked
- every applicable how-to rule was applied
- every non-applicable how-to rule has a documented reason
- governance compliance matrix exists for every stage
- no unresolved governance blocker remains
- all V5 tooling gates pass
```

V5 may be GREEN only if:

```txt
- every mandatory how-to document was checked
- every applicable how-to rule was applied
- every non-applicable how-to rule has a reason
- governance compliance matrix exists for every stage
- no unresolved governance blocker remains
- full validation passes
- all V5 tooling gates pass
```

# V5.5 Acceptance Criteria

V5.5 is GREEN if:

```txt
- benchmark methodology locked
- environment baseline recorded
- microbenchmarks run
- runtime benchmarks run
- reference app benchmarks run
- soak tests run
- memory leak tests run
- DB/Queue/Messaging throughput measured
- observability/security overhead measured
- comparison suite exists
- optimization pass done
- final report says honestly where AvaX stands
```

V5.5 may be GREEN only if:

```txt
- benchmark methodology follows how-to-system-performance.md
- benchmark claims are backed by evidence
- comparison claims are not marketing
- production readiness follows how-to-production-readiness.md
- security-sensitive benchmark paths follow how-to-system-security.md
- final review follows how-to-code-review.md
- every mandatory how-to document was checked for V5.5 stages
- governance compliance matrix exists for every V5.5 stage
- no unresolved governance blocker remains
```

---

# Stage Lock

```txt
V5-00 cannot start until V4 truth lock is GREEN.
V5-01 cannot start until V5-00 is GREEN.
V5-02 cannot start until V5-01 is GREEN.
Each subsequent stage depends on previous stage GREEN.
V5.5-00 cannot start until V5-23 is GREEN.
Each V5.5 stage depends on previous V5.5 stage GREEN.
V5.6-00 cannot start until V5-23 is GREEN and V5.5-12 is GREEN.
Each V5.6 stage depends on previous V5.6 stage GREEN.
```

---

# Next Planned Major Phase

```txt
V5   — AvaX Eats AvaX (self-hardening, modern PHP, attributes, DI, compiled metadata)
V5.5 — AvaX Proves AvaX (benchmark/proof phase)
V5.6 — AvaX Judges AvaX (system & component governance code review)
```

V5 starts after V4 truth lock is confirmed GREEN.

---

# Deliverables Index

## Evidence Files (V5)

```txt
EVIDENCE/v5/v4-final-truth-lock.md
EVIDENCE/v5/governance-resolution-map.md
EVIDENCE/v5/source-of-truth-order.md
EVIDENCE/v5/security-blocker-cleanup.md
EVIDENCE/v5/capability-ownership-map.md
EVIDENCE/v5/duplicate-capability-owners.md
EVIDENCE/v5/dogfooding-adoption-matrix.md
EVIDENCE/v5/filesystem-storage-cache-adoption.md
EVIDENCE/v5/reflection-to-compiled-metadata.md
EVIDENCE/v5/modern-php-8x-adoption-map.md
EVIDENCE/v5/attribute-runtime-plan.md
EVIDENCE/v5/compiled-attribute-metadata.md
EVIDENCE/v5/di-autowiring-clean-code.md
EVIDENCE/v5/data-structures-adoption-map.md
EVIDENCE/v5/weakmap-cache-policy.md
EVIDENCE/v5/arrhae-collection-json-product-boundary.md
EVIDENCE/v5/enum-domain-value-cleanup.md
EVIDENCE/v5/request-object-superglobal-isolation-plan.md
EVIDENCE/v5/router-completion.md
EVIDENCE/v5/naming-convergence.md
EVIDENCE/v5/traits-multiclass-empty-cleanup.md
EVIDENCE/v5/serialization-payload-safety.md
EVIDENCE/v5/async-concurrency-parallelism-adoption.md
EVIDENCE/v5/pooling-resource-lifecycle.md
EVIDENCE/v5/hot-path-cache-compiled-metadata.md
EVIDENCE/v5/tooling-gates-final-report.md
EVIDENCE/v5/e2e-test-report.md
EVIDENCE/v5/final-v5-truth-report.md
EVIDENCE/v5/dogfooding-final-report.md
EVIDENCE/v5/modern-php-final-report.md
EVIDENCE/v5/security-final-report.md
```

## Evidence Files (V5.5)

```txt
EVIDENCE/v5.5/benchmark-methodology-lock.md
EVIDENCE/v5.5/environment-baseline.md
EVIDENCE/v5.5/microbenchmark-results.md
EVIDENCE/v5.5/runtime-benchmark-results.md
EVIDENCE/v5.5/http-throughput-results.md
EVIDENCE/v5.5/reference-app-benchmarks.md
EVIDENCE/v5.5/soak-test-results.md
EVIDENCE/v5.5/memory-leak-test-results.md
EVIDENCE/v5.5/db-queue-messaging-throughput.md
EVIDENCE/v5.5/observability-security-overhead.md
EVIDENCE/v5.5/framework-comparison-results.md
EVIDENCE/v5.5/optimization-results.md
EVIDENCE/v5.5/final-world-class-readiness-report.md
EVIDENCE/v5.5/performance-regression-baseline.md
```

## Evidence Files (V5.6)

```txt
EVIDENCE/v5.6/review-governance-lock.md
EVIDENCE/v5.6/governance-inventory.md
EVIDENCE/v5.6/whole-system-review.md
EVIDENCE/v5.6/system-governance-compliance-report.md
EVIDENCE/v5.6/system-findings.md
EVIDENCE/v5.6/component-review-index.md
EVIDENCE/v5.6/components/runtime-review.md
EVIDENCE/v5.6/components/container-review.md
EVIDENCE/v5.6/components/router-review.md
EVIDENCE/v5.6/components/request-review.md
EVIDENCE/v5.6/components/response-review.md
EVIDENCE/v5.6/components/secure-request-review.md
EVIDENCE/v5.6/components/data-transfer-review.md
EVIDENCE/v5.6/components/schema-generation-review.md
EVIDENCE/v5.6/components/filesystem-review.md
EVIDENCE/v5.6/components/storage-review.md
EVIDENCE/v5.6/components/cache-review.md
EVIDENCE/v5.6/components/database-review.md
EVIDENCE/v5.6/components/queue-review.md
EVIDENCE/v5.6/components/messaging-review.md
EVIDENCE/v5.6/components/reliability-review.md
EVIDENCE/v5.6/components/observability-review.md
EVIDENCE/v5.6/components/security-policy-review.md
EVIDENCE/v5.6/components/system-design-kit-review.md
EVIDENCE/v5.6/components/benchmarks-review.md
EVIDENCE/v5.6/components/cli-console-review.md
EVIDENCE/v5.6/components/serialization-review.md
EVIDENCE/v5.6/components/concurrency-review.md
EVIDENCE/v5.6/components/parallelism-review.md
EVIDENCE/v5.6/security-review.md
EVIDENCE/v5.6/performance-review.md
EVIDENCE/v5.6/testing-review.md
EVIDENCE/v5.6/documentation-review.md
EVIDENCE/v5.6/production-readiness-review.md
EVIDENCE/v5.6/final-review-decision.md
EVIDENCE/v5.6/next-actions-after-review.md
EVIDENCE/v5.6/final-review-closure.md
```

## Tooling Scripts

```txt
tooling/refactor/check-security-blockers.php
tooling/refactor/check-component-adoption.php
tooling/refactor/check-dogfooding-matrix.php
tooling/refactor/check-raw-file-operations.php
tooling/refactor/check-raw-reflection-hot-path.php
tooling/refactor/check-superglobal-usage.php
tooling/refactor/check-naked-domain-strings.php
tooling/refactor/check-one-class-per-file.php
tooling/refactor/check-empty-production-classes.php
tooling/refactor/check-trait-overuse.php
tooling/refactor/check-constructor-bloat.php
tooling/refactor/check-container-service-locator.php
tooling/refactor/check-modern-php-style.php
tooling/refactor/check-cacheable-metadata.php
tooling/refactor/check-forbidden-names.php
tooling/refactor/check-ambiguous-names.php
```

## Custom Rector Rules

```txt
rector/rules/StringToEnumCandidateRector.php
rector/rules/ConstructorPromotionRector.php
rector/rules/PropertyHookRector.php
rector/rules/RawFileToFilesystemRector.php
rector/rules/SuperglobalToRequestRector.php
rector/rules/MultiClassFileSplitRector.php
rector/rules/UnsafeUnserializeRector.php
rector/rules/AmbiguousNameRector.php
rector/rules/NakedDomainStringRector.php
rector/rules/ReflectionHotPathRector.php
rector/rules/ConstructorBloatRector.php
```
