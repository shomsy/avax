# How to System Performance

## 1. Status of This Document

This document is part of the AvaX governance system.

It defines how performance must be designed, implemented, reviewed, tested, measured, observed, and proven across AvaX.

This document applies to:

```text
framework runtime
components
public surfaces
flows
capabilities
configuration
foundation primitives
HTTP endpoints
console commands
workers
queues
events
message buses
database access
cache access
filesystem access
external service integrations
operator tools
developer tooling
examples
tests
AI-generated code
```

This document is not a micro-optimization guide.

This document is not permission to make code unreadable.

This document is not permission to weaken security, correctness, validation, or maintainability.

Performance must serve the system.

Performance must be proven with evidence.

The core AvaX architecture law still wins:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

Performance behavior must follow that law.

Do not create vague folders such as:

```text
Performance/
  Services/
  Managers/
  Helpers/
  Utils/
  Optimizers/
  Processors/
```

Performance behavior must be named by what it measures, protects, improves, limits, caches, compiles, batches, or
avoids.

Good:

```text
Capabilities/
  RouteCompilation/
    CompileRoutes.php
    ReadCompiledRouteTable.php

  QueryBudget/
    RecordQueryCount.php
    DetectTooManyQueries.php

  CacheWarming/
    WarmCompiledCache.php
    WarmRouteCache.php

  MemorySafety/
    DetectWorkerMemoryGrowth.php
    ResetRequestScopedState.php
```

Bad:

```text
Capabilities/
  Performance/
    PerformanceService.php
    OptimizerManager.php
    SpeedHelper.php
```

---

## 2. Performance Thesis

AvaX must be fast where speed matters.

AvaX must be predictable where latency matters.

AvaX must be memory-safe where workers are long-lived.

AvaX must be observable where performance can degrade.

AvaX must be honest where performance is unknown.

No code may be called fast, optimized, lightweight, scalable, production-grade, or high-performance without evidence.

Performance without measurement is opinion.

Performance without correctness is a bug.

Performance without security is a liability.

Performance without observability is temporary luck.

---

## 3. Non-Negotiable Performance Laws

### 3.1 Measure Before Optimizing

Do not optimize blindly.

Before making a performance change, identify at least one of:

```text
measured latency problem
measured memory problem
measured CPU problem
measured I/O problem
measured allocation problem
measured query problem
measured queue backlog
measured cache miss problem
known algorithmic complexity risk
hot path confirmed by design
```

No measurement and no clear hot path means no performance claim.

### 3.2 Correctness First

A faster wrong result is not an optimization.

Never sacrifice:

```text
security
authorization
validation
data integrity
transaction safety
idempotency
runtime state isolation
public API compatibility
failure handling
```

Performance must not remove safety.

### 3.3 Secure Performance

Do not improve performance by bypassing security controls.

Forbidden:

```text
skip authorization for speed
skip validation for speed
log secrets for easier debugging
disable TLS verification
remove CSRF protection
reuse request state in singleton
cache cross-user sensitive data
cache cross-tenant sensitive data
```

Security wins over speed.

### 3.4 Predictability Beats Peak Speed

AvaX should prefer predictable behavior over impressive single-run numbers.

Good systems control:

```text
p50 latency
p95 latency
p99 latency
memory growth
queue depth
connection pressure
retry storms
cache stampede
tail latency
```

A fast average with terrible tail latency is not good performance.

### 3.5 No Hidden I/O

A method that looks like a local calculation must not secretly perform network, database, filesystem, queue, or external
service I/O.

If I/O happens, the name, boundary, timeout, failure behavior, and observability must make it visible.

Bad:

```php
$user->displayName();
```

if it loads data from a database.

Good:

```php
$profiles->readDisplayName(userId: $userId);
```

### 3.6 Bounded Work

Every public or repeatable operation must have bounded work.

Bound at least one of:

```text
input size
batch size
page size
loop count
query count
memory use
external calls
queue fanout
concurrency
time budget
```

Unbounded work is a production incident waiting to happen.

### 3.7 Performance Needs Proof

No performance status can be green without evidence.

Evidence may be:

```text
benchmark
profile
load test
complexity analysis
memory measurement
latency measurement
query count measurement
cache hit ratio measurement
before/after comparison
runtime observability report
```

No proof means no green status.

---

## 4. Performance Boundary Model

Every public or repeated execution boundary must define its performance boundary.

