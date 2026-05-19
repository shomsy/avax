# AvaX V3 Master Plan — Executable System Design Framework

Status: dedicated V3 roadmap  
Depends on: V1 Production Kernel GREEN and V2 Enterprise Platform Engine at least YELLOW/GREEN  
Purpose: make AvaX capable of modeling, validating, simulating, and demonstrating system-design-grade application
architectures.

---

# 0. V3 thesis

V3 is not an examples folder.

V3 is not a collection of markdown notes.

V3 is not “we have URL Shortener example”.

V3 is the layer where AvaX becomes:

```text
a framework that can model, validate, simulate, test, and explain large application architectures.
```

V3 should answer these questions:

```text
Can this architecture handle the expected load?
Where are the bottlenecks?
What happens when Redis is down?
What happens when the broker duplicates a message?
Is this command idempotent?
Is this projection eventually consistent?
Is this read path protected from cache stampede?
Does the hot path avoid slow synchronous side effects?
Does the architecture have an SLO and failure budget?
Does every external call have timeout/retry/circuit policy?
Can the architecture explain its tradeoffs?
```

V3 turns AvaX into a system-design validation framework.

---

# 1. V3 one-line definition

```text
V3 validates large-system behavior.
```

Long form:

```text
AvaX V3 is an executable system-design framework for modeling capacity,
consistency, partitioning, messaging, projections, failures, simulations,
architecture tests, and reference architectures.
```

---

# 2. V3 hard boundary

AvaX V3 must not become infrastructure.

AvaX core must not implement:

```text
custom Kafka
custom Redis Cluster
custom RocksDB
custom Elasticsearch
custom FFmpeg
custom CDN
custom ML recommendation engine
custom Raft/Paxos consensus
custom high-frequency stream processing engine
custom ultra-low-latency matching engine
custom distributed database
low-level networking stack
```

AvaX V3 may provide:

```text
ports
adapters
architecture models
failure models
capacity models
simulations
scenario runners
architecture tests
reference architectures
operator diagnostics
tradeoff reports
```

Correct mental model:

```text
AvaX does not replace infrastructure.
AvaX disciplines how application architecture uses infrastructure.
```

---

# 3. Placement strategy

Start here:

```text
labs/SystemDesignKit/
```

Promote here only after proof:

```text
components/SystemDesign/
```

Promotion requirements:

```text
[ ] V1 Kernel Green.
[ ] V2 platform baseline at least YELLOW/GREEN.
[ ] URL Shortener reference architecture validates.
[ ] Distributed Rate Limiter or News Feed reference architecture validates.
[ ] At least one runnable system-design example passes architecture tests.
[ ] SystemDesign public API is classified as @experimental or @public.
[ ] Architecture tests are useful enough to catch real design mistakes.
```

---

# 4. Final V3 project tree

```text
components/SystemDesign/
  Capacity/
  LoadModel/
  LatencyBudget/
  Availability/
  Consistency/
  Partitioning/
  Replication/
  Sharding/
  Messaging/
  Projections/
  Caching/
  Failure/
  Simulation/
  ArchitectureTests/
  ReferenceArchitecture/
  ScenarioRunner/
  TradeoffReport/
  InfrastructureBoundary/
  ApiSurface/
  Examples/

labs/
  SystemDesignKit/
    README.md
    experiments/
    scenarios/
    spikes/

reference-architectures/
  url-shortener/
  parking-lot/
  distributed-rate-limiter/
  news-feed/
  notification-system/
  payment-processor/
  booking-system/
  distributed-cache/
  chat-system/
  file-storage/
  search-system/
  video-metadata-system/
  media-transcoding-platform/
  marketplace-matching/

examples/
  system-design/
    url-shortener/
    distributed-rate-limiter/
    notification-system/
    payment-processor/
```

---

# 5. components/SystemDesign detailed tree

## 5.1 Capacity

Purpose:

```text
Model whether a system can handle expected traffic, growth, fanout, cache efficiency,
queue pressure, and latency requirements.
```

Tree:

