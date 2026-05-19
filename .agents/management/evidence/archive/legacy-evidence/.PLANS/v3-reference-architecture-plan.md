# V3 Reference Architecture Plan

Version: 1.0.0
Date: 2026-05-07
Stage: V3-01 — Reference Architecture Schema + Capacity Engine
Depends on: V1 Kernel Green (PROVEN), V2 Platform Baseline (CLOSED/GREEN), V3-00 Labs Foundation (COMPLETE)
Status: PLANNING

---

## 0. Executive Summary

This plan defines the reference architecture schema, canonical vocabulary, ownership boundaries, and phased
implementation order for AvaX V3 — the executable system-design validation framework.

V3 does not build applications. V3 tests whether the architecture makes sense.

All V3 work starts in `labs/SystemDesignKit/` and promotes to `components/SystemDesign/` only after proof.

---

## 1. Terminology Reconciliation

This section aligns terms from the V3 master plan with AvaX canonical vocabulary.

| V3 Master Plan Term     | AvaX Canonical Translation                         | Reason                                                                                                                            |
|-------------------------|----------------------------------------------------|-----------------------------------------------------------------------------------------------------------------------------------|
| Capacity                | Capacity (component name — capability, not action) | Acceptable as a component name because it names a platform plane, not a technical category                                        |
| LoadModel               | LoadModel (component name)                         | Acceptable — names the capability of modeling workload shape                                                                      |
| LatencyBudget           | LatencyBudget (component name)                     | Acceptable — names the capability of budgeting latency                                                                            |
| Availability            | Availability (component name)                      | Acceptable — names the capability of modeling availability                                                                        |
| Consistency             | Consistency (component name)                       | Acceptable — names the capability of modeling consistency guarantees                                                              |
| Partitioning            | Partitioning (component name)                      | Acceptable — names the capability of modeling data partitioning                                                                   |
| Replication             | Replication (component name)                       | Acceptable — names the capability of modeling replication                                                                         |
| Sharding                | Sharding (component name)                          | Acceptable — names the capability of modeling sharding                                                                            |
| Messaging               | Messaging (component name)                         | Acceptable — names the capability of async communication                                                                          |
| Projections             | Projections (component name)                       | Acceptable — names the capability of CQRS read models                                                                             |
| Caching                 | Caching (component name)                           | Acceptable — names the capability of cache strategy modeling                                                                      |
| Failure                 | Failure (component name)                           | Acceptable — names the capability of failure modeling                                                                             |
| Simulation              | Simulation (component name)                        | Acceptable — names the capability of running architecture simulations                                                             |
| ArchitectureTests       | ArchitectureTests (component name)                 | Acceptable — names the capability of executable architecture assertions                                                           |
| ReferenceArchitecture   | ReferenceArchitecture (component name)             | Acceptable — names the capability of reading/validating reference architectures                                                   |
| ScenarioRunner          | ScenarioRunner (component name)                    | Acceptable — names the capability of running named scenarios                                                                      |
| TradeoffReport          | TradeoffReport (component name)                    | Acceptable — names the capability of explaining tradeoffs                                                                         |
| InfrastructureBoundary  | InfrastructureBoundary (component name)            | Acceptable — names the capability of modeling external boundaries                                                                 |
| ApiSurface              | ApiSurfaceModel (component rename)                 | "ApiSurface" was renamed to "ApiBlueprint" in V2; V3 uses it differently (architecture-level modeling, not runtime API contracts) |
| capacity.yaml           | capacity.yaml (filename — OK)                      | Filename, not folder — acceptable                                                                                                 |
| scenarios.yaml          | scenarios.yaml (filename — OK)                     | Filename, not folder — acceptable                                                                                                 |
| architecture-tests.yaml | architecture-tests.yaml (filename — OK)            | Filename, not folder — acceptable                                                                                                 |
| Builder                 | Avoid as class name                                | Forbidden by AvaX naming law — use Flow/Capability names instead                                                                  |
| Processor               | Avoid as class name                                | Forbidden by AvaX naming law — use Flow/Capability names instead                                                                  |
| Manager                 | Avoid as class name                                | Forbidden by AvaX naming law — use Flow/Capability names instead                                                                  |
| Service                 | Avoid as class name                                | Forbidden by AvaX naming law — use Flow/Capability names instead                                                                  |
| Helper                  | Avoid as class name                                | Forbidden by AvaX naming law — use Flow/Capability names instead                                                                  |
| Utils                   | Avoid as class/folder name                         | Forbidden by AvaX naming law                                                                                                      |
| UseCase                 | Translate to Flow                                  | AvaX uses Flow, not UseCase                                                                                                       |
| Handler                 | Translate to Flow or Capability name               | Forbidden as default folder/class name — use action names                                                                         |
| Adapter                 | Translate to concrete boundary name                | Concept word, not folder name — use S3ObjectStorage, RedisCacheStore, etc.                                                        |
| Contract                | Translate to PublicApiCompatibility or schema name | Concept word, not folder name                                                                                                     |

### Key Translation Rule

Component names like Capacity, Messaging, Consistency are acceptable because they name **platform capabilities** at the
component level — not technical dumping grounds. Inside each component, the canonical shape applies:

```text
System/
  PublicSurface/    — receives
  Flows/            — executes end-to-end behavior
  Capabilities/     — powers reusable behavior
  Configuration/    — assembles
  Foundation/       — supports with tiny primitives
```

Inside Capabilities/, folders must say the real ability, not the pattern:

- Good: `Traffic/`, `Storage/`, `Cache/`, `Queue/`, `Latency/`, `Availability/`
- Bad: `Services/`, `Managers/`, `Helpers/`, `Adapters/`, `Contracts/`

---

## 2. Capability Map — V3 Platform Planes

V3 maps to the **System-Design Validation Plane** (Plane 8) in the AvaX platform plane model.

