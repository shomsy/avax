# Event

## What It Is

An Event is an immutable fact that something happened in the system.

It carries data about what happened:
- WHAT happened (the event type: UserRegistered, OrderPlaced, FileUploaded)
- WHEN it happened (a timestamp)
- WHO caused it (a user ID, system ID, or "anonymous")
- DETAILS (any data relevant to the event: user name, order total, file path)

Events are like newspaper headlines. They announce news. They don't DO anything — they just tell people what happened.

## What It Is NOT

- It is NOT a command (a command says "do this", an event says "this happened")
- It is NOT a request (a request expects a response, an event is one-way)
- It is NOT a database record (events are transient notifications, not permanent records — unless you're doing event sourcing)
- It is NOT a callback (a callback is a function reference, an event is a data object)

## Common Confusion

**Confusion**: "I should use events to tell other components what to do."
**Reality**: Events announce what DID happen. Commands tell components what TO DO. If you need something done, call a Capability directly. If you want to announce it happened, emit an event. Event = past tense. Command = future tense.

**Confusion**: "Listeners should be able to modify the event data."
**Reality**: Events are immutable facts. You can't change the past. If a listener needs to change something, it should emit a NEW event or call a Capability. Some frameworks allow "before" events with mutability, but this should be clearly documented and limited.

**Confusion**: "I don't need to document events — they're just internal notifications."
**Reality**: Events are a PUBLIC API between components. When component A emits `UserRegistered`, component B's listener depends on that event's shape and data. Changing the event breaks component B. Events must be documented and versioned.