```text
components/SystemDesign/Capacity/
  System/
    PublicSurface/
      CapacityModel.php
        Public model for traffic/storage/cache/queue/latency/availability assumptions.

      CapacityBudget.php
        Public budget object for system capacity limits.

      CapacityReport.php
        Public report generated after validation.

      CapacityViolation.php
        Public violation model.

    Flows/
      ValidateCapacityModel/
        ValidateCapacityModel.php
          Validates capacity.yaml against required schema and rules.

      EstimateTrafficLoad/
        EstimateTrafficLoad.php
          Calculates read/write traffic, fanout, peak traffic, burst traffic.

      EstimateStorageGrowth/
        EstimateStorageGrowth.php
          Estimates storage growth over retention window.

      EstimateQueuePressure/
        EstimateQueuePressure.php
          Estimates queue depth and consumer throughput.

      EstimateCacheEffectiveness/
        EstimateCacheEffectiveness.php
          Estimates expected cache hit/miss behavior.

      EstimateLatencyBudget/
        EstimateLatencyBudget.php
          Splits latency budget across HTTP, cache, DB, queue, external calls.

      EstimateFailureBudget/
        EstimateFailureBudget.php
          Converts SLO into allowed downtime/error budget.

    Capabilities/
      Traffic/
        RequestsPerSecond.php
        ReadsPerSecond.php
        WritesPerSecond.php
        ReadWriteRatio.php
        PeakTrafficMultiplier.php
        BurstWindow.php
        FanoutSize.php

      Storage/
        StorageGrowth.php
        RetentionPeriod.php
        AverageObjectSize.php
        DataGrowthCurve.php
        StorageTier.php

      Cache/
        CacheHitRatio.php
        CacheMissPenalty.php
        CacheWarmupBudget.php
        CacheStampedeRisk.php

      Queue/
        QueueDepth.php
        QueueDelayBudget.php
        ConsumerThroughput.php
        ConsumerCount.php
        RetryAmplification.php

      Latency/
        LatencyBudget.php
        P50Latency.php
        P95Latency.php
        P99Latency.php
        TailLatencyRisk.php

      Availability/
        Slo.php
        Sla.php
        FailureBudget.php
        ErrorBudgetBurnRate.php

    Configuration/
      CapacityModelConfiguration.php

    Foundation/
      Failure/
        CapacityModelInvalid.php
        CapacityBudgetExceeded.php

    how-this-works.md
```

Required metrics:

```text
requests per second
read/write ratio
storage growth
cache hit ratio
fanout size
queue depth
latency budget
SLO/SLA
failure budget
```

---

## 5.2 LoadModel

Purpose:

```text
Describe workload shape, not just raw request count.
```

Tree:

```text
components/SystemDesign/LoadModel/
  System/
    PublicSurface/
      LoadModel.php
      WorkloadProfile.php

    Flows/
      ValidateLoadModel/
        ValidateLoadModel.php

      SimulateLoadProfile/
        SimulateLoadProfile.php

    Capabilities/
      Workloads/
        ReadHeavyWorkload.php
        WriteHeavyWorkload.php
        BurstTrafficWorkload.php
        FanoutWorkload.php
        StreamingWorkload.php
        BatchWorkload.php
        RealtimeWorkload.php

      TrafficShape/
        SteadyTraffic.php
        SpikyTraffic.php
        DiurnalTraffic.php
        ViralSpike.php

    Foundation/
      Failure/
        LoadModelInvalid.php

    how-this-works.md
```

---

## 5.3 LatencyBudget

Purpose:

```text
Make latency explicit and testable.
```

Tree:

```text
components/SystemDesign/LatencyBudget/
  System/
    PublicSurface/
      LatencyBudget.php
      LatencyBudgetReport.php

    Flows/
      ValidateLatencyBudget/
        ValidateLatencyBudget.php

      AllocateLatencyBudget/
        AllocateLatencyBudget.php

    Capabilities/
      Budgets/
        HttpLatencyBudget.php
        DatabaseLatencyBudget.php
        CacheLatencyBudget.php
        QueueLatencyBudget.php
        ExternalCallLatencyBudget.php
        RenderLatencyBudget.php

      Detection/
        DetectSynchronousSlowPath.php
        DetectHotPathExternalCall.php
        DetectUnboundedFanout.php

    Foundation/
      Failure/
        LatencyBudgetExceeded.php

    how-this-works.md
```