Performance boundary examples:

```text
HTTP endpoint
console command
worker job
queue consumer
event listener
database query path
cache access path
filesystem path
external API call
runtime boot
request handling
route matching
dependency resolution
view rendering
serialization
export
import
search
batch processing
AI provider call
```

A performance boundary answers:

```text
How often can this run?
What input size can it handle?
What is the latency budget?
What is the memory budget?
What I/O can it perform?
What can be cached?
What must not be cached?
What is the timeout?
What is the retry policy?
What happens under overload?
What metrics prove it is healthy?
```

A boundary without performance limits is incomplete.

---

## 5. Performance Claim Rule

A performance claim must be proven.

Forbidden claims without evidence:

```text
fast
optimized
lightweight
low-latency
high-throughput
scalable
production-grade
memory-safe
async-ready
Swoole-ready
RoadRunner-ready
FrankenPHP-ready
enterprise-grade performance
```

Required evidence for claims:

```text
claim
measurement method
environment
input size
result
baseline
regression threshold
date
command used
```

Example:

```md
## Performance Evidence

Claim:
Route matching is faster after compiled route table.

Command:
php tooling/benchmark/benchmark-route-matching.php

Baseline:
7.8 ms p95 for 1,000 routes.

Current:
1.2 ms p95 for 1,000 routes.

Decision:
Compiled route table accepted.

Regression threshold:
p95 must stay below 2.0 ms for 1,000 routes.
```

---

## 6. Latency Budget Rule

Every serious runtime path must have a latency budget.

Examples:

```text
HTTP request handling
route matching
container resolution
database query execution
cache read
queue job handling
event publishing
view rendering
API response serialization
```

A latency budget defines:

```text
target p50
target p95
target p99 where relevant
maximum acceptable timeout
measurement command
observability metric
regression threshold
```

### 6.1 Naming

Good:

```text
Capabilities/
  RouteLatencyBudget/
    RecordRouteLatency.php
    DetectRouteLatencyViolation.php

  QueryLatencyBudget/
    RecordQueryLatency.php
    DetectSlowQuery.php
```

Bad:

```text
Performance/
  LatencyManager.php
```

### 6.2 Latency Proof

Required tests or reports:

```text
hot path benchmark exists
slow path is observable
timeout is configured for external calls
latency regression threshold exists for critical path
```

---

## 7. Hot Path Rule

A hot path is code that runs often enough that overhead matters.

Examples:

```text
every HTTP request
every route match
every container resolution
every middleware step
every database query
every cache read
every queue message
every event dispatch
every validation pass on public endpoints
```

Hot path code must avoid:

```text
unnecessary reflection
unbounded filesystem scans
repeated config parsing
repeated container compilation
repeated route compilation
excessive allocations
unnecessary string parsing
hidden I/O
debug logging by default
expensive exception use for normal control flow
```

### 7.1 Hot Path Documentation

Every known hot path must document:

```text
why it is hot
what must stay cheap
what is allowed to allocate
what I/O is forbidden
what is cached or compiled
what benchmark proves it
```

### 7.2 Hot Path Naming

Good:

```text
Capabilities/
  RouteMatching/
    MatchCompiledRoute.php

  ContainerResolution/
    ResolveDependency.php

  RequestScope/
    OpenRequestScope.php
    CloseRequestScope.php
```

Bad:

```text
HotPath/
  Optimizer.php
```

---

## 8. Memory Budget Rule

Long-lived runtimes make memory growth dangerous.

Every worker-safe component must define memory behavior.

Memory-sensitive paths:

```text
HTTP worker
queue worker
scheduler
stream processor
large import
large export
view rendering
query hydration
event replay
projection rebuild
cache warming
file processing
```

A memory budget defines:

```text
expected memory per request/job
maximum memory per request/job
allowed retained state
reset behavior
large object lifetime
streaming behavior
leak detection
```

### 8.1 Long-Lived Runtime Memory Rule

Singletons must not retain request-specific data.

Static state must be bounded, immutable, resettable, or forbidden.

Caches inside long-lived workers must have:

```text
size limit
TTL
clear/reset behavior
memory pressure behavior
```

### 8.2 Memory Proof

Required evidence:

```text
worker can handle repeated requests without unbounded growth
request state resets after request
large imports use streaming or bounded batches
cache memory is bounded
```

---

## 9. Allocation Rule

