# Term
Event

# Classification
Pub/Sub Messaging Component

# Purpose
Represents a domain occurrence that interested parties can subscribe to and react to. An event captures a fact that something has happened in the system, enabling loose coupling between the component that raised the event and the components that respond to it.

# Why Allowed
Events are fundamental to event-driven architecture and are used universally in modern PHP frameworks. Laravel provides a comprehensive events and listeners system where events represent domain occurrences and listeners react to them, supporting synchronous and queued handling, event broadcasting, and event discovery. Symfony's EventDispatcher component is a mature implementation of the Mediator pattern, widely used throughout the framework lifecycle (kernel events, form events, console events). PSR-14 (Event Dispatcher) standardizes event dispatching across PHP implementations, defining a common interface for event dispatchers and event objects. Event sourcing patterns (broadway, event-store) use events as the source of truth for state changes. An event has clear characteristics: it represents a fact that has already happened (past tense), it is immutable, it carries data about the occurrence, and it does not expect a return value from its listeners. It is not a command (which represents an intent to do something) — it is a notification that something was done.

# Allowed Contexts
- Event dispatching and pub/sub systems
- Domain events in DDD (something significant happened in the domain)
- Integration events for cross-component or cross-service communication
- Lifecycle events (boot, shutdown, request handled, response sent)
- Event sourcing (events as the authoritative state log)
- Broadcasting events to external systems (WebSockets, webhooks)

# Forbidden Misuse
- As a generic notification system for coupling unrelated components
- As a dumping ground for messages, commands, and requests mixed together
- Naming state-mutation intents as "events" when they should be commands
- Creating an Events/ folder for random functions or callbacks
- Using events to return data or expect synchronous responses from listeners

# Ecosystem References
- https://laravel.com/docs/events
- https://symfony.com/doc/current/event_dispatcher.html
- https://www.php-fig.org/psr/psr-14/
- https://eventstore.com/docs/introduction/event-sourcing

# Allowed Patterns
- UserRegistered
- OrderPlaced
- CacheCleared
- PaymentProcessed
- ArticlePublished
- UserPasswordChanged

# Forbidden Patterns
- Events/ (as folder for random functions, callbacks, or non-event code)
- EventManager (managers are forbidden; use EventDispatcher per PSR-14)
- HandleEvent (events are dispatched, not handled directly — use listeners)
- GenericEvent (too vague — what happened?)