---

## 5.4 Availability

Purpose:

```text
Model availability targets, SLO, SLA, failure budgets, and degradation.
```

Tree:

```text
components/SystemDesign/Availability/
  System/
    PublicSurface/
      AvailabilityModel.php
      FailureBudget.php
      DegradationPolicy.php

    Flows/
      ValidateAvailabilityTarget/
        ValidateAvailabilityTarget.php

      EstimateFailureBudget/
        EstimateFailureBudget.php

      EvaluateDegradationPlan/
        EvaluateDegradationPlan.php

    Capabilities/
      Targets/
        Slo.php
        Sla.php
        ErrorBudget.php

      Degradation/
        GracefulDegradation.php
        PartialAvailability.php
        ReadOnlyMode.php
        FeatureDegradation.php

      BurnRate/
        ErrorBudgetBurnRate.php
        AlertThreshold.php

    Foundation/
      Failure/
        AvailabilityModelInvalid.php

    how-this-works.md
```

---

## 5.5 Consistency

Purpose:

```text
Model consistency guarantees, staleness, delivery semantics, projection lag, and conflict resolution.
```

Tree:

```text
components/SystemDesign/Consistency/
  System/
    PublicSurface/
      ConsistencyModel.php
      ConsistencyReport.php

    Flows/
      ValidateConsistencyModel/
        ValidateConsistencyModel.php

      ExplainConsistencyTradeoff/
        ExplainConsistencyTradeoff.php

      DetectConsistencyRisk/
        DetectConsistencyRisk.php

    Capabilities/
      Models/
        StrongConsistency.php
        EventualConsistency.php
        ReadYourWrites.php
        MonotonicReads.php
        CausalConsistency.php
        SessionConsistency.php

      Delivery/
        AtLeastOnceDelivery.php
        AtMostOnceDelivery.php
        ExactlyOnceIllusion.php

      Conflicts/
        ConflictResolution.php
        LastWriteWins.php
        VersionVector.php
        OptimisticConcurrency.php
        MergeStrategy.php

      Lag/
        ProjectionLag.php
        ReplicationLag.php
        StalenessBudget.php

    Foundation/
      Failure/
        ConsistencyModelInvalid.php
        ConsistencyRiskDetected.php

    how-this-works.md
```

---

## 5.6 Partitioning

Purpose:

```text
Model how data/workload is divided and what risks appear.
```

Tree:

```text
components/SystemDesign/Partitioning/
  System/
    PublicSurface/
      PartitioningModel.php

    Flows/
      ValidatePartitioningModel/
        ValidatePartitioningModel.php

      DetectPartitioningRisk/
        DetectPartitioningRisk.php

    Capabilities/
      Keys/
        PartitionKey.php
        RoutingKey.php
        TenantBoundary.php
        NaturalKey.php
        SyntheticKey.php

      Detection/
        DetectHotPartition.php
        DetectSkewedKey.php
        DetectCrossPartitionQuery.php

      Rules/
        CrossPartitionQueryPolicy.php
        TenantIsolationPolicy.php

    Foundation/
      Failure/
        PartitioningModelInvalid.php
        HotPartitionDetected.php

    how-this-works.md
```

---

## 5.7 Replication

Purpose:

```text
Model replication topology, lag, quorum, failover, and split-brain risk.
```

Tree:

```text
components/SystemDesign/Replication/
  System/
    PublicSurface/
      ReplicationModel.php

    Flows/
      ValidateReplicationModel/
        ValidateReplicationModel.php

      EstimateReplicationLag/
        EstimateReplicationLag.php

    Capabilities/
      Strategies/
        LeaderFollowerReplication.php
        MultiLeaderReplication.php
        QuorumReplication.php
        AsyncReplication.php
        SyncReplication.php

      Lag/
        ReplicationLagBudget.php
        ReplicaFreshness.php

      Failure/
        FailoverPlan.php
        SplitBrainRisk.php
        ReplicaUnavailable.php

    Foundation/
      Failure/
        ReplicationModelInvalid.php

    how-this-works.md
```

