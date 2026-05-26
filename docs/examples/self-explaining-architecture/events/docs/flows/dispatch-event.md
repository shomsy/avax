# Flow: Dispatch an Event

## Description

This flow describes the complete event dispatch lifecycle from event creation to listener notification.

## Steps

1. **Event Creation**
   - A Flow completes its primary behavior (e.g., user registration)
   - The Flow creates an event object with relevant data
   - The event is immutable (it describes a fact that cannot be changed)

2. **Dispatch**
   - The Flow calls `Dispatcher::dispatch($event)`
   - The Dispatcher looks up all registered listeners for this event type
   - The Dispatcher uses the compiled dispatch table (no runtime reflection)

3. **Listener Fan-Out**
   - Each listener is invoked with the event object
   - Synchronous listeners run in the current request
   - Async listeners are queued for background execution

4. **Listener Execution**
   - Each listener handles the event independently
   - Listener failures are caught and logged
   - Failed listeners do NOT break other listeners or the Flow
   - Failed listeners may be retried (configurable)

5. **Completion**
   - Dispatcher returns control to the Flow
   - Flow continues with response (listeners don't block the response for sync events)
   - Async listeners complete independently in background workers

## Negative Paths

| What Goes Wrong | Response | Where It's Handled |
|----------------|----------|-------------------|
| No listeners registered | Event dispatched, nothing happens (not an error) | Dispatcher |
| Listener throws exception | Logged, next listener runs | Dispatcher |
| All listeners fail | All logged, Flow still succeeds | Dispatcher |
| Async queue is down | Logged, event is lost OR stored for retry (configurable) | Queue capability |
| Circular event dependency | BLOCKER at boot time (detected in dispatch table validation) | Configuration |
| Event payload is too large | Logged, consider reducing payload size | Dispatcher |

## Key Principles

1. Events are notifications, not instructions
2. Listener failures must not break the Flow
3. Events are a public API between components — document them
4. Async listeners must handle failure gracefully (retry or discard)
