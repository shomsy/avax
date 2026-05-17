# Term
EventSubscriber

# Classification
Event Dispatching Component

# Purpose
Registers itself with an event dispatcher to listen to multiple related events, grouping event handling logic into a single cohesive unit rather than scattering listeners across separate classes.

# Why Allowed
EventSubscriber is a standard pattern in PHP frameworks. Symfony defines it as a formal interface (`EventSubscriberInterface`) with a `getSubscribedEvents()` method that maps events to handlers. Laravel supports the same pattern through subscriber classes registered in the `EventServiceProvider`. The subscriber pattern is distinct from individual listeners — it bundles related event reactions together, making it easier to see all reactions to a domain concept in one place. It is a well-defined contract, not a generic event bucket.

# Allowed Contexts
- Grouping multiple related event reactions for a single domain concept
- Cross-cutting event reactions (logging, auditing, notifications)
- Plugin or module event registration
- System-level event handling (cache invalidation, stats updates)
- Package-level event subscription with clear boundaries

# Forbidden Misuse
- As a catch-all for unrelated events from different domains
- As a place to put business logic that should be in flows or capabilities
- Subscribers that mutate events in ways that break other subscribers
- Subscribers with side effects that are not idempotent or retry-safe

# Ecosystem References
- https://symfony.com/doc/current/event_dispatcher.html#creating-an-event-subscriber
- https://laravel.com/docs/events#event-subscribers
- https://www.php-fig.org/psr/psr-14/

# Allowed Patterns
- UserActivityEventSubscriber
- OrderLifecycleEventSubscriber
- CacheInvalidationEventSubscriber
- AuditLogEventSubscriber

# Forbidden Patterns
- GlobalEventSubscriber (unbounded scope)
- EverythingSubscriber (catch-all anti-pattern)
- EventSubscriber/EventSubscriber (recursive, meaningless)
