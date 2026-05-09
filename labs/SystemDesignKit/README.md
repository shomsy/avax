# SystemDesignKit (labs/)

V3 Executable System Design Framework

## Status

**V3 CLOSED / GREEN** — All V3 stages complete.
**V2 Platform Baseline:** GREEN (all 72 components complete).
**Promotion:** Ready for promotion to `components/SystemDesign/` when V4 planning begins.

## Purpose

Model, validate, simulate, test, and explain large application architectures.

V3 does not just build applications.
V3 tests whether the architecture makes sense.

## V3-01 Schema Validation

### Schemas

- `schemas/capacity-schema.yaml` — Schema for capacity.yaml files
- `schemas/scenarios-schema.yaml` — Schema for scenarios.yaml files
- `schemas/architecture-tests-schema.yaml` — Schema for architecture-tests.yaml files

### Capabilities

- `System/Capabilities/SchemaValidation/NativeYamlParser.php` — Native PHP YAML parser
- `System/Capabilities/SchemaValidation/SchemaValidator.php` — Schema validation engine
- `System/Capabilities/SchemaValidation/SchemaValidationResult.php` — Validation result value object

### Flows

- `System/Flows/ValidateCapacitySchema/ValidateCapacitySchema.php` — Validate capacity.yaml
- `System/Flows/ValidateScenariosSchema/ValidateScenariosSchema.php` — Validate scenarios.yaml
- `System/Flows/ValidateArchitectureTestsSchema/ValidateArchitectureTestsSchema.php` — Validate architecture-tests.yaml

### Foundation

- `System/Foundation/Failure/SchemaParseException.php` — YAML parse failure
- `System/Foundation/Failure/SchemaValidationException.php` — Schema validation failure

## V3-02 Capacity Engine

### Aggregate Root

- `System/Capabilities/Capacity/CapacityModel.php` — capacity model (traffic, storage, cache, queue, latency,
  availability)

### Value Objects

- `System/Capabilities/Capacity/Traffic/RequestsPerSecond.php`
- `System/Capabilities/Capacity/Traffic/PeakTrafficMultiplier.php`
- `System/Capabilities/Capacity/Traffic/FanoutSize.php`
- `System/Capabilities/Capacity/Cache/CacheHitRatio.php`
- `System/Capabilities/Capacity/Cache/CacheStampedeRisk.php`
- `System/Capabilities/Capacity/Storage/StorageGrowth.php`
- `System/Capabilities/Capacity/Queue/QueueDepth.php`
- `System/Capabilities/Capacity/Queue/ConsumerThroughput.php`
- `System/Capabilities/Capacity/Latency/LatencyBudget.php`
- `System/Capabilities/Capacity/Availability/Slo.php`
- `System/Capabilities/Capacity/Availability/FailureBudget.php`

### Flows

- `System/Flows/ValidateCapacityModel/ValidateCapacityModel.php`
- `System/Flows/EstimateTrafficLoad/EstimateTrafficLoad.php`
- `System/Flows/EstimateStorageGrowth/EstimateStorageGrowth.php`
- `System/Flows/EstimateCacheEffectiveness/EstimateCacheEffectiveness.php`
- `System/Flows/EstimateQueuePressure/EstimateQueuePressure.php`
- `System/Flows/EstimateLatencyBudget/EstimateLatencyBudget.php`
- `System/Flows/EstimateFailureBudget/EstimateFailureBudget.php`

## V3-03 Consistency Engine

### Aggregate Root

- `System/Capabilities/Consistency/ConsistencyModel.php` — consistency model (profiles, delivery, staleness, conflicts,
  lag)

### Enums

- `System/Capabilities/Consistency/Profiles/ConsistencyModel.php` — Strong, Eventual, Causal, Session, etc.
- `System/Capabilities/Consistency/Delivery/DeliverySemantics.php` — AtMostOnce, AtLeastOnce, ExactlyOnceIllusion
- `System/Capabilities/Consistency/Conflicts/ConflictStrategy.php` — LastWriteWins, CRDT, VersionVector, etc.

### Value Objects

- `System/Capabilities/Consistency/Profiles/ConsistencyProfile.php`
- `System/Capabilities/Consistency/Delivery/DeliveryGuarantee.php`
- `System/Capabilities/Consistency/Staleness/StalenessBudget.php`
- `System/Capabilities/Consistency/Conflicts/ConflictResolution.php`
- `System/Capabilities/Consistency/Lag/ProjectionLag.php`
- `System/Capabilities/Consistency/Lag/ReplicationLag.php`