```text
Plane 1  Runtime            — Boot, lifecycle, request scope, worker lifecycle
Plane 2  Control            — Health, readiness, liveness, diagnostics
Plane 3  Contract           — API compatibility, schema, versioning
Plane 4  Integration        — External infrastructure boundaries
Plane 5  Reliability        — Retry, timeout, circuit breaker, outbox, inbox
Plane 6  Observability      — Logs, metrics, traces, audit, redaction
Plane 7  Delivery           — Build, compile, smoke checks, rollback
Plane 8  System-Design      — V3: capacity, consistency, messaging, failure, simulation
```

### V3 Component-to-Plane Mapping

| V3 Component           | Primary Plane | Supports Planes                              |
|------------------------|---------------|----------------------------------------------|
| Capacity               | Plane 8       | Plane 2 (control), Plane 7 (delivery)        |
| LoadModel              | Plane 8       | Plane 1 (runtime), Plane 5 (reliability)     |
| LatencyBudget          | Plane 8       | Plane 2 (control), Plane 6 (observability)   |
| Availability           | Plane 8       | Plane 5 (reliability), Plane 2 (control)     |
| Consistency            | Plane 8       | Plane 4 (integration), Plane 5 (reliability) |
| Partitioning           | Plane 8       | Plane 4 (integration)                        |
| Replication            | Plane 8       | Plane 4 (integration), Plane 5 (reliability) |
| Sharding               | Plane 8       | Plane 4 (integration)                        |
| Messaging              | Plane 8       | Plane 4 (integration), Plane 5 (reliability) |
| Projections            | Plane 8       | Plane 3 (contract), Plane 6 (observability)  |
| Caching                | Plane 8       | Plane 5 (reliability), Plane 1 (runtime)     |
| Failure                | Plane 8       | Plane 5 (reliability)                        |
| Simulation             | Plane 8       | Plane 7 (delivery), Plane 2 (control)        |
| ArchitectureTests      | Plane 8       | Plane 7 (delivery), Plane 3 (contract)       |
| ReferenceArchitecture  | Plane 8       | Plane 7 (delivery)                           |
| ScenarioRunner         | Plane 8       | Plane 7 (delivery), Plane 2 (control)        |
| TradeoffReport         | Plane 8       | Plane 7 (delivery), Plane 2 (control)        |
| InfrastructureBoundary | Plane 8       | Plane 4 (integration)                        |
| ApiSurfaceModel        | Plane 8       | Plane 3 (contract)                           |

### V3 Dependencies on Other Planes

V3 depends on:

- Plane 1 (Runtime): for simulation execution context
- Plane 2 (Control): for health/readiness checks during simulations
- Plane 3 (Contract): for API surface modeling
- Plane 4 (Integration): for infrastructure boundary modeling
- Plane 5 (Reliability): for failure scenario policies (retry, timeout, circuit breaker)
- Plane 6 (Observability): for simulation logging and trace output
- Plane 7 (Delivery): for architecture test execution and reporting

V3 must not re-implement Plane 5 reliability primitives. V3 models them at architecture level. The actual
retry/timeout/circuit-breaker implementations belong in `components/Operations/Resilience/`.

---

## 3. Ownership Boundaries

### 3.1 What V3 Owns

```text
V3 owns:
- Architecture modeling (capacity, load, latency, consistency, etc.)
- Scenario definition and execution
- Architecture test assertions
- Reference architecture validation
- Tradeoff analysis and reporting
- Failure scenario simulation
- Infrastructure boundary modeling (at architecture level, not runtime)
- API surface modeling (at architecture level, not runtime contracts)
```

### 3.2 What V3 Does NOT Own

```text
V3 does NOT own:
- Runtime HTTP handling (Plane 1 — framework/System)
- Runtime cache reading/writing (V2 — Application/Cache)
- Runtime database queries (V2 — DataStack/Database)
- Runtime queue dispatch (V2 — Operations/Queue)
- Runtime event dispatch (V2 — Operations/MessageBus)
- Runtime retry/timeout execution (V2 — Operations/Resilience)
- Runtime telemetry export (V2 — Operations/Observability)
- Runtime object storage operations (V2 — Integration/ObjectStorage)
- Custom Kafka, Redis Cluster, RocksDB implementations (hard boundary)
- Custom Raft/Paxos consensus (hard boundary)
- Custom distributed databases (hard boundary)
```

### 3.3 Boundary Rule

V3 models. V2/V1 execute.

Example:

- V3 Capacity models whether a system can handle 100k RPS with 98% cache hit ratio.
- V2 Application/Cache actually reads/writes cache at runtime.
- V3 models the tradeoff. V2 executes the behavior.

Example:

- V3 Failure models what happens when Redis is down.
- V2 Operations/Resilience actually retries with circuit breaker.
- V3 asserts the architecture has a retry policy. V2 executes the retry.

---

## 4. Canonical Vocabulary

### 4.1 V3 Value Objects and Models

These are tiny, neutral primitives. They belong in Foundation/ or as PublicSurface models.

| Canonical Name        | Location                            | Purpose                   |
|-----------------------|-------------------------------------|---------------------------|
| RequestsPerSecond     | Capacity/Capabilities/Traffic/      | Traffic rate model        |
| ReadsPerSecond        | Capacity/Capabilities/Traffic/      | Read traffic rate         |
| WritesPerSecond       | Capacity/Capabilities/Traffic/      | Write traffic rate        |
| ReadWriteRatio        | Capacity/Capabilities/Traffic/      | Read/write ratio          |
| PeakTrafficMultiplier | Capacity/Capabilities/Traffic/      | Peak traffic multiplier   |
| BurstWindow           | Capacity/Capabilities/Traffic/      | Burst traffic time window |
| FanoutSize            | Capacity/Capabilities/Traffic/      | Fanout size model         |
| CacheHitRatio         | Capacity/Capabilities/Cache/        | Cache hit ratio target    |
| CacheMissPenalty      | Capacity/Capabilities/Cache/        | Cache miss penalty        |
| CacheStampedeRisk     | Capacity/Capabilities/Cache/        | Cache stampede risk model |
| QueueDepth            | Capacity/Capabilities/Queue/        | Queue depth model         |
| QueueDelayBudget      | Capacity/Capabilities/Queue/        | Queue delay budget        |
| ConsumerThroughput    | Capacity/Capabilities/Queue/        | Consumer throughput       |
| LatencyBudget         | Capacity/Capabilities/Latency/      | Latency budget model      |
| P50Latency            | Capacity/Capabilities/Latency/      | P50 latency value         |
| P95Latency            | Capacity/Capabilities/Latency/      | P95 latency value         |
| P99Latency            | Capacity/Capabilities/Latency/      | P99 latency value         |
| Slo                   | Capacity/Capabilities/Availability/ | Service level objective   |
| Sla                   | Capacity/Capabilities/Availability/ | Service level agreement   |
| FailureBudget         | Capacity/Capabilities/Availability/ | Failure budget            |
| ErrorBudgetBurnRate   | Capacity/Capabilities/Availability/ | Error budget burn rate    |

### 4.2 V3 Flow Names

Flow names are actions (verb-noun or action phrase).

| Canonical Flow Name           | Purpose                                          |
|-------------------------------|--------------------------------------------------|
| ValidateCapacityModel         | Validates capacity.yaml against schema and rules |
| EstimateTrafficLoad           | Calculates read/write/fanout/peak/burst traffic  |
| EstimateStorageGrowth         | Estimates storage growth over retention window   |
| EstimateQueuePressure         | Estimates queue depth and consumer throughput    |
| EstimateCacheEffectiveness    | Estimates expected cache hit/miss behavior       |
| EstimateLatencyBudget         | Splits latency budget across layers              |
| EstimateFailureBudget         | Converts SLO into allowed downtime/error budget  |
| ValidateLoadModel             | Validates load model against schema              |
| SimulateLoadProfile           | Simulates a load profile                         |
| ValidateLatencyBudget         | Validates latency budget model                   |
| AllocateLatencyBudget         | Allocates latency budget across layers           |
| ValidateAvailabilityTarget    | Validates availability target                    |
| EvaluateDegradationPlan       | Evaluates graceful degradation plan              |
| ValidateConsistencyModel      | Validates consistency model                      |
| ExplainConsistencyTradeoff    | Explains consistency tradeoffs                   |
| DetectConsistencyRisk         | Detects consistency risks                        |
| ValidatePartitioningModel     | Validates partitioning model                     |
| DetectPartitioningRisk        | Detects partitioning risks                       |
| ValidateReplicationModel      | Validates replication model                      |
| EstimateReplicationLag        | Estimates replication lag                        |
| ValidateShardingModel         | Validates sharding model                         |
| RouteToShard                  | Routes to a shard                                |
| EstimateShardRebalance        | Estimates shard rebalance cost                   |
| ValidateMessagingModel        | Validates messaging model                        |
| DetectMessagingRisk           | Detects messaging risks                          |
| ValidateProjectionModel       | Validates projection model                       |
| DetectProjectionRisk          | Detects projection risks                         |
| ValidateCacheStrategy         | Validates cache strategy                         |
| DetectCacheRisk               | Detects cache risks                              |
| ValidateFailureModel          | Validates failure model                          |
| RunFailureAnalysis            | Runs failure analysis                            |
| RunArchitectureSimulation     | Runs architecture simulation                     |
| RunFailureScenario            | Runs a failure scenario                          |
| RunLoadSimulation             | Runs a load simulation                           |
| RunConsistencySimulation      | Runs a consistency simulation                    |
| RunArchitectureTests          | Runs architecture tests                          |
| ValidateReferenceArchitecture | Validates a reference architecture               |
| ReadReferenceArchitecture     | Reads a reference architecture                   |
| GenerateTradeoffReport        | Generates a tradeoff report                      |
| RunScenario                   | Runs a named scenario                            |
| ListScenarios                 | Lists available scenarios                        |

### 4.3 V3 Capability Names

Capability names are abilities (noun phrases that describe what the component powers).

| Canonical Capability Name | Purpose                                             |
|---------------------------|-----------------------------------------------------|
| Traffic                   | Traffic rate and shape capabilities                 |
| Storage                   | Storage growth and retention capabilities           |
| Cache                     | Cache effectiveness and risk capabilities           |
| Queue                     | Queue depth and consumer capabilities               |
| Latency                   | Latency budget and percentile capabilities          |
| Availability              | SLO/SLA and failure budget capabilities             |
| Workloads                 | Workload profile capabilities                       |
| TrafficShape              | Traffic shape pattern capabilities                  |
| Budgets                   | Latency budget allocation capabilities              |
| Detection                 | Slow path and hot path detection capabilities       |
| Targets                   | Availability target capabilities                    |
| Degradation               | Graceful degradation capabilities                   |
| BurnRate                  | Error budget burn rate capabilities                 |
| Models                    | Consistency model capabilities                      |
| Delivery                  | Message delivery semantics capabilities             |
| Conflicts                 | Conflict resolution capabilities                    |
| Lag                       | Projection/replication lag capabilities             |
| Keys                      | Partition key capabilities                          |
| Rules                     | Partitioning rule capabilities                      |
| Strategies                | Replication strategy capabilities                   |
| Shards                    | Shard key and map capabilities                      |
| Rebalancing               | Shard rebalance capabilities                        |
| Risks                     | Shard and partitioning risk capabilities            |
| Core                      | Core messaging vocabulary (Command, Event, Message) |
| Envelope                  | Message envelope capabilities                       |
| Outbox                    | Outbox pattern capabilities                         |
| Inbox                     | Inbox deduplication capabilities                    |
| Consumers                 | Consumer and subscriber capabilities                |
| DeadLetters               | Dead letter queue capabilities                      |
| Policies                  | Retry/backoff/timeout/ack policies                  |
| BrokerModel               | Broker topology capabilities                        |
| Cqrs                      | CQRS command/query side capabilities                |
| Views                     | Projection and materialized view capabilities       |
| Freshness                 | Projection freshness capabilities                   |
| Scenarios                 | Failure scenario capabilities                       |
| Assertions                | Architecture assertion capabilities                 |
| Files                     | Reference architecture file reading capabilities    |
| Validation                | Reference architecture validation capabilities      |
| Registry                  | Scenario registry capabilities                      |
| Execution                 | Scenario execution capabilities                     |
| Analysis                  | Tradeoff analysis capabilities                      |
| ObjectStorage             | Object storage infrastructure boundary              |
| SearchIndex               | Search index infrastructure boundary                |
| MessageBroker             | Message broker infrastructure boundary              |
| StreamProcessor           | Stream processor infrastructure boundary            |
| Transcoding               | Transcoding gateway infrastructure boundary         |
| Recommendation            | Recommendation gateway infrastructure boundary      |
| Cdn                       | CDN infrastructure boundary                         |
| GraphQl                   | GraphQL API surface modeling                        |
| Rest                      | REST API surface modeling                           |
| JsonApi                   | JSON:API surface modeling                           |
| OpenApi                   | OpenAPI contract modeling                           |
| Webhooks                  | Webhook delivery modeling                           |
| Rpc                       | RPC API modeling                                    |