---

## 5.8 Sharding

Purpose:

```text
Model horizontal data distribution and rebalance strategy.
```

Tree:

```text
components/SystemDesign/Sharding/
  System/
    PublicSurface/
      ShardingModel.php

    Flows/
      ValidateShardingModel/
        ValidateShardingModel.php

      RouteToShard/
        RouteToShard.php

      EstimateShardRebalance/
        EstimateShardRebalance.php

    Capabilities/
      Shards/
        ShardKey.php
        ShardMap.php
        ShardId.php
        ShardRange.php

      Rebalancing/
        ShardRebalancePlan.php
        EstimateRebalanceCost.php

      Risks/
        CrossShardTransactionRisk.php
        HotShardRisk.php

    Foundation/
      Failure/
        ShardingModelInvalid.php

    how-this-works.md
```

---

## 5.9 Messaging

Purpose:

```text
Model asynchronous communication and message delivery discipline.
```

Tree:

```text
components/SystemDesign/Messaging/
  System/
    PublicSurface/
      MessagingModel.php
      MessageFlow.php

    Flows/
      ValidateMessagingModel/
        ValidateMessagingModel.php

      DetectMessagingRisk/
        DetectMessagingRisk.php

    Capabilities/
      Core/
        Command.php
        Event.php
        Message.php
        Job.php

      Envelope/
        MessageId.php
        CorrelationId.php
        CausationId.php
        IdempotencyKey.php
        RetryCount.php

      Outbox/
        Outbox.php
        OutboxMessage.php
        OutboxRelay.php

      Inbox/
        Inbox.php
        InboxMessage.php
        InboxDeduplication.php

      Consumers/
        Consumer.php
        Subscriber.php
        IdempotentConsumer.php
        ConsumerGroup.php

      DeadLetters/
        DeadLetterQueue.php
        PoisonMessage.php
        DeadLetterReason.php

      Policies/
        RetryPolicy.php
        BackoffPolicy.php
        TimeoutPolicy.php
        AcknowledgementPolicy.php

      BrokerModel/
        Topic.php
        Partition.php
        Offset.php
        ConsumerLag.php

    Foundation/
      Failure/
        MessagingModelInvalid.php
        MessagingRiskDetected.php

    how-this-works.md
```

First-class vocabulary:

```text
Command
Event
Message
Job
Outbox
Projection
Consumer
Subscriber
RetryPolicy
DeadLetterQueue
IdempotencyKey
```

---

## 5.10 Projections

Purpose:

```text
Model CQRS read models, projections, materialized views, cache strategies, and projection lag.
```

Tree:

```text
components/SystemDesign/Projections/
  System/
    PublicSurface/
      ProjectionModel.php
      CqrsModel.php

    Flows/
      ValidateProjectionModel/
        ValidateProjectionModel.php

      DetectProjectionRisk/
        DetectProjectionRisk.php

    Capabilities/
      Cqrs/
        CommandSide.php
        QuerySide.php
        ReadModel.php

      Views/
        Projection.php
        MaterializedView.php
        ProjectionRebuilder.php

      Freshness/
        ProjectionLag.php
        ProjectionFreshness.php
        StalenessBudget.php

      Caching/
        CacheAside.php
        WriteThroughCache.php
        WriteBehindCache.php
        ReadThroughCache.php

      Risks/
        ReadModelAsSourceOfTruthRisk.php
        StaleProjectionRisk.php

    Foundation/
      Failure/
        ProjectionModelInvalid.php
        ProjectionRiskDetected.php

    how-this-works.md
```

First-class CQRS concepts:

```text
Command side
Query side
Read model
Projection
Materialized view
Cache-aside
Write-through cache
Eventual consistency
```

---

## 5.11 Caching

Purpose:

```text
Model cache strategy at architecture level, not runtime implementation.
```

Tree:

```text
components/SystemDesign/Caching/
  System/
    PublicSurface/
      CacheStrategyModel.php

    Flows/
      ValidateCacheStrategy/
        ValidateCacheStrategy.php

      DetectCacheRisk/
        DetectCacheRisk.php

    Capabilities/
      Strategies/
        CacheAside.php
        ReadThroughCache.php
        WriteThroughCache.php
        WriteBehindCache.php
        RefreshAhead.php
        StaleWhileRevalidate.php

      Risks/
        CacheStampedeRisk.php
        HotKeyRisk.php
        StaleDataRisk.php
        InvalidationRisk.php

      Budgets/
        CacheHitRatioTarget.php
        CacheTtlBudget.php

    Foundation/
      Failure/
        CacheStrategyInvalid.php

    how-this-works.md
```

---

## 5.12 Failure

Purpose:

```text
Model what breaks and what the architecture promises when it breaks.
```

Tree:

```text
components/SystemDesign/Failure/
  System/
    PublicSurface/
      FailureModel.php
      FailureScenario.php
      FailureReport.php

    Flows/
      ValidateFailureModel/
        ValidateFailureModel.php

      RunFailureAnalysis/
        RunFailureAnalysis.php

    Capabilities/
      Scenarios/
        CacheUnavailable.php
        DatabaseTimeout.php
        QueueDuplicateMessage.php
        WorkerCrashAfterSideEffect.php
        ProjectionDelay.php
        ClockSkew.php
        PartialWrite.php
        NetworkTimeout.php
        RateLimitStorm.php
        HotKey.php
        BrokerUnavailable.php
        ObjectStorageUnavailable.php
        SearchIndexUnavailable.php
        CdnStaleContent.php
        PaymentProviderTimeout.php

      Policies/
        Retry.php
        Timeout.php
        CircuitBreaker.php
        Fallback.php
        Bulkhead.php
        DeadLetter.php
        Compensation.php
        Saga.php
        Lease.php
        Lock.php
        Idempotency.php
        Backpressure.php
        LoadShedding.php

    Foundation/
      Failure/
        FailureModelInvalid.php

    how-this-works.md
```

---

## 5.13 Simulation

Purpose:

```text
Execute architecture scenarios and produce evidence.
```

Tree:

```text
components/SystemDesign/Simulation/
  System/
    PublicSurface/
      Simulation.php
      SimulationResult.php
      SimulationReport.php

    Flows/
      RunArchitectureSimulation/
        RunArchitectureSimulation.php

      RunFailureScenario/
        RunFailureScenario.php

      RunLoadSimulation/
        RunLoadSimulation.php

      RunConsistencySimulation/
        RunConsistencySimulation.php

    Capabilities/
      Load/
        SimulateTraffic.php
        SimulateFanout.php
        SimulateQueueDelay.php
        SimulateCacheHitRatio.php

      Failure/
        InjectFailure.php
        FailureScenarioResult.php

      Time/
        SimulatedClock.php
        AdvanceSimulatedTime.php

      Results/
        SimulationTimeline.php
        SimulationEvent.php
        SimulationViolation.php

    Foundation/
      Failure/
        SimulationFailed.php

    how-this-works.md
```

CLI:

```bash
php avax system-design:simulate reference-architectures/url-shortener --scenario=redis-outage
php avax system-design:simulate reference-architectures/payment-processor --scenario=payment-provider-timeout
php avax system-design:simulate reference-architectures/news-feed --scenario=fanout-spike
```

---

## 5.14 ArchitectureTests

Purpose:

```text
Executable architecture assertions.
```

Tree:

```text
components/SystemDesign/ArchitectureTests/
  System/
    PublicSurface/
      ArchitectureScenario.php
      ArchitectureAssertion.php
      ArchitectureTestRunner.php
      ArchitectureTestReport.php

    Flows/
      RunArchitectureTests/
        RunArchitectureTests.php

      ValidateReferenceArchitecture/
        ValidateReferenceArchitecture.php

    Capabilities/
      Assertions/
        AssertHotPathAvoidsSynchronousAnalytics.php
        AssertCommandPathIsIdempotent.php
        AssertProjectionIsEventuallyConsistent.php
        AssertPublicApiHasObservability.php
        AssertFailureBudgetIsDefined.php
        AssertReadPathHasCacheStrategy.php
        AssertWritePathHasOutboxWhenAsyncSideEffectsExist.php
        AssertQueueHasDeadLetterPolicy.php
        AssertExternalPortHasTimeoutAndRetry.php
        AssertCacheMissPathHasStampedeProtection.php
        AssertTenantBoundaryIsDefined.php
        AssertSensitiveDataIsRedacted.php

    Foundation/
      Failure/
        ArchitectureTestFailed.php

    how-this-works.md
```