### Flows

- `System/Flows/ValidateConsistencyModel/ValidateConsistencyModel.php`
- `System/Flows/ExplainConsistencyTradeoff/ExplainConsistencyTradeoff.php`
- `System/Flows/DetectConsistencyRisk/DetectConsistencyRisk.php`
- `System/Flows/EstimateProjectionLag/EstimateProjectionLag.php`
- `System/Flows/EstimateReplicationLag/EstimateReplicationLag.php`
- `System/Flows/ResolveConflict/ResolveConflict.php`

## V3-04 Messaging & CQRS

### Aggregate Root

- `System/Capabilities/Messaging/MessagingModel.php` — messaging model (messages, consumers, broker, outbox, inbox, DLQ,
  retry, CQRS)

### Enums

- `System/Capabilities/Messaging/Types/MessageType.php` — Command, Event, Message, Job
- `System/Capabilities/Messaging/Acknowledgement/AcknowledgementPolicy.php` — Auto, Manual, Batch

### Value Objects

- `System/Capabilities/Messaging/Types/Message.php`
- `System/Capabilities/Messaging/Envelope/MessageEnvelope.php`
- `System/Capabilities/Messaging/Broker/Broker.php`
- `System/Capabilities/Messaging/Consumers/Consumer.php`
- `System/Capabilities/Messaging/Outbox/Outbox.php`
- `System/Capabilities/Messaging/Inbox/Inbox.php`
- `System/Capabilities/Messaging/DeadLetters/DeadLetterQueue.php`
- `System/Capabilities/Messaging/Retry/RetryPolicy.php`
- `System/Capabilities/Messaging/Cqrs/CommandSide.php`
- `System/Capabilities/Messaging/Cqrs/QuerySide.php`

### Flows

- `System/Flows/ValidateMessagingModel/ValidateMessagingModel.php`
- `System/Flows/DetectMessagingRisk/DetectMessagingRisk.php`

## V3-05 Runtime Integration Proof

Integration tests prove V3 models can describe V2 runtime behavior without duplicating runtime ownership:

- `tests/SystemDesignKit/Integration/V3MessagingModelDescribesV2MessageBusTest.php` — V3 MessagingModel describes V2
  CommandBus, EventBus, QueryBus behavior
- `tests/SystemDesignKit/Integration/V3RetryPolicyAlignsV2RetryTest.php` — V3 RetryPolicy aligns with V2 TaskRetryPolicy
  and Resilience RetryOptions
- `tests/SystemDesignKit/Integration/V3QueueDepthModelsV2TaskQueueTest.php` — V3 QueueDepth models V2 TaskQueue capacity
- `tests/SystemDesignKit/Integration/V3OutboxDlqModelsV2ResilienceTest.php` — V3 Outbox/DLQ model V2 Resilience
  OutboxStore/DeadLetterStore patterns
- `tests/SystemDesignKit/Integration/V3NoRuntimeDuplicationTest.php` — Static proof V3 classes are design-time only

### Ownership Boundary

| Concept           | V3 (design-time)                     | V2 (runtime)                                  |
|-------------------|--------------------------------------|-----------------------------------------------|
| Message types     | `MessagingModel`, `MessageType`      | `CommandBus`, `EventBus`, `QueryBus`          |
| Retry policy      | `RetryPolicy` (max retries, backoff) | `RetryBuilder`, `TaskRetryPolicy` (execution) |
| Queue capacity    | `QueueDepth`, `ConsumerThroughput`   | `TaskQueue`, `TaskRunner` (execution)         |
| Outbox            | `Outbox` (config VO)                 | `OutboxStore` (runtime interface)             |
| Dead letter queue | `DeadLetterQueue` (config VO)        | `DeadLetterStore` (runtime interface)         |
| Risk detection    | `DetectMessagingRisk` (analysis)     | `BusMiddleware` (runtime enforcement)         |

See: `EVIDENCE/plans/v3-runtime-integration-proof-plan.md`

## Public Surface

- `System/PublicSurface/SystemDesignKit.php` — static facade for all V3 operations (@experimental)

## Tree