### 4.4 Forbidden Names in V3

The following names are forbidden in V3 component structure:

```text
CapacityBuilder          — Builder is forbidden
LoadManager              — Manager is forbidden
ConsistencyProcessor     — Processor is forbidden
LatencyService           — Service is forbidden
SimulationHelper         — Helper is forbidden
TradeoffUtils            — Utils is forbidden
MessagingAdapters/       — Adapters is forbidden as folder
ConsistencyContracts/    — Contracts is forbidden as folder
AvailabilityServices/    — Services is forbidden as folder
ShardingHandlers/        — Handlers is forbidden as folder
PartitioningCommands/    — Commands is forbidden as folder
FailurePolicies/         — Policies as folder is forbidden (use Capabilities/)
InternalSystem/          — Forbidden
ExportedCapabilities/    — Forbidden
```

---

## 5. Overlap Analysis

### 5.1 Capacity vs LatencyBudget

**Overlap**: Both model latency assumptions.

- Capacity has `LatencyBudget` in its Capabilities (for capacity estimation).
- LatencyBudget component has its own `LatencyBudget` model (for detailed budget allocation).

**Resolution**: Capacity owns the latency budget as one of many capacity metrics. LatencyBudget component owns detailed
budget allocation, detection, and per-layer breakdown.

- Use `Capacity/Capabilities/Latency/LatencyBudget` for capacity-level latency assumptions.
- Use `LatencyBudget/System/PublicSurface/LatencyBudget` for detailed budget allocation and validation.

**Risk**: Low. They serve different purposes — estimation vs allocation.

### 5.2 Capacity vs Availability

**Overlap**: Both model SLO/SLA and failure budget.

- Capacity has `Slo`, `Sla`, `FailureBudget`, `ErrorBudgetBurnRate` in Capabilities/Availability.
- Availability component has its own `Slo`, `Sla`, `FailureBudget`, `ErrorBudgetBurnRate`.

**Resolution**: This is a genuine overlap. Two approaches:

**Approach A (Recommended)**: Availability owns SLO/SLA/failure budget as its primary responsibility. Capacity
references Availability models but does not duplicate them.

**Approach B**: Capacity owns capacity-level availability assumptions (input). Availability owns detailed availability
modeling, degradation, and burn rate (output).

**Decision**: Approach A. Move `Slo`, `Sla`, `FailureBudget`, `ErrorBudgetBurnRate` to
`Availability/Capabilities/Targets/` and `Availability/Capabilities/BurnRate/`. Capacity references them. This avoids
duplication and makes Availability the single source of truth for availability modeling.

### 5.3 Capacity vs LoadModel

**Overlap**: Both model traffic assumptions.

- Capacity has `RequestsPerSecond`, `ReadsPerSecond`, `WritesPerSecond`, `PeakTrafficMultiplier`, etc.
- LoadModel has `WorkloadProfile`, `ReadHeavyWorkload`, `WriteHeavyWorkload`, etc.

**Resolution**: LoadModel owns workload shape (read-heavy, write-heavy, burst, fanout, streaming, batch). Capacity owns
raw traffic numbers (RPS, read/write ratio). They are complementary, not duplicative.

- Capacity says "100k RPS, 99:1 read/write".
- LoadModel says "this is a read-heavy workload with spiky traffic".

**Risk**: Low. They complement each other.

### 5.4 Consistency vs Projections

**Overlap**: Both model projection lag and staleness.

- Consistency has `ProjectionLag`, `ReplicationLag`, `StalenessBudget` in Capabilities/Lag.
- Projections has `ProjectionLag`, `ProjectionFreshness`, `StalenessBudget` in Capabilities/Freshness.

**Resolution**: Projections owns projection-specific lag (CQRS read model freshness). Consistency owns general
consistency lag (replication lag, staleness budgets for data consistency).

- Use `Projections/Capabilities/Freshness/ProjectionLag` for CQRS projection lag.
- Use `Consistency/Capabilities/Lag/ReplicationLag` for database replication lag.
- Use `Consistency/Capabilities/Lag/StalenessBudget` for general data staleness tolerance.

**Risk**: Medium. Clear naming discipline required to avoid confusion.

### 5.5 Messaging vs Projections

**Overlap**: Both reference CQRS concepts.

- Messaging has `Command`, `Event`, `Message`, `Job` in Capabilities/Core.
- Projections has `CommandSide`, `QuerySide`, `ReadModel` in Capabilities/Cqrs.

**Resolution**: Messaging owns the message vocabulary (Command, Event, Message, Job, Outbox, Inbox, Consumer).
Projections owns the CQRS architecture pattern (CommandSide, QuerySide, ReadModel, Projection, MaterializedView).