Must validate:

```text
redirect path must not synchronously dispatch analytics
payment command must be idempotent
event consumer must be retry-safe
write model must not read projection as source of truth
public API must not call internal capability
cache miss path must be protected from stampede
queue consumer must tolerate duplicate messages
projection delay must not break user-facing invariant
external port must define timeout and retry policy
hot path must have latency budget
```

---

## 5.15 ReferenceArchitecture

Purpose:

```text
Define reusable reference architecture format.
```

Tree:

```text
components/SystemDesign/ReferenceArchitecture/
  System/
    PublicSurface/
      ReferenceArchitecture.php
      ReferenceArchitectureReader.php
      ReferenceArchitectureReport.php

    Flows/
      ReadReferenceArchitecture/
        ReadReferenceArchitecture.php

      ValidateReferenceArchitecture/
        ValidateReferenceArchitecture.php

      GenerateReferenceArchitectureReport/
        GenerateReferenceArchitectureReport.php

    Capabilities/
      Files/
        ReadCapacityFile.php
        ReadArchitectureFile.php
        ReadFailureModes.php
        ReadConsistencyModel.php

      Validation/
        ValidateRequiredFiles.php
        ValidateArchitectureSections.php
        ValidateCapacitySchema.php

    Foundation/
      Failure/
        ReferenceArchitectureInvalid.php

    how-this-works.md
```

---

## 5.16 ScenarioRunner

Purpose:

```text
Run named scenarios against reference architectures or examples.
```

Tree:

```text
components/SystemDesign/ScenarioRunner/
  System/
    PublicSurface/
      ScenarioRunner.php
      Scenario.php
      ScenarioResult.php

    Flows/
      RunScenario/
        RunScenario.php

      ListScenarios/
        ListScenarios.php

    Capabilities/
      Registry/
        ScenarioRegistry.php

      Execution/
        ScenarioContext.php
        ScenarioStep.php
        ScenarioTimeline.php

      Reports/
        ScenarioReport.php

    Foundation/
      Failure/
        ScenarioFailed.php

    how-this-works.md
```

---

## 5.17 TradeoffReport

Purpose:

```text
Explain architectural tradeoffs explicitly.
```

Tree:

```text
components/SystemDesign/TradeoffReport/
  System/
    PublicSurface/
      TradeoffReport.php
      TradeoffAnalyzer.php

    Flows/
      GenerateTradeoffReport/
        GenerateTradeoffReport.php

    Capabilities/
      Analysis/
        AnalyzeConsistencyTradeoff.php
        AnalyzeLatencyTradeoff.php
        AnalyzeCostTradeoff.php
        AnalyzeOperationalComplexity.php

      Reports/
        Tradeoff.php
        TradeoffDecision.php
        TradeoffRisk.php

    Foundation/
      Failure/
        TradeoffAnalysisFailed.php

    how-this-works.md
```

---

## 5.18 InfrastructureBoundary

Purpose:

```text
Model external infrastructure boundaries without implementing infrastructure engines.
```

Tree:

```text
components/SystemDesign/InfrastructureBoundary/
  System/
    PublicSurface/
      InfrastructureBoundary.php
      InfrastructurePortModel.php

    Capabilities/
      ObjectStorage/
        ObjectStorageBoundary.php
        ObjectStorageAccessPattern.php

      SearchIndex/
        SearchIndexBoundary.php
        SearchIndexFreshness.php

      MessageBroker/
        MessageBrokerBoundary.php
        BrokerDeliveryModel.php

      StreamProcessor/
        StreamProcessorBoundary.php
        StreamProcessingSemantics.php

      Media/
        TranscodingGatewayBoundary.php
        TranscodingLatencyBudget.php

      Recommendations/
        RecommendationGatewayBoundary.php

      CDN/
        CdnInvalidatorBoundary.php
        CdnStalenessBudget.php

    how-this-works.md
```