```text
labs/SystemDesignKit/
  System/
    PublicSurface/
      SystemDesignKit.php
    Capabilities/
      Capacity/
        CapacityModel.php
        Availability/
          FailureBudget.php
          Slo.php
        Cache/
          CacheHitRatio.php
          CacheStampedeRisk.php
        Latency/
          LatencyBudget.php
        Queue/
          ConsumerThroughput.php
          QueueDepth.php
        Storage/
          StorageGrowth.php
        Traffic/
          FanoutSize.php
          PeakTrafficMultiplier.php
          RequestsPerSecond.php
      Consistency/
        ConsistencyModel.php
        Conflicts/
          ConflictResolution.php
          ConflictStrategy.php          (enum)
        Delivery/
          DeliveryGuarantee.php
          DeliverySemantics.php          (enum)
        Lag/
          ProjectionLag.php
          ReplicationLag.php
        Profiles/
          ConsistencyModel.php           (enum)
          ConsistencyProfile.php
        Staleness/
          StalenessBudget.php
      Messaging/
        MessagingModel.php
        Acknowledgement/
          AcknowledgementPolicy.php      (enum)
        Broker/
          Broker.php
        Consumers/
          Consumer.php
        Core/                           (removed — flattened to Types/)
        Cqrs/
          CommandSide.php
          QuerySide.php
        DeadLetters/
          DeadLetterQueue.php
        Envelope/
          MessageEnvelope.php
        Inbox/
          Inbox.php
        Outbox/
          Outbox.php
        Policies/                       (removed — split to Retry/ and Acknowledgement/)
        Retry/
          RetryPolicy.php
        Types/
          Message.php
          MessageType.php                (enum)
      SchemaValidation/
        NativeYamlParser.php
        SchemaValidator.php
        SchemaValidationResult.php
    Flows/
      DetectConsistencyRisk/
      DetectMessagingRisk/
      EstimateCacheEffectiveness/
      EstimateFailureBudget/
      EstimateLatencyBudget/
      EstimateProjectionLag/
      EstimateQueuePressure/
      EstimateReplicationLag/
      EstimateStorageGrowth/
      EstimateTrafficLoad/
      ExplainConsistencyTradeoff/
      ResolveConflict/
      ValidateArchitectureTestsSchema/
      ValidateCapacityModel/
      ValidateCapacitySchema/
      ValidateConsistencyModel/
      ValidateMessagingModel/
      ValidateScenariosSchema/
    Foundation/
      Failure/
        SchemaParseException.php
        SchemaValidationException.php
  schemas/
    capacity-schema.yaml
    scenarios-schema.yaml
    architecture-tests-schema.yaml
  examples/
    valid-capacity.yaml
    valid-scenarios.yaml
    valid-architecture-tests.yaml
    invalid-capacity-bad-values.yaml
    invalid-capacity-missing-sections.yaml
    invalid-scenarios-bad.yaml
    invalid-architecture-tests-bad.yaml
```

## Usage

```php
use Avax\Labs\SystemDesignKit\System\Flows\ValidateCapacitySchema\ValidateCapacitySchema;

$flow = new ValidateCapacitySchema();
$result = $flow->execute('reference-architectures/url-shortener/capacity.yaml');

if ($result->valid) {
    echo "Capacity model is valid.\n";
} else {
    foreach ($result->errors as $error) {
        echo "Error: {$error}\n";
    }
}
```

## MVP Scope

- [x] Capacity modeling (traffic, storage, cache, queue, latency, availability)
- [x] Schema validation for capacity.yaml, scenarios.yaml, architecture-tests.yaml
- [x] Consistency modeling (profiles, delivery, staleness, conflicts, replication lag, projection lag)
- [x] Messaging & CQRS modeling (messages, broker, outbox, inbox, DLQ, retry, ack, CQRS)
- [x] Reference architectures (URL shortener, e-commerce)
- [x] Failure simulation (6 failure modes)
- [x] Architecture tests (13 assertion types)
- [x] Scenario runner (27 scenario assertions)

## Promotion Criteria

Promotion to `components/SystemDesign/` requires:

- [x] V1 Kernel Green (PROVEN)
- [x] V2 platform baseline GREEN (PROVEN)
- [x] Canonical naming (no Core/, Models/, Policies/, BrokerModel/ buckets)
- [x] Runtime integration tests with V2 components (V3-05 PROVEN)
- [x] At least 2 reference architectures validate (URL shortener, e-commerce — both pass)
- [x] At least 1 runnable example passes (ReferenceArchitectureTest — 227 tests pass)
- [x] At least 3 failure scenarios catch real violations (6 failure modes, violations detected)
- [x] Architecture tests have meaningful assertions (13 assertion types)
- [x] Public API classified as @public (SystemDesignKit facade)

See: `EVIDENCE/plans/v3-reference-architecture-plan.md`
See: `EVIDENCE/avax-v3-executable-system-design-framework-plan.md`