Avoid excessive allocation in hot paths.

Do not create many short-lived objects inside tight loops unless there is a clear benefit.

Do not overuse value objects mechanically in hot paths when they add no meaning.

Do use value objects when they prevent bugs, clarify public API, or protect invariants.

Performance does not cancel domain clarity.

The rule is:

```text
semantic objects are good
decorative objects are expensive noise
```

### 9.1 Allocation Review Questions

Ask:

```text
Is this object meaningful?
Is it created in a hot loop?
Can it be reused safely?
Can it be immutable and shared?
Can this be compiled once?
Can this be normalized once at the boundary?
```

---

## 10. Algorithmic Complexity Rule

Every component must avoid accidental algorithmic blowups.

Watch for:

```text
nested loops over unbounded collections
repeated searches inside loops
N+1 database queries
N+1 HTTP calls
N+1 filesystem calls
sorting huge arrays repeatedly
loading entire dataset when streaming is possible
cartesian product behavior
recursive traversal without depth limit
```

### 10.1 Complexity Naming

If complexity matters, name the capability by what it protects.

Good:

```text
Capabilities/
  QueryCountProtection/
    DetectTooManyQueries.php

  BatchProcessing/
    ProcessItemsInBatches.php

  TreeTraversal/
    TraverseTreeWithDepthLimit.php
```

Bad:

```text
Performance/
  ComplexityHelper.php
```

### 10.2 Complexity Proof

Required tests or analysis:

```text
large input does not time out
batching is bounded
depth limit works
N+1 detection catches repeated query pattern
```

---

## 11. I/O Boundary Rule

I/O is expensive and failure-prone.

I/O includes:

```text
database
cache server
filesystem
network
message broker
external API
object storage
search index
AI provider
email provider
payment provider
CDN
```

Every I/O boundary must define:

```text
timeout
failure model
retry policy where useful
circuit breaker where useful
observability
resource budget
idempotency where side effects exist
```

### 11.1 No Accidental I/O

A function name must make I/O visible.

Good:

```text
ReadUserFromDatabase
WriteObjectToS3
PublishMessageToBroker
FetchExchangeRateFromProvider
```

Weak:

```text
getUser
load
process
handle
```

### 11.2 I/O Proof

Required evidence:

```text
timeout configured
failure path tested
retry does not duplicate side effects
metrics record latency and failure
```

---

## 12. Database Performance Rule

Database access is one of the most common performance failure points.

Every database-heavy flow must consider:

```text
query count
query latency
indexes
pagination
batch size
transaction length
lock duration
hydration cost
N+1 risk
connection pressure
result size
```

### 12.1 Query Count Rule

A flow must not perform unbounded queries.

If a flow loops and queries inside the loop, it must justify why.

Prefer:

```text
batch queries
joins where appropriate
explicit eager loading
read models
projections
pagination
```

### 12.2 Pagination Rule

Endpoints returning collections must use bounded pagination or streaming.

No endpoint may return unbounded rows by default.

Required:

```text
default limit
maximum limit
stable ordering
safe cursor or offset strategy
resource budget
```

### 12.3 Index Awareness Rule

A query that is expected to run in production must have an index strategy when data can grow.

The code does not need to own the index, but the design must know it exists.

### 12.4 Transaction Performance Rule

Transactions must be as short as practical.

Do not perform slow external I/O inside a database transaction unless explicitly justified.

Examples to avoid inside transaction:

```text
HTTP call
email send
message broker publish without outbox
large file operation
AI provider call
sleep/retry loop
```

### 12.5 Database Observability

Record:

```text
query latency
query count per request/job
slow queries
failed queries
connection acquisition failure
transaction duration where useful
```

### 12.6 Database Proof

Required tests or reports:

```text
N+1 protection for known risk
pagination limit enforced
query bindings are used
large result path is bounded
transaction does not wrap slow external I/O unless justified
```

---

## 13. Cache Performance Rule

Cache is not a magic speed button.

Cache must have a reason, scope, invalidation rule, and measurement.

Every cache use must define:

```text
what is cached
why it is cached
cache key
scope
TTL
invalidation rule
stampede protection if expensive
stale behavior if allowed
sensitive data rule
hit/miss metrics
```

### 13.1 Cache Key Rule

Cache keys must be:

```text
stable
scoped
versioned where needed
safe
not secret-bearing
```

Bad:

```text
user:<raw-token>
```

Good:

```text
user-profile:<tenant-id>:<user-id>:v1
```

### 13.2 Cache Stampede Rule

If a cached value is expensive and many callers may request it together, stampede protection is required.

Options:

```text
lock
single flight
stale-while-revalidate
background refresh
pre-warming
```

### 13.3 Cache Invalidation Rule

Every cache must answer:

```text
When does this become stale?
Who invalidates it?
What happens if invalidation fails?
Can stale data be served?
For how long?
```

### 13.4 Cache Proof

Required evidence:

```text
hit path works
miss path works
invalidation works
sensitive scope is preserved
stampede risk is controlled where relevant
hit/miss metrics exist
```

---

## 14. Compilation and Warmup Rule

Compile expensive runtime metadata before hot path execution when possible.

Good candidates:

```text
routes
container definitions
configuration
validation metadata
serialization metadata
public API schema
view templates
database metadata
event/message schema
```

Compilation must be safe.

Compiled artifacts must define:

```text
source inputs
cache key or version
invalidated when source changes
atomic write behavior
corruption detection
fallback behavior
warmup command
doctor check
```

### 14.1 Naming

Good:

```text
Capabilities/
  RouteCompilation/
    CompileRoutes.php
    ReadCompiledRoutes.php

  ContainerCompilation/
    CompileContainer.php
    ReadCompiledContainer.php

  MetadataWarmup/
    WarmEntityMetadata.php
```

Bad:

```text
Optimizers/
  OptimizeEverything.php
```

### 14.2 Compilation Proof

Required tests:

```text
compiled artifact is created
compiled artifact is read
source change invalidates compiled artifact
corrupt compiled artifact is rejected
fallback behavior is safe
```

---

## 15. Reflection and Metadata Rule

Reflection is useful.

Reflection in hot paths is expensive.

Use reflection during:

```text
boot
compile
warmup
development tooling
metadata build
```

Avoid repeated reflection during:

```text
every request
every route match
every container resolution
every entity hydration
every validation call
every serialization call
```

If repeated reflection is necessary, cache or compile the result.

---

## 16. Serialization Performance Rule

Serialization can dominate runtime cost.

Every serialization-heavy path must define:

```text
data shape
depth limit
circular reference behavior
field selection
sensitive field rule
format
streaming behavior for large output
```

Avoid:

```text
serializing entire domain objects accidentally
serializing lazy relations accidentally
serializing internal runtime state
serializing secrets
```

Good:

```text
Capabilities/
  UserProfileSerialization/
    SerializeUserProfile.php
    UserProfileView.php
```

Bad:

```text
return json_encode($domainObject)
```

---

## 17. HTTP Performance Rule

Every HTTP path must be bounded and observable.

Endpoint performance must define:

```text
request body limit
validation cost
authorization cost
query count
external call count
response size
pagination limit
cache behavior
timeout behavior
rate limit where needed
```

### 17.1 Route Matching

Route matching is hot path behavior.

Routes should be compiled or otherwise optimized when route count grows.

### 17.2 Middleware

Middleware runs often.

Middleware must be:

```text
cheap
ordered deliberately
free of hidden I/O unless explicit
observable when expensive
free of request state leakage
```

### 17.3 Response Size

Large responses must be paginated, streamed, compressed, or explicitly justified.

---

## 18. Queue and Worker Performance Rule

Workers must be memory-safe, retry-safe, and throughput-aware.

Worker jobs must define:

```text
timeout
max attempts
backoff
idempotency
batch size
memory budget
visibility timeout where relevant
dead-letter behavior
metrics
```

### 18.1 Worker Memory

Long-running workers must reset state between jobs.

Track:

```text
job duration
memory before
memory after
memory peak
failure count
retry count
```

### 18.2 Queue Backpressure

If queue depth grows, the system must react.

Possible actions:

```text
scale workers
delay producers
reject low-priority work
shed load
open circuit for downstream dependency
alert operator
```

Silent backlog growth is not acceptable.

---

## 19. Event and Message Performance Rule

Events and messages must not create uncontrolled fanout.

Every event/message path must define:

```text
number of consumers
expected volume
payload size
serialization cost
delivery guarantee
retry behavior
dead-letter behavior
observability
```

### 19.1 Fanout Rule

Fanout must be intentional.

If one event triggers many consumers, measure and observe it.