---

## 5.19 ApiSurface

Purpose:

```text
Model API style at architecture level.
Runtime implementation belongs to V2 API suite.
```

Tree:

```text
components/SystemDesign/ApiSurface/
  System/
    PublicSurface/
      ApiSurfaceModel.php

    Capabilities/
      GraphQL/
        GraphQlApiModel.php
        GraphQlResolverRisk.php
        GraphQlNPlusOneRisk.php

      Rest/
        RestApiModel.php

      JsonApi/
        JsonApiModel.php

      OpenApi/
        OpenApiContractModel.php

      Webhooks/
        WebhookDeliveryModel.php

      Rpc/
        RpcApiModel.php

    how-this-works.md
```

---

# 6. Reference architecture standard

Every reference architecture must have:

```text
README.md
capacity.yaml
architecture.md
read-path.md
write-path.md
async-path.md
failure-modes.md
consistency.md
storage.md
caching.md
messaging.md
observability.md
security.md
evolution.md
tradeoffs.md
architecture-tests.yaml
scenarios.yaml
```

Optional:

```text
cost-model.md
api-surface.md
data-model.md
deployment.md
```

---

# 7. Reference architecture tree

```text
reference-architectures/
  url-shortener/
    README.md
    capacity.yaml
    architecture.md
    read-path.md
    write-path.md
    async-path.md
    failure-modes.md
    consistency.md
    storage.md
    caching.md
    messaging.md
    observability.md
    security.md
    evolution.md
    tradeoffs.md
    architecture-tests.yaml
    scenarios.yaml

  parking-lot/
  distributed-rate-limiter/
  news-feed/
  notification-system/
  payment-processor/
  booking-system/
  distributed-cache/
  chat-system/
  file-storage/
  search-system/
  video-metadata-system/
  media-transcoding-platform/
  marketplace-matching/
```

---

# 8. capacity.yaml standard

```yaml
system: url-shortener
traffic:
  requests_per_second: 100000
  reads_per_second: 99000
  writes_per_second: 1000
  read_write_ratio: 99
  peak_multiplier: 3
storage:
  growth_per_day: 1000000
  average_record_size_bytes: 512
  retention_days: 3650
cache:
  hit_ratio_target: 0.98
  miss_penalty_ms: 25
  stampede_protection_required: true
fanout:
  average_fanout_size: 1
  max_fanout_size: 1
queue:
  max_depth: 100000
  delay_budget_ms: 5000
  consumer_throughput_per_second: 5000
latency:
  p50_ms: 10
  p95_ms: 50
  p99_ms: 100
availability:
  slo: 99.99
  sla: 99.9
  failure_budget_minutes_per_month: 4.38
consistency:
  write_path: strong
  read_path: strong
  analytics: eventual
external_dependencies:
  cache:
    timeout_ms: 5
    retry: false
    fallback: database
  database:
    timeout_ms: 50
    retry: true
    circuit_breaker: true
```

---

# 9. Runnable system-design examples

```text
examples/system-design/
  url-shortener/
  distributed-rate-limiter/
  notification-system/
  payment-processor/
  news-feed/
```

Each example must include:

```text
README.md
capacity.yaml
architecture-tests.yaml
scenarios.yaml
config/
routes/
app/
tests/
```

Each example must prove:

```text
read path
write path
async path
failure modes
capacity assumptions
consistency model
storage model
cache strategy
queue/event strategy
architecture tests
load simulation
```

---

# 10. V3 CLI

```bash
php avax system-design:validate reference-architectures/url-shortener
php avax system-design:capacity reference-architectures/url-shortener/capacity.yaml
php avax system-design:simulate reference-architectures/url-shortener --scenario=redis-outage
php avax system-design:simulate reference-architectures/payment-processor --scenario=payment-provider-timeout
php avax system-design:check examples/system-design/url-shortener
php avax system-design:tradeoffs reference-architectures/news-feed
php avax system-design:report reference-architectures/url-shortener
```

