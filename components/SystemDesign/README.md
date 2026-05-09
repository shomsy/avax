# SystemDesign

## Owner

`components/SystemDesign/` — System Design Kit

## Responsibility

Models, validates, simulates, tests, and explains large application architectures.

V3 does not just build applications.
V3 tests whether the architecture makes sense.

## Public API

- `Avax\Components\SystemDesign\System\PublicSurface\SystemDesignKit` — Static facade for all operations.

## Key Flows

- `ValidateCapacityModel` — Validates capacity.yaml files (schema + model)
- `RunScenarios` — Runs failure/load/consistency scenarios against a capacity model
- `RunArchitectureTests` — Runs architecture test assertions against system models
- `RunFailureSimulations` — Applies failure modes and detects violations

## Key Capabilities

- `CapacityModel` — Traffic, storage, cache, queue, latency, availability modeling
- `ConsistencyModel` — Consistency profiles, delivery guarantees, staleness budgets
- `MessagingModel` — Message vocabulary, outbox/inbox, DLQ, retry, CQRS
- `Scenario` — Named scenarios with step-by-step evaluation
- `ArchitectureTest` — Architecture assertion evaluation
- `FailureSimulation` — Failure mode simulation with violation detection

## Status

Production-ready. Promoted from labs/SystemDesignKit.