### 19.2 Payload Size Rule

Messages should contain enough information to be useful, but not unnecessary large payloads.

Avoid:

```text
full object graphs
large binary payloads
secrets
unbounded arrays
```

---

## 20. External Service Performance Rule

External services are slow and unreliable compared to local code.

Every external call must define:

```text
timeout
retry policy
circuit breaker when useful
bulkhead when useful
fallback when useful
rate limit
observability
idempotency for side effects
```

### 20.1 No External I/O in Hot Path Without Budget

If an HTTP request needs external I/O, it must have a latency budget.

If latency is not acceptable, use:

```text
queue
cache
pre-fetch
background sync
read model
stale data policy
```

### 20.2 AI Provider Calls

AI provider calls are expensive and variable.

They require:

```text
timeout
token budget
cost budget
retry policy
fallback behavior
observability
redaction
rate limit
```

Do not hide AI calls behind local-looking functions.

---

## 21. Filesystem Performance Rule

Filesystem can be fast locally and slow in production.

Every filesystem-heavy path must define:

```text
file count
file size
directory traversal depth
streaming behavior
atomic write behavior
lock behavior
cleanup behavior
```

Avoid:

```text
scanning whole directories on every request
reading large files into memory
writing non-atomic cache files
unbounded temp files
```

Good:

```text
Capabilities/
  AtomicFileWriting/
    WriteFileAtomically.php

  DirectoryScanning/
    ScanDirectoryWithLimit.php
```

---

## 22. Logging and Telemetry Cost Rule

Observability is required, but it has cost.

Logging and telemetry must be:

```text
structured
bounded
sampled when high-volume
redacted
cheap on hot paths
async where useful
```

Avoid:

```text
building expensive log context when log level is disabled
logging full payloads
logging every hot-path event without sampling
synchronous remote telemetry on request path without timeout
```

### 22.1 Metrics

Prefer counters, histograms, and gauges over unstructured logs for high-volume performance signals.

Record:

```text
latency
duration
count
size
memory
queue depth
cache hit ratio
failure count
retry count
```

---

## 23. Rate Limiting and Abuse Performance Rule

Abuse is a performance problem and a security problem.

Rate limit or resource-limit:

```text
login
password reset
registration
webhook endpoints
search
export
import
file upload
AI endpoints
expensive reports
admin actions
queue-producing endpoints
```

Rate limits must be scoped by an appropriate key:

```text
user
tenant
IP
API key
route
resource
```

Rate limiting must be observable.

---

## 24. Backpressure and Load Shedding Rule

When the system is overloaded, it must fail deliberately.

Backpressure means slowing or rejecting work because downstream capacity is limited.

Load shedding means dropping or rejecting lower-priority work to protect critical work.

Required for:

```text
queue depth overflow
worker saturation
external dependency outage
database pressure
memory pressure
AI provider quota pressure
```

Good naming:

```text
Capabilities/
  QueueCapacity/
    RejectWorkWhenQueueIsFull.php

  WorkerCapacity/
    DelayWorkWhenWorkersAreBusy.php
```

Bad:

```text
PerformanceManager
```

---

## 25. Concurrency Rule

Concurrency can improve throughput and create bugs.

Use concurrency only when:

```text
work is independent
shared state is controlled
idempotency is clear
failure behavior is clear
ordering requirements are known
resource limits are enforced
```

Concurrency must not create:

```text
race conditions
duplicate side effects
lost updates
unbounded parallelism
connection exhaustion
memory spikes
```

### 25.1 Locks and Leases

Use locks or leases when a shared resource must be protected.

Locks must define:

```text
owner
timeout
expiry
release behavior
failure behavior
deadlock prevention
observability
```

Locks without timeout are dangerous.

---

## 26. Streaming Rule

Use streaming when data can be large.

Candidates:

```text
large file read
large file upload
large export
large import
large query result
large response body
event replay
projection rebuild
```

Streaming must define:

```text
chunk size
memory budget
error handling
partial result behavior
timeout
backpressure
```

Avoid loading unbounded data into memory.

---

## 27. Batch Processing Rule

Batch processing must be bounded, resumable where needed, and observable.

Batch flows must define:

```text
batch size
cursor or offset strategy
retry behavior
idempotency
partial failure behavior
progress reporting
memory budget
time budget
```

Good:

```text
Flows/
  ImportUsers/
    ImportUsers.php
    ReadNextImportBatch.php
    RecordImportProgress.php
```