- Messaging: "What is a Command? What is an Event? How do they flow?"
- Projections: "How does the read model get built from events?"

**Risk**: Low. Complementary responsibilities.

### 5.6 Consistency vs Messaging

**Overlap**: Both model delivery semantics.

- Consistency has `AtLeastOnceDelivery`, `AtMostOnceDelivery`, `ExactlyOnceIllusion` in Capabilities/Delivery.
- Messaging has `RetryPolicy`, `BackoffPolicy`, `AcknowledgementPolicy` in Capabilities/Policies.

**Resolution**: Messaging owns the delivery mechanism (retry, backoff, ack, dead letter). Consistency owns the delivery
guarantee (at-least-once, at-most-once, exactly-once illusion) as a consistency property.

- Messaging: "How do we retry? How do we back off? How do we acknowledge?"
- Consistency: "What consistency guarantee does this delivery provide?"

**Risk**: Low. Complementary.

### 5.7 Failure vs Simulation

**Overlap**: Both model and execute failure scenarios.

- Failure has `Scenarios/` (CacheUnavailable, DatabaseTimeout, etc.) and `Policies/` (Retry, Timeout, etc.).
- Simulation has `Failure/InjectFailure`, `FailureScenarioResult`, etc.

**Resolution**: Failure owns the scenario definitions and policy catalog. Simulation owns the execution engine that runs
scenarios and produces results.

- Failure: "Here is what a cache outage looks like. Here is the retry policy."
- Simulation: "Let me inject a cache outage and measure the impact."

**Risk**: Low. Clear separation: definition vs execution.

### 5.8 Simulation vs ScenarioRunner

**Overlap**: Both run scenarios.

- Simulation runs architecture simulations (load, failure, consistency).
- ScenarioRunner runs named scenarios against reference architectures.

**Resolution**: Simulation is the low-level execution engine. ScenarioRunner is the high-level orchestrator that loads
scenarios from YAML, resolves the target architecture, and delegates to Simulation.

- Simulation: "Inject failure, advance clock, measure impact."
- ScenarioRunner: "Load scenarios.yaml, run 'redis-outage' against url-shortener, produce report."

**Risk**: Low. Orchestrator vs engine.

### 5.9 ArchitectureTests vs Simulation

**Overlap**: Both validate architecture.

- ArchitectureTests makes assertions (AssertHotPathAvoidsSynchronousAnalytics, etc.).
- Simulation runs scenarios and produces violations.

**Resolution**: ArchitectureTests makes static or semi-static assertions about architecture structure and properties.
Simulation runs dynamic scenarios and measures behavior.

- ArchitectureTests: "Does this architecture have a retry policy for external calls?" (structural)
- Simulation: "What happens when we inject a 5-second external call timeout?" (behavioral)

**Risk**: Low. Structural vs behavioral validation.

### 5.10 TradeoffReport vs Consistency

**Overlap**: Both analyze consistency tradeoffs.

- TradeoffReport has `AnalyzeConsistencyTradeoff`.
- Consistency has `ExplainConsistencyTradeoff`.

**Resolution**: Consistency explains consistency-specific tradeoffs (strong vs eventual, read-your-writes vs monotonic).
TradeoffReport provides a general tradeoff analysis framework that can include consistency, latency, cost, and
operational complexity.

- Consistency: "Strong consistency costs 20ms more but prevents stale reads."
- TradeoffReport: "Overall tradeoff analysis: consistency + latency + cost + ops complexity."

**Risk**: Low. Specific vs general.

### 5.11 InfrastructureBoundary vs Integration (V2)

**Overlap**: Both reference external infrastructure.

- V3 InfrastructureBoundary models infrastructure boundaries at architecture level.
- V2 Integration/ObjectStorage implements object storage at runtime.

**Resolution**: V3 models. V2 executes. V3 InfrastructureBoundary says "this architecture uses S3 for object storage
with 5ms timeout and retry." V2 Integration/ObjectStorage actually talks to S3.

**Risk**: Low. Clear plane separation.

---

## 6. Phased Implementation Order

### Phase 1: Foundation + Schema (V3-01) — THIS PHASE

**Goal**: Define reference architecture schema, capacity.yaml schema, scenarios.yaml schema, architecture-tests.yaml
schema, and validators.

**Scope**:

- `labs/SystemDesignKit/` only. No component implementation yet.
- Schema definition files (YAML schemas or JSON schemas).
- Schema validator flows.
- Canonical vocabulary codified in documentation.
- This plan document.

**Deliverables**:

1. `labs/SystemDesignKit/schemas/capacity-schema.yaml` — capacity.yaml schema
2. `labs/SystemDesignKit/schemas/scenarios-schema.yaml` — scenarios.yaml schema
3. `labs/SystemDesignKit/schemas/architecture-tests-schema.yaml` — architecture-tests.yaml schema
4. `labs/SystemDesignKit/System/Flows/ValidateCapacitySchema/` — validator flow
5. `labs/SystemDesignKit/System/Flows/ValidateScenariosSchema/` — validator flow
6. `labs/SystemDesignKit/System/Flows/ValidateArchitectureTestsSchema/` — validator flow
7. `labs/SystemDesignKit/System/Capabilities/SchemaValidation/` — shared validation capability
8. `labs/SystemDesignKit/README.md` — updated with V3-01 scope

**Validation**:

- Schema validator rejects invalid capacity.yaml.
- Schema validator rejects invalid scenarios.yaml.
- Schema validator rejects invalid architecture-tests.yaml.
- Schema validator accepts valid examples.
- No production code outside labs/.

### Phase 2: Capacity Engine (V3-02)

**Goal**: Implement capacity model, traffic model, storage growth, cache hit, queue depth, latency budget,
SLO/SLA/failure budget.

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-01 schemas must exist and validate.

### Phase 3: Consistency Engine (V3-03)

**Goal**: Consistency taxonomy, delivery semantics, staleness budget, conflict resolution, projection lag.

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-02 capacity engine (consistency needs capacity assumptions).

