# Listener

## What It Is

A Listener is a piece of code that runs when a specific event happens.

Think of it like a firefighter's radio. The firefighter (Listener) waits for dispatch calls (Events). When a call comes in about a fire (FireDetected event), the firefighter responds (drives the truck, fights the fire).

Listeners:
- Register interest in specific event types
- Receive the event data when it's dispatched
- Do their own thing (send email, update search index, log audit trail)
- Do NOT return data to the emitter
- Do NOT block the emitter (for async events)

## What It Is NOT

- It is NOT the main Flow (the Flow handles the user's request; the listener handles the aftermath)
- It is NOT middleware (middleware runs BEFORE the Flow; listeners run AFTER or DURING)
- It is NOT a required step (if a listener fails, the main Flow should still succeed)
- It is NOT a way to add business logic to a Flow (business logic belongs in the Flow or a Capability)

## Common Confusion

**Confusion**: "My listener validates business rules and throws if they fail."
**Reality**: Listeners should NOT block the main Flow by throwing exceptions. If a listener's work is required for the operation to succeed, it belongs in the Flow, not a listener. Listeners are observers, not gatekeepers.

**Confusion**: "I can depend on the order that listeners run."
**Reality**: Listener execution order is NOT guaranteed (especially for async events). If listener B must run after listener A, they should not be separate listeners — they should be one listener or a coordinated Capability.

**Confusion**: "Listeners can be slow because they run in the background."
**Reality**: Synchronous listeners run in the same request and add to response time. If a listener is slow (sending emails, generating PDFs, calling external APIs), it should be async (queued for background execution).