Bad:

```text
ImportProcessor
```

---

## 28. Runtime Boot Performance Rule

Runtime boot must be measured.

Boot performance matters for:

```text
PHP-FPM request startup
console commands
worker startup
test startup
serverless style runtimes
local developer feedback
```

Long-lived workers can pay boot cost once, but boot must still be bounded.

Boot should avoid:

```text
scanning huge trees repeatedly
eager-loading unnecessary components
connecting to external services unnecessarily
compiling at request time
```

Use:

```text
lazy initialization
explicit warmup
compiled manifests
configuration cache
runtime doctor checks
```

---

## 29. Container Performance Rule

Container resolution can be hot.

Container must avoid:

```text
repeated reflection
unbounded autowiring search
hidden filesystem scans
resolving heavy services eagerly
request-scoped services in singletons
```

Container performance should support:

```text
compiled definitions
singleton where safe
scoped services where needed
resettable state
resolution metrics
diagnostics for slow resolution
```

---

## 30. Routing Performance Rule

Routing is hot path behavior.

Routing must define:

```text
route count behavior
matching algorithm
compiled route table when needed
method matching
dynamic parameter extraction
fallback behavior
405 behavior where applicable
```

Routing proof should include:

```text
many routes benchmark
dynamic route benchmark
not found route benchmark
method mismatch benchmark
```

---

## 31. View Rendering Performance Rule

View rendering must avoid:

```text
recompiling templates on every request in production
unbounded partial nesting
hidden database calls from templates
rendering raw domain objects
loading large collections without pagination
```

Use:

```text
compiled templates
view models
bounded loops
explicit data preparation
escaping by default
```

Templates must not become query engines.

---

## 32. Configuration Performance Rule

Configuration must not be parsed repeatedly in hot paths.

Configuration should be:

```text
loaded once
validated once
normalized once
compiled or cached where useful
redacted when dumped
reset-safe in long-lived runtimes
```

Avoid:

```text
reading .env on every request
parsing many config files repeatedly
doing filesystem scans in every container resolution
```

---

## 33. Developer Experience Performance Rule

Developer tooling must be fast enough to be used.

Governance checks, tests, static analysis, and recovery tools should be:

```text
incremental where possible
cache-aware
targetable by component
clear in output
safe to run locally
```

Slow tooling gets skipped.

Skipped tooling means broken governance.

---

## 34. AI-Assisted Development Performance Rule

AI-generated changes must not add slow patterns accidentally.

Reject AI code that:

```text
adds repeated filesystem scans
adds hidden I/O
adds unbounded loops
adds N+1 queries
adds eager loading of large trees
adds global reflection on hot paths
adds synchronous remote calls without timeout
adds excessive logging
adds broad catch and retry loops
adds sleep in request path
```

AI must provide proof for performance claims.

---

## 35. Naming Rules for Performance Code

Performance names must be exact.

Good:

```text
CompileRoutes
WarmRouteCache
DetectSlowQuery
RecordQueryLatency
DetectWorkerMemoryGrowth
LimitExportBatchSize
RejectWorkWhenQueueIsFull
ReadCompiledContainer
MeasureRouteMatching
```

Weak:

```text
PerformanceService
OptimizationManager
SpeedHelper
FastProcessor
TuningService
```

Use what the code protects or improves.

Not how important it sounds.

---

## 36. Performance Folder Placement

Performance logic belongs where the performance pressure lives.

Examples:

```text
Capabilities/
  QueryBudget/
    RecordQueryCount.php
    DetectTooManyQueries.php

Capabilities/
  RouteCompilation/
    CompileRoutes.php
    ReadCompiledRoutes.php

Capabilities/
  CacheWarming/
    WarmCache.php
    WarmCompiledCache.php

Flows/
  ExportOrders/
    ExportOrders.php
    StreamOrderExport.php
    LimitOrderExportBatch.php
```

Avoid:

```text
Performance/
  Helpers/
  Services/
  Managers/
```

A performance capability may exist when the protection or optimization is reusable.

A performance flow may exist when the optimization is part of one end-to-end behavior.

---

## 37. Benchmark Rule

Benchmarks must be honest and repeatable.

A benchmark must include:

```text
command
environment
PHP version
runtime mode
input size
warmup rule
number of iterations
result
baseline
regression threshold
date
```