### Phase 4: Messaging and CQRS Engine (V3-04)

**Goal**: Command/Event/Message/Job vocabulary, Outbox/Inbox, Consumer/Subscriber, Projection, DeadLetterQueue,
IdempotencyKey.

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-03 consistency engine (messaging needs consistency guarantees).

### Phase 5: Failure Model (V3-05)

**Goal**: Failure scenarios (cache unavailable, database timeout, duplicate message, worker crash, projection delay,
clock skew, partial write, network timeout, rate limit storm, hot key).

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-02 capacity + V3-04 messaging.

### Phase 6: Architecture Tests (V3-06)

**Goal**: Executable architecture assertions (hot path, idempotent command, projection consistency, failure budget, dead
letter policy, external timeout/retry, cache stampede).

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-02 through V3-05 (assertions need models to assert against).

### Phase 7: Scenario Runner (V3-07)

**Goal**: Scenario registry, timeline, result, report, CLI command.

**Scope**: `labs/SystemDesignKit/` only.

**Dependencies**: V3-06 architecture tests + V3-05 failure model.

### Phase 8: URL Shortener Proof (V3-08)

**Goal**: First reference architecture validates end-to-end.

**Scope**: `reference-architectures/url-shortener/` + `examples/system-design/url-shortener/`.

**Dependencies**: V3-01 through V3-07.

### Phase 9: Distributed Rate Limiter Proof (V3-09)

**Goal**: Second reference architecture validates.

**Scope**: `reference-architectures/distributed-rate-limiter/`.

**Dependencies**: V3-08.

### Phase 10: Payment Processor Proof (V3-10)

**Goal**: Third reference architecture validates with idempotency, outbox, saga.

**Scope**: `reference-architectures/payment-processor/`.

**Dependencies**: V3-09.

### Phase 11: Promotion (V3-11)

**Goal**: Promote validated V3 code from `labs/SystemDesignKit/` to `components/SystemDesign/`.

**Scope**: Move validated, tested, proven code. Update autoload. Update documentation.

**Dependencies**: At least 2 reference architectures validate, 1 runnable example passes, 3 failure scenarios catch real
violations, architecture tests have meaningful assertions.

---

## 7. Runtime Assumptions

### 7.1 PHP Version

V3 assumes PHP 8.2+ (matching V1/V2 baseline).

### 7.2 No New Runtime Dependencies

V3 must not introduce new runtime dependencies for its core functionality. Schema validation uses standard PHP (
json_decode, yaml parsing via Symfony Yaml if already present, or native PHP parsing).

If Symfony Yaml is not already a dependency, V3 may:

- Use native PHP to parse simple YAML (key-value, nested maps, lists).
- Or add `symfony/yaml` as a dev dependency only.

### 7.3 CLI Execution

V3 commands execute via `php avax system-design:*` CLI. V3 must not require HTTP context to run. V3 is a CLI tool, not
an HTTP middleware.

### 7.4 Simulation Scope

V3 simulations are analytical, not load-testing. V3 does not spawn thousands of concurrent requests. V3 models behavior
mathematically and structurally.

### 7.5 File-Based Configuration

V3 reads configuration from YAML files in reference architecture directories. V3 does not require a database to store
architecture models.

### 7.6 Test Execution

V3 architecture tests run as PHPUnit tests or as standalone CLI assertions. V3 does not require a separate test runner.

---

## 8. Scalability Assumptions

### 8.1 Reference Architecture Count

V3 must support at least 20 reference architectures without performance degradation. Each reference architecture is a
directory with YAML and markdown files. File I/O is minimal.

### 8.2 Scenario Count

V3 must support at least 50 named scenarios per reference architecture. Scenarios are loaded on demand, not eagerly.

### 8.3 Simulation Performance

V3 analytical simulations must complete in under 5 seconds for a single reference architecture. V3 does not perform real
load testing.

### 8.4 Architecture Test Performance

V3 architecture test suite must complete in under 10 seconds for a single reference architecture.

### 8.5 Memory

V3 must not load all reference architectures into memory simultaneously. Load on demand. Release after validation.

---

## 9. Risks

### 9.1 Schema Complexity Risk

**Risk**: capacity.yaml schema becomes too complex, discouraging adoption.

**Mitigation**: Start with minimal required fields. Make advanced fields optional. Provide example files.

### 9.2 Overlap Confusion Risk

**Risk**: Developers confuse Capacity/Latency with LatencyBudget component, or Consistency/Lag with
Projections/Freshness.

**Mitigation**: Clear documentation. Cross-references. Distinct naming. Ownership boundaries enforced in code reviews.

### 9.3 Labs-to-Components Promotion Risk

**Risk**: V3 code promoted to components/ before it is proven useful.

**Mitigation**: Strict promotion criteria (Section 11). V3-11 requires 2 reference architectures, 1 runnable example, 3
failure scenarios, meaningful architecture test assertions.

### 9.4 Scope Creep Risk

**Risk**: V3 becomes an infrastructure implementation instead of a modeling framework.

**Mitigation**: Hard boundary enforced (Section 2 of V3 master plan). V3 models. V2 executes. Code reviews check for
boundary violations.

### 9.5 YAML Parsing Risk

**Risk**: Native PHP YAML parsing is fragile for complex nested structures.

**Mitigation**: Use Symfony Yaml if available. Fall back to simple native parser for basic structures. Validate schema
strictly.

### 9.6 Simulation Accuracy Risk

**Risk**: Analytical simulations produce misleading results that don't match real-world behavior.

**Mitigation**: Document assumptions clearly. Label results as "analytical estimate, not benchmark." Validate against
known reference architectures where real data exists.

### 9.7 Dependency on V2 Planes

**Risk**: V3 architecture tests depend on V2 reliability primitives (retry, timeout, circuit breaker) that may change.

**Mitigation**: V3 models these at architecture level. V3 does not depend on V2 implementation details. V3 asserts "
architecture has a retry policy," not "architecture uses RetryPolicy class X."

---

## 10. Non-Goals

V3 explicitly does NOT do the following:

```text
1. V3 is not a load testing tool.
   It does not spawn real HTTP requests against a running application.

2. V3 is not an APM (Application Performance Monitoring) tool.
   It does not collect real-time metrics from production systems.

3. V3 is not a distributed tracing tool.
   It does not trace individual requests through a microservice mesh.

4. V3 is not an infrastructure provisioning tool.
   It does not create or manage servers, databases, or message brokers.

5. V3 is not a chaos engineering tool.
   It does not inject real failures into production systems.

6. V3 is not a database query optimizer.
   It does not analyze SQL query plans or recommend indexes.

7. V3 is not a code analysis tool.
   It does not analyze PHP source code for complexity or quality.

8. V3 is not a cost estimation tool.
   It does not calculate AWS/GCP/Azure bills.

9. V3 is not a replacement for real benchmarking.
   Analytical simulations are estimates, not measurements.

10. V3 is not a production runtime component.
    V3 validates architecture before deployment. It does not run in production.
```

---

## 11. Promotion Criteria (labs → components)

V3 code may be promoted from `labs/SystemDesignKit/` to `components/SystemDesign/` only when ALL of the following are
proven:

```text
[ ] V1 Kernel Green (PROVEN).
[ ] V2 platform baseline GREEN (PROVEN).
[ ] At least 2 reference architectures validate successfully.
[ ] At least 1 runnable system-design example passes all architecture tests.
[ ] At least 3 failure scenarios catch real architectural violations.
[ ] Architecture tests have meaningful assertions (can fail bad designs).
[ ] Tradeoff report can be generated for a reference architecture.
[ ] Schema validators reject invalid YAML and accept valid YAML.
[ ] No forbidden folder names exist in promoted code.
[ ] All promoted components follow canonical component shape.
[ ] Public API is classified as @experimental or @public.
[ ] Component completion standard met for each promoted component.
[ ] Documentation exists in docs/ for promoted components.
[ ] Tests exist and pass for promoted components.
```

Until then, V3 remains in labs/.

---

## 12. Future Expansion Points

### 12.1 Cost Modeling

V3 may eventually include cost modeling (estimated infrastructure cost for a given capacity). This is deferred. It
requires a cost database and pricing assumptions.

### 12.2 Multi-Region Modeling

V3 may eventually model multi-region architectures (latency between regions, data residency, failover). This is
deferred. It requires geographic latency data.

### 12.3 Security Modeling

V3 may eventually model security properties (attack surface, data exposure, compliance). This is deferred. It overlaps
with V2 Security plane.

### 12.4 Machine Learning Workload Modeling

V3 may eventually model ML workloads (training time, inference latency, model size). This is deferred. It requires
ML-specific vocabulary.

### 12.5 Real-Time Collaboration Modeling

V3 may eventually model real-time collaboration (CRDTs, operational transformation, presence). This is deferred. It
requires CRDT-specific vocabulary.

### 12.6 Graph Workload Modeling

V3 may eventually model graph database workloads (traversal depth, relationship cardinality, index effectiveness). This
is deferred.

---

## 13. What Belongs in V3 vs What Does Not

### 13.1 Belongs in V3

```text
- Capacity modeling and validation
- Load modeling and simulation
- Latency budget allocation and validation
- Availability/SLO/SLA modeling
- Consistency modeling and tradeoff analysis
- Partitioning and sharding modeling
- Replication modeling
- Messaging pattern modeling (outbox, inbox, consumer, dead letter)
- CQRS and projection modeling
- Cache strategy modeling
- Failure scenario modeling
- Architecture simulation execution
- Architecture test assertions
- Reference architecture validation
- Scenario execution and reporting
- Tradeoff analysis and reporting
- Infrastructure boundary modeling (architecture level)
- API surface modeling (architecture level)
```

### 13.2 Does NOT Belong in V3

```text
- Runtime HTTP handling (V1/V2 framework)
- Runtime cache operations (V2 Application/Cache)
- Runtime database operations (V2 DataStack/Database)
- Runtime queue operations (V2 Operations/Queue)
- Runtime event dispatch (V2 Operations/MessageBus)
- Runtime retry/timeout/circuit-breaker execution (V2 Operations/Resilience)
- Runtime telemetry export (V2 Operations/Observability)
- Runtime object storage operations (V2 Integration/ObjectStorage)
- Custom Kafka implementation
- Custom Redis implementation
- Custom RocksDB implementation
- Custom Raft/Paxos implementation
- Custom distributed database implementation
- Low-level networking
- Real load testing (spawn real requests)
- Production APM
- Chaos engineering (inject real failures)
- Cost estimation
- Security threat modeling (V2 Security plane)
- Code quality analysis
- Database query optimization
```

### 13.3 Deferred to Future V3 Phases

```text
- Cost modeling (Phase 12.1)
- Multi-region modeling (Phase 12.2)
- Security modeling (Phase 12.3)
- ML workload modeling (Phase 12.4)
- Real-time collaboration modeling (Phase 12.5)
- Graph workload modeling (Phase 12.6)
```

### 13.4 Experimental (labs/ only, no promotion path yet)

```text
- Architecture explanation (natural language generation)
- Architecture comparison (A vs B tradeoff matrix)
- Architecture recommendation (suggest better patterns)
- Architecture migration planning (how to move from A to B)
```

---

## 14. V3-01 Detailed Task Breakdown

### 14.1 Task 1: Create Schema Files

**Files**:

- `labs/SystemDesignKit/schemas/capacity-schema.yaml`
- `labs/SystemDesignKit/schemas/scenarios-schema.yaml`
- `labs/SystemDesignKit/schemas/architecture-tests-schema.yaml`

**Content**: YAML schema definitions that describe the required structure, required fields, allowed values, and
validation rules for each configuration file type.

### 14.2 Task 2: Create Schema Validation Capability

**Files**:

- `labs/SystemDesignKit/System/Capabilities/SchemaValidation/ValidateYamlSchema.php`
- `labs/SystemDesignKit/System/Capabilities/SchemaValidation/SchemaValidationError.php`
- `labs/SystemDesignKit/System/Capabilities/SchemaValidation/SchemaValidationResult.php`

**Purpose**: Generic YAML schema validator that checks a YAML file against a schema definition. Reports missing required
fields, invalid values, type mismatches, and unknown fields.

### 14.3 Task 3: Create Schema Validation Flows

**Files**:

- `labs/SystemDesignKit/System/Flows/ValidateCapacitySchema/ValidateCapacitySchema.php`
- `labs/SystemDesignKit/System/Flows/ValidateScenariosSchema/ValidateScenariosSchema.php`
- `labs/SystemDesignKit/System/Flows/ValidateArchitectureTestsSchema/ValidateArchitectureTestsSchema.php`

**Purpose**: Flow-level entry points that load the appropriate schema and validate a target YAML file against it.

### 14.4 Task 4: Create Example Valid YAML Files

**Files**:

- `labs/SystemDesignKit/examples/valid-capacity.yaml`
- `labs/SystemDesignKit/examples/valid-scenarios.yaml`
- `labs/SystemDesignKit/examples/valid-architecture-tests.yaml`

**Purpose**: Example files that pass schema validation. Used for testing the validators.

### 14.5 Task 5: Create Example Invalid YAML Files

**Files**:

- `labs/SystemDesignKit/examples/invalid-capacity-missing-rps.yaml`
- `labs/SystemDesignKit/examples/invalid-capacity-bad-types.yaml`
- `labs/SystemDesignKit/examples/invalid-scenarios-missing-name.yaml`
- `labs/SystemDesignKit/examples/invalid-architecture-tests-missing-assertion.yaml`

**Purpose**: Example files that fail schema validation. Used for testing the validators catch errors.

### 14.6 Task 6: Create Tests

**Files**:

- `tests/SystemDesignKit/Capacity/ValidateCapacitySchemaTest.php`
- `tests/SystemDesignKit/Capacity/ValidateScenariosSchemaTest.php`
- `tests/SystemDesignKit/Capacity/ValidateArchitectureTestsSchemaTest.php`

**Purpose**: PHPUnit tests that prove the schema validators work correctly for valid and invalid input.

### 14.7 Task 7: Update README

**File**:

- `labs/SystemDesignKit/README.md`

**Purpose**: Document V3-01 scope, schema validation usage, and promotion criteria.

---

## 15. Governance Compliance

### 15.1 Rules Applied

- **AGENTS.md**: Stage lock (V3-01, labs/ only, no promotion), canonical component shape, screaming architecture,
  forbidden folder names, concept words not folder names, use case translation to Flow.
- **how-to-design-components.md**: Canonical component shape (
  System/PublicSurface/Flows/Capabilities/Configuration/Foundation), completion standard, flow vs capability rule,
  shared last rule.
- **how-to-architecture.md**: Fractal flow architecture, recursive ownership, folder says flow/capability, unit says
  responsibility, function says exact action.
- **how-to-write-avax.md**: Strict adherence to all how-to documents, enterprise-grade quality, evidence-based claims,
  TODO.md tracking, validation before completion claims.
- **EXECUTION.md**: V3 stage lock (labs/ only until promotion criteria met), V3-01 scope definition.

### 15.2 Rules Intentionally Not Applicable

- **how-to-system-security.md**: V3-01 does not touch security-sensitive code (schema validation of architecture files).
  Will apply in later phases if V3 models security properties.
- **how-to-system-performance.md**: V3-01 schema validation is not a hot path. Performance rules apply to simulation and
  runtime behavior in later phases.
- **how-to-production-readiness.md**: V3-01 is labs/experimental. Production readiness applies at promotion time (
  V3-11).

### 15.3 Naming Compliance

All names follow AvaX canonical vocabulary:

- No Manager, Service, Helper, Utils, Builder, Processor, Handler, Adapter, Contract as default names.
- Folder names say flow or capability.
- Class names say responsibility.
- Method names say exact action.

### 15.4 Structure Compliance

All V3-01 code follows canonical component shape:

```text
labs/SystemDesignKit/
  System/
    PublicSurface/
    Flows/
    Capabilities/
    Configuration/    (if needed for schema config)
    Foundation/       (if needed for error types)
  schemas/
  examples/
  README.md
```

---

## 16. Evidence and Validation

### 16.1 V3-01 Validation Commands

After V3-01 implementation:

```bash
# Autoload
composer dump-autoload -o

# Tests
vendor/bin/phpunit --no-coverage --filter SystemDesignKit

# Static analysis
vendor/bin/phpstan analyse labs/SystemDesignKit --memory-limit=1G

# Governance checks
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

### 16.2 V3-01 Success Criteria

```text
[ ] Schema validator rejects capacity.yaml with missing required fields.
[ ] Schema validator rejects capacity.yaml with invalid types.
[ ] Schema validator accepts valid capacity.yaml.
[ ] Schema validator rejects scenarios.yaml with missing required fields.
[ ] Schema validator accepts valid scenarios.yaml.
[ ] Schema validator rejects architecture-tests.yaml with missing required fields.
[ ] Schema validator accepts valid architecture-tests.yaml.
[ ] All tests pass (PHPUnit).
[ ] PHPStan reports 0 errors for labs/SystemDesignKit.
[ ] No forbidden folders in labs/SystemDesignKit.
[ ] Canonical component shape followed.
[ ] README updated.
```

---

## 17. Next Allowed Action

After this plan is approved:

**V3-01 Task 1**: Create schema definition files in `labs/SystemDesignKit/schemas/`.

No implementation of V3-02 or later phases is allowed until V3-01 is complete and validated.

---

## 18. Plan Status

Status: DRAFT
Created: 2026-05-07
Author: AvaX agent execution
Pending: Implementation of V3-01 per task breakdown in Section 14.
