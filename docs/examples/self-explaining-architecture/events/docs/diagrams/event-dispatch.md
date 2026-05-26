# Event Dispatch Lifecycle

```mermaid
sequenceDiagram
    participant Flow
    participant Dispatcher
    participant Listener1
    participant Listener2
    participant Logger

    Flow->>Flow: User completes registration
    Flow->>Flow: Create UserRegistered event
    Flow->>Dispatcher: dispatch(UserRegistered)
    Dispatcher->>Dispatcher: Look up listeners for UserRegistered
    Dispatcher->>Listener1: handle(UserRegistered)
    Listener1->>Listener1: Send welcome email
    Dispatcher->>Listener2: handle(UserRegistered)
    Listener2->>Listener2: Update search index
    Dispatcher-->>Flow: All listeners notified
    Flow->>Flow: Return response to user
```

## Key Phases

1. **Event Creation**: Flow creates an event object with relevant data
2. **Dispatch**: Flow hands the event to the Dispatcher
3. **Fan-Out**: Dispatcher finds all registered listeners for this event type
4. **Execution**: Each listener runs (synchronously or queued)
5. **Completion**: Dispatcher returns control to the Flow

## Failure Handling

```mermaid
sequenceDiagram
    participant Flow
    participant Dispatcher
    participant BadListener
    participant Logger

    Flow->>Dispatcher: dispatch(UserRegistered)
    Dispatcher->>BadListener: handle(UserRegistered)
    BadListener-->>Dispatcher: THROWS exception
    Dispatcher->>Logger: Log listener failure
    Dispatcher->>Dispatcher: Continue to next listener
    Note over Dispatcher: BadListener failure does NOT break Flow
```

When a listener fails, the Dispatcher:
1. Logs the failure (with event type, listener class, exception)
2. Continues to the next listener (one bad listener doesn't break others)
3. Does NOT throw back to the Flow (the Flow's job is done)
4. Optionally retries failed listeners (configurable per listener)
