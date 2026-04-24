---
title: Saga-todo
owner: foundation
last_reviewed: 2026-04-24
classification: internal
---

# Saga ToDo

## Milestone 1 Complete

- Foundation/ApplicationWorkflow/Saga exists outside DataLayer.
- Saga owns long-running workflow state, steps, compensation, recovery, and idempotency.
- Saga state and events are represented by explicit files under `StoreSagaState`.
- Saga start and step execution leave state and event evidence.
- Diagnostics-safe timeline/report code exists under `InspectSaga`.

## Next Capabilities

- Add persistent Saga store backed by DataLayer transaction and outbox boundaries.
- Add compensation execution tests for reverse side-effect order.
- Add resume from event history after process crash.
- Add retry policy that cannot duplicate side effects.
- Add message bus integration through `ConfigureSagaRuntime`.