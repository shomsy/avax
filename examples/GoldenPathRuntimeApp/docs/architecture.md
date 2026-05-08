# Runtime Architecture — Webhook Ingestion Pipeline

## Overview

This example proves that AvaX can run a realistic production-style application
using canonical component architecture, V2 runtime engines, and V3 modeling support.

## Architecture Diagram

```
┌─────────────────────────────────────────────────────────────────────┐
│                     Webhook Ingestion Pipeline                      │
│                                                                     │
│  ┌─────────────────────────────────────────────────────────────┐   │
│  │                        HTTP Layer                            │   │
│  │  ┌───────────┐    ┌────────────────────┐    ┌────────────┐  │   │
│  │  │ CorrelID  │───►│ Middleware Pipeline │───►│  Router    │  │   │
│  │  │ Middleware│    │ (reverse order)     │    │  /health   │  │   │
│  │  │           │    │                     │    │  /ingest   │  │   │
│  │  └───────────┘    └────────┬───────────┘    │  /status   │  │   │
│  │                            │                └──────┬─────┘  │   │
│  └────────────────────────────┼───────────────────────┼────────┘   │
│                               │                       │            │
│  ┌────────────────────────────┼───────────────────────┼────────┐   │
│  │                    Message Bus                     │        │   │
│  │  ┌────────────┐  ┌─────────────┐  ┌────────────┐  │        │   │
│  │  │ CommandBus │  │  QueryBus   │  │  EventBus  │  │        │   │
│  │  │ dispatch() │  │  query()    │  │ publish()  │  │        │   │
│  │  └──────┬─────┘  └──────┬──────┘  └─────┬──────┘  │        │   │
│  │         │               │               │         │        │   │
│  │  ┌──────┴───────────────┴───────────────┴──────┐  │        │   │
│  │  │              Handlers                        │  │        │   │
│  │  │  IngestWebhook  GetWebhookStatus  Webhook*   │  │        │   │
│  │  └──────────────────────┬───────────────────────┘  │        │   │
│  └─────────────────────────┼──────────────────────────┘        │   │
│                            │                                     │
│  ┌─────────────────────────┼──────────────────────────────────┐ │
│  │                    Queue / Jobs                             │ │
│  │  ┌──────────────┐  ┌──────────────────┐  ┌──────────────┐ │ │
│  │  │ Queue::push()│─►│ ProcessWebhookJob│─►│CircuitBreaker│ │ │
│  │  │ Queue::pop() │  │ DeadLetterNotif  │  │ Timeout      │ │ │
│  │  │ Queue::rel() │  │                  │  │ Fallback     │ │ │
│  │  └──────────────┘  └──────────────────┘  └──────────────┘ │ │
│  └────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                   Observability                              │ │
│  │  Logger → StructuredLogRecord → withRequestId(correlation)  │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                   V3 Design-Time Validation                  │ │
│  │  CapacityModel → EstimateQueuePressure, EstimateTrafficLoad │ │
│  │  MessagingModel → DetectMessagingRisk, ValidateMessaging    │ │
│  └─────────────────────────────────────────────────────────────┘ │
│                                                                   │
│  ┌─────────────────────────────────────────────────────────────┐ │
│  │                   Runtime Lifecycle                          │ │
│  │  Boot → Open Scope → Handle → Close Scope → Reset State    │ │
│  │  WorkerLoop: receive → handle → reset per request → stop   │ │
│  └─────────────────────────────────────────────────────────────┘ │
└───────────────────────────────────────────────────────────────────┘
```

## Component Ownership

| Layer         | Component               | Responsibility                                     |
|---------------|-------------------------|----------------------------------------------------|
| HTTP          | CorrelationIdMiddleware | Inject/propagate X-Correlation-ID                  |
| HTTP          | Router                  | Exact URI route matching, dispatch to action       |
| MessageBus    | CommandBus              | Single-handler command dispatch with middleware    |
| MessageBus    | QueryBus                | Single-handler query dispatch                      |
| MessageBus    | EventBus                | Multi-handler event publish                        |
| Queue         | Queue                   | In-memory job queue with push/pop/process/release  |
| Resilience    | CircuitBreaker          | Open/Closed/HalfOpen state protection              |
| Resilience    | Timeout                 | Post-execution timeout enforcement                 |
| Resilience    | Fallback                | Callable fallback chain execution                  |
| Observability | Logger                  | Structured log records with correlation IDs        |
| Runtime       | Runtime                 | State, context, scopes, reset registry, worker     |
| V3            | CapacityModel           | Traffic, storage, cache, queue, latency modeling   |
| V3            | MessagingModel          | Message types, broker, outbox, DLQ, retry modeling |

## Data Flow

1. **HTTP Request** arrives → CorrelationIdMiddleware extracts/generates ID
2. **Route match** → Router dispatches to registered action
3. **Action** returns array → HandleIncomingHttp normalizes to JSON response
4. **Command dispatch** → MessageBus::dispatch routes to handler
5. **Event publish** → MessageBus::publish notifies all registered listeners
6. **Job enqueue** → Queue::push schedules async processing
7. **Job process** → Queue::process executes due jobs with resilience patterns
8. **Scope close** → HandleIncomingHttp closes request scope in finally
9. **State reset** → StateResetRegistry resets all registered components
10. **V3 validation** → CapacityModel and MessagingModel validate architecture