### 37.1 Benchmark Naming

Good:

```text
tooling/benchmark/benchmark-route-matching.php
tooling/benchmark/benchmark-container-resolution.php
tooling/benchmark/benchmark-query-builder.php
```

Bad:

```text
fast-test.php
speed.php
```

### 37.2 Benchmark Review

A benchmark is weak if:

```text
input size is unrealistic
warmup is missing
baseline is missing
only one run is measured
output cannot be compared later
environment is unknown
```

---

## 38. Profiling Rule

Use profiling when the bottleneck is unclear.

Profiling should answer:

```text
where time is spent
where memory is allocated
which calls repeat too often
which I/O dominates
which hot path is unexpectedly expensive
```

Do not guess when profiling is available.

Record profiling summaries in performance reports.

---

## 39. Performance Regression Rule

Performance-sensitive paths must have regression protection.

Regression protection may be:

```text
benchmark threshold
architecture test
query count assertion
memory growth assertion
latency budget alert
load test
runtime metric alert
```

Examples:

```text
route matching p95 must stay below threshold
container resolution count must not grow unexpectedly
endpoint must not exceed maximum query count
worker memory must not grow unbounded after N jobs
```

---

## 40. Performance Static Analysis Rule

Static checks should catch repeatable performance mistakes.

Examples:

```text
raw filesystem scan in request path
query inside loop
HTTP call inside database transaction
sleep in request path
unbounded collection response
debug logging in hot path
reflection in route match path
PublicSurface doing heavy work
```

If a rule can be automated, automate it.

---

## 41. Performance Review Checklist

A performance review passes only if:

```text
[ ] performance-sensitive boundaries are identified
[ ] hot paths are known
[ ] hidden I/O is absent or explicit
[ ] public endpoints have resource limits
[ ] database query count is bounded
[ ] collections are paginated, streamed, or bounded
[ ] cache has invalidation and metrics
[ ] external calls have timeout
[ ] retries are bounded and idempotent
[ ] long-lived runtime state is reset or bounded
[ ] memory growth risk is addressed
[ ] telemetry cost is bounded
[ ] benchmark or measurement exists for performance claims
[ ] regression threshold exists for critical paths
[ ] security and correctness were not weakened
```

If one item is missing in a performance-critical path, the design is not performance-green.

---

## 42. Performance Proof Matrix

| Area          | Required Proof                                            |
|---------------|-----------------------------------------------------------|
| PublicSurface | delegates heavy work and does not hide I/O                |
| HTTP endpoint | bounded input, pagination, query count awareness          |
| Database      | query count bounded, slow query visible, N+1 controlled   |
| Cache         | hit/miss/invalidation tested, stampede risk addressed     |
| Queue         | timeout, retry, memory, idempotency, dead-letter behavior |
| Worker        | repeated jobs do not leak memory                          |
| Routing       | route matching benchmark when route count grows           |
| Container     | resolution benchmark or compiled definitions for hot path |
| External I/O  | timeout, retry, circuit behavior where needed             |
| Serialization | large payload behavior bounded                            |
| Filesystem    | path traversal aside, file size and streaming bounded     |
| Batch         | batch size, progress, retry, partial failure behavior     |
| Events        | fanout and payload size controlled                        |
| Observability | telemetry cost bounded and high-volume signals sampled    |
| Runtime       | boot and request lifecycle measured where relevant        |

---

## 43. Performance Classification

### GREEN

```text
Performance boundary is explicit.
Hot paths are known.
Resource use is bounded.
Evidence is current.
Regression protection exists where needed.
Security and correctness are preserved.
```

### YELLOW

```text
Performance design is plausible.
Some limits exist.
Proof is incomplete.
May remain during active repair or design.
```

### RED

```text
Performance boundary is unclear.
Hidden I/O exists.
Resource use is unbounded.
Performance claim has no evidence.
Memory growth risk is unmeasured.
```

### BLOCKER

```text
unbounded public endpoint
query inside unbounded loop on production path
external call without timeout
retry without limit
worker retains request/user/tenant state
cache leaks cross-user or cross-tenant data
large export loads all rows into memory
performance optimization bypasses security
```

Blockers must be fixed before promotion.

---

## 44. V1 / V2 / V3 Performance Discipline

### 44.1 V1 Performance Minimum

V1 must prove:

```text
autoload is clean
runtime boot is bounded
PublicSurface does not do heavy hidden work
request state does not leak in long-lived runtime model
database query execution is observable
cache behavior is bounded where used
HTTP kernel has bounded request handling
doctor checks can report performance-sensitive risks
```

### 44.2 V2 Performance Expansion

V2 must prove:

```text
API endpoints have resource limits
integration components have timeouts and reliability policy
queue workers are memory-safe
scheduler is bounded
observability cost is controlled
cache warming and compiled artifacts work where useful
delivery pipeline records performance evidence
```

### 44.3 V3 Performance Expansion

V3 must validate system-design performance risks:

```text
capacity model
load model
latency budget
throughput model
queue depth behavior
projection lag
cache hit ratio
failure simulation
backpressure
load shedding
reference architecture benchmarks
```

V2 and V3 performance implementation remains locked until active stage allows it.

Planning may continue.

---

## 45. Existing Code Recovery Rule

When recovering code from `avax-backup.txt`, `Framework.txt`, `Components.txt`, or git history, performance must be
revalidated.

Old code is not automatically performant.

Recovered code must be checked for:

```text
hidden I/O
unbounded loops
N+1 queries
unbounded memory use
large eager loads
request-path filesystem scans
external calls without timeout
retry without limit
state retained in long-lived workers
debug logging in hot paths
```

Old behavior is evidence.

Current governance is the target.

Performance tests are the judge.

---

## 47. Security and Performance Trigger Cross-Rule

Performance review MUST be triggered by changes to: hot paths, loops over routes/listeners/middleware, reflection,
container resolution, event dispatch, queue workers, database query execution, route matching, filesystem scans, cache
compile/read/write, boot/worker startup, long-lived runtime reset, serialization, hydration/extraction, projections/read
models, graph compilation, or runtime scope creation/closing.

If triggered, review evidence must include why performance is relevant, what was checked, result, remaining risk, and
blocking decision. If not triggered, review must say why.

For security triggers, see:

```text
.agents/how-to/how-to-system-security.md §44
```

---

## 48. Critical Quality Signal Rule

### Status

**MANDATORY**
**Severity:** HIGH (escalates to BLOCKER — see below)

### Severity Escalation

Default severity is HIGH.

Escalates to **BLOCKER** when the issue threatens:

```text
security
data integrity
runtime safety
long-lived worker safety
truth/evidence integrity
public API compatibility
dependency graph correctness
rollback/recovery safety
```

Examples that MUST be BLOCKER when active:

```text
exploitable security issue
data corruption risk
request state stored in singleton
unresolved runtime composition leak in active runtime
fake GREEN
gate PASS with RED content
mandatory gate scans zero active files
hidden fallback dependency in runtime
missing required dependency discovered in business/runtime code
service locator in business/runtime code
```

### Rule

The review MUST loudly flag anything that threatens security, data integrity, runtime safety, long-lived worker safety,
dependency graph correctness, public API compatibility, static analysis baseline, test reliability, performance hot
paths, observability of failures, rollback/recovery safety, container verification, request scope isolation, tenant
isolation, state reset safety, or failure boundary correctness.

The following must not pass silently:

```text
hidden fallback construction
runtime service assembly
service locator usage in business code
missing dependency checks in business or runtime code
mutable static state without reset proof
request state stored in singleton
fake ServiceProvider
fake PublicSurface
broad try/catch swallowing errors
broad PHPStan ignores
weak tests
assertTrue(true)
evidence claiming GREEN while validation says otherwise
gate PASS with RED content
mandatory gate with zero scanned files
fake compatibility shim
duplicate canonical concepts
large builders acting as hidden containers
examples showing non-canonical style
```

If it can create a security hole, corrupt data, hide a runtime failure, break long-lived workers, or fake correctness,
it must scream in review.

---

## 46. Final Performance Law

Performance is not decoration.

Performance is not a label.

Performance is not micro-optimization.

Performance is bounded work, visible cost, controlled memory, explicit I/O, measured latency, safe caching, predictable
runtime behavior, and evidence.

Fast without proof is a guess.

Fast without correctness is wrong.

Fast without security is dangerous.

Fast without observability is fragile.

AvaX must measure before optimizing, bound every public operation, expose performance truth, and protect long-lived
runtimes from memory and state leaks.

If performance makes the system clearer, safer, and more predictable, it belongs.

If performance hides behavior behind vague optimizers, it failed.
