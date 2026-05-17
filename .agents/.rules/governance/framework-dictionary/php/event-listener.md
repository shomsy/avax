# Term
EventListener

# Classification
Event Dispatching Component

# Purpose
Handles a single specific event type when dispatched by an event system, executing a focused reaction such as sending a notification, logging activity, or triggering a downstream process.

# Why Allowed
EventListener is a core pattern in PHP event-driven architectures. Laravel, Symfony, PSR-14 implementations, and Zend all use listeners to react to events. Unlike subscribers (which handle multiple events), a listener focuses on one event type with a single `handle` or `__invoke` method. This single-responsibility design makes listeners easy to test, compose, and reason about. The pattern is well-defined: receive an event, perform a reaction, optionally return nothing or a response.

# Allowed Contexts
- Single-event reactions (SendWelcomeEmail on UserRegistered)
- Side effects triggered by domain events
- Notifications, alerts, and messaging after state changes
- Audit logging and metrics emission
- Cache warming or invalidation on data changes

# Forbidden Misuse
- As a place to put core business logic that should be in the flow that raised the event
- Listeners that modify the event state in ways that affect other listeners
- Listeners with unbounded side effects (infinite loops, unbounded retries)
- Listeners that silently swallow exceptions instead of failing or reporting

# Ecosystem References
- https://laravel.com/docs/events#defining-listeners
- https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-listener
- https://www.php-fig.org/psr/psr-14/

# Allowed Patterns
- SendPasswordResetNotification
- InvalidateProductCache
- LogOrderPlacedEvent
- UpdateInventoryAfterPurchase

# Forbidden Patterns
- HandleEverythingListener (violates single responsibility)
- GenericEventListener (too vague — which event?)
- Listener/Listener (recursive, meaningless)
