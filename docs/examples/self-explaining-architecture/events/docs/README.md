# Events Component

## What This Component Owns

The Events component owns the publish-subscribe mechanism within AvaX. It lets parts of the system say "something happened" without knowing who cares about it.

It is the system's nervous system. When something important happens, events carry the news to anyone who's listening.

It does NOT own business logic. Events are notifications, not actions.

## Where It Belongs

```
components/Events/
  System/
    PublicSurface/     # Event dispatcher, event bus, listener registry
    Flows/             # Event-specific flows: dispatch, fan-out, event sourcing
    Capabilities/      # Reusable event behavior: listener registration, filtering
    Configuration/     # Listener registration, event handler assembly
    Foundation/        # Tiny event primitives: Event, Listener, EventId
```

## How It Works (The Simple Version)

1. Something important happens (a user registered, an order was placed, a file was uploaded)
2. The Flow says "I'm done with my part. Hey, anyone who cares — a user just registered!"
3. All registered listeners hear the event and do their own thing
4. The original Flow doesn't wait for listeners (unless it's a synchronous event)
5. The original Flow doesn't care WHO is listening — it just announces

This is called **loose coupling**. The sender doesn't know the receivers. They communicate through events.

## What It Does NOT Own

- Business logic (events notify, they don't act)
- Command execution (events are "something happened", not "do this")
- Message queue transport (that's the Queue component for cross-service events)
- State changes (events describe changes that already happened, they don't make changes)

## Synchronous vs Asynchronous Events

**Synchronous** (happens now, in the same request):
- "Before user is saved" — listeners can modify the data before save
- "After user is saved" — listeners send welcome email, update search index, log audit trail
- The request waits for all listeners to finish

**Asynchronous** (happens later, in background):
- "Order placed" — listeners generate invoice, notify warehouse, update analytics
- The request completes immediately
- Listeners run in background workers or queue

## How It Fails

- Listener failures must NOT break the main Flow (listeners are observers, not required steps)
- Failed listeners are logged and retried (if configured)
- Event dispatch must never throw listener exceptions back to the caller (fail closed for the event, not the Flow)
- Event ordering is NOT guaranteed for async events (do not depend on listener execution order)

## How It Is Tested

- Unit tests for event dispatch, listener registration
- Integration tests for synchronous event handling
- Negative tests for listener failures, circular event dependencies
- Contract tests for event payloads (events are a public API between components)