---

# 11. V3 staged execution

## V3-00: Labs foundation

```text
[ ] Create labs/SystemDesignKit.
[ ] Add README.
[ ] Add experimental status.
[ ] Add first capacity.yaml parser spike.
[ ] Do not promote to components/SystemDesign yet.
```

## V3-01: Reference architecture schema

```text
[ ] Define required files.
[ ] Define capacity.yaml schema.
[ ] Define scenarios.yaml schema.
[ ] Define architecture-tests.yaml schema.
[ ] Add validator.
```

## V3-02: Capacity engine

```text
[ ] Capacity model.
[ ] Traffic model.
[ ] Storage growth model.
[ ] Cache hit model.
[ ] Queue depth model.
[ ] Latency budget model.
[ ] SLO/SLA/failure budget model.
```

## V3-03: Consistency engine

```text
[ ] Consistency taxonomy.
[ ] Delivery semantics.
[ ] Staleness budget.
[ ] Conflict resolution.
[ ] Projection lag model.
```

## V3-04: Messaging and CQRS engine

```text
[ ] Command/Event/Message/Job vocabulary.
[ ] Outbox/Inbox model.
[ ] Consumer/Subscriber model.
[ ] Projection model.
[ ] DeadLetterQueue model.
[ ] IdempotencyKey model.
```

## V3-05: Failure model

```text
[ ] Cache unavailable.
[ ] Database timeout.
[ ] Duplicate queue message.
[ ] Worker crash after side effect.
[ ] Projection delay.
[ ] Clock skew.
[ ] Partial write.
[ ] Network timeout.
[ ] Rate limit storm.
[ ] Hot key.
```

## V3-06: Architecture tests

```text
[ ] Hot path assertion.
[ ] Idempotent command assertion.
[ ] Projection consistency assertion.
[ ] Failure budget assertion.
[ ] Dead letter policy assertion.
[ ] External timeout/retry assertion.
[ ] Cache stampede assertion.
```

## V3-07: Scenario runner

```text
[ ] Scenario registry.
[ ] Scenario timeline.
[ ] Scenario result.
[ ] Scenario report.
[ ] CLI command.
```

## V3-08: URL Shortener proof

```text
[ ] Reference architecture.
[ ] Runnable example.
[ ] Capacity validation.
[ ] Redis outage simulation.
[ ] Cache stampede check.
[ ] Hot path assertion.
[ ] Async analytics assertion.
```

## V3-09: Distributed Rate Limiter proof

```text
[ ] Reference architecture.
[ ] Capacity validation.
[ ] Consistency tradeoff.
[ ] Hot key scenario.
[ ] Sharding/partitioning model.
```

## V3-10: Payment Processor proof

```text
[ ] Reference architecture.
[ ] Idempotency check.
[ ] Outbox check.
[ ] Payment timeout scenario.
[ ] Compensation/Saga validation.
```

## V3-11: Promotion to components/SystemDesign

Promotion allowed only when:

```text
[ ] at least two reference architectures validate
[ ] at least one runnable system-design example passes
[ ] at least one failure scenario catches a real architectural violation
[ ] architecture tests have meaningful assertions
[ ] public API classified as experimental or public
```

---

# 12. V3 acceptance criteria

V3 is GREEN only when:

```text
[ ] SystemDesign has public API.
[ ] capacity.yaml schema exists.
[ ] scenario schema exists.
[ ] architecture tests schema exists.
[ ] URL Shortener reference validates.
[ ] Distributed Rate Limiter or Payment Processor reference validates.
[ ] At least one runnable system-design example works.
[ ] At least three failure scenarios are executable.
[ ] Architecture tests can fail bad designs.
[ ] Tradeoff report can be generated.
[ ] Non-goals are documented.
[ ] SystemDesign docs mirror implementation.
```

---

# 13. Final V3 positioning

```text
AvaX V3 is an executable system-design framework for validating large application architectures.
```

Sharper:

```text
AvaX V3 does not just build applications.
AvaX V3 tests whether the architecture makes sense.
```
