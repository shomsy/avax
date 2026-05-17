# V5.7 — 03 Event Model Decision

**Date:** 2026-05-12

## 1. What is an Event?

An event is a **plain readonly object** representing something that **already happened**.

```php
final readonly class UserRegistered
{
    public function __construct(
        public string $userId,
        public string $email,
        public DateTimeImmutable $registeredAt,
    ) {}
}
```

**Rules:**

- Named in past tense: `UserRegistered`, `OrderPaid`, `InvoiceIssued`, `PaymentFailed`
- Plain readonly objects — no parent class, no interface required by default
- Should NOT require `EventInterface` by default
- May optionally implement `Psr\EventDispatcher\StoppableEventInterface` for PSR-14 stoppable events
- Carries all data listeners need to react

**Good event names:**

- `UserRegistered`
- `OrderPaid`
- `PasswordChanged`
- `PaymentFailed`
- `CacheWarmed`
- `JobCompleted`

**Bad event names (these are commands/actions, not events):**

- `RegisterUser`
- `PayOrder`
- `ChangePassword`
- `WarmCache`

## 2. What is a Listener?

A listener is a **callable reaction** to an event.

**Preferred form: invokable class**

```php
final readonly class SendWelcomeEmail
{
    public function __construct(
        private MailerInterface $mailer,
    ) {}

    public function __invoke(UserRegistered $event): void
    {
        $this->mailer->to($event->email)->send(new WelcomeEmail($event->userId));
    }
}
```

**Supported forms:**

- Invokable classes: `SendWelcomeEmail::class` — preferred
- Closures: `fn (UserRegistered $e) => ...` — for simple inline reactions
- Class@method: `[LoggerService::class, 'onUserRegistered']` — for legacy compatibility

**Rules:**

- Should NOT require `ListenerInterface` by default
- Invokable classes are the canonical form
- Listeners receive the event object as their only argument
- Listeners may have dependencies resolved through container

## 3. What are Contracts?

Contracts exist for the **plumbing**, not for user events or listeners.

| Contract                   | Purpose                                                     | Location                       |
|----------------------------|-------------------------------------------------------------|--------------------------------|
| `EventEmitter`             | Entry point — accepts event objects and dispatches them     | Operations/Events Foundation   |
| `EventDispatcher`          | Coordinates listener execution for a given event            | Operations/Events Capabilities |
| `ListenerProvider`         | Returns iterable listeners for an event (PSR-14 compatible) | Operations/Events Capabilities |
| `ListenerRegistry`         | Stores listener declarations with priority and metadata     | Operations/Events Foundation   |
| `CompiledListenerRegistry` | Pre-compiled, reflection-free listener resolution           | Operations/Events Foundation   |
| `ListenerDeclaration`      | Immutable record of a listener registration                 | Operations/Events Foundation   |
| `ListenerCompiler`         | Compiles declarations into compiled registry                | Operations/Events Capabilities |
| `Psr14Adapter`             | Adapts AvaX event system to PSR-14 interfaces               | Operations/Events Capabilities |

**User-facing contracts:**

- Users do NOT implement `EventInterface` for their events
- Users do NOT implement `ListenerInterface` for their listeners
- Users create plain readonly event objects
- Users create invokable listener classes
- Users use `#[ListensTo]` attribute or `onEvent()->do()` DSL

## 4. Stoppable Events

**Decision:** Support PSR-14 `StoppableEventInterface` semantics.

```php
final readonly class ValidationFailed implements \Psr\EventDispatcher\StoppableEventInterface
{
    public function __construct(
        public string $field,
        public string $reason,
    ) {}

    public function isPropagationStopped(): bool
    {
        // Events implementing StoppableEventInterface are stoppable by design.
        // The dispatcher checks this between each listener.
        return false; // default — listeners may set a flag on mutable variant
    }
}
```

**Rules:**

- PSR-14 semantics when `psr/event-dispatcher` is available
- AvaX defines its own `StoppableEvent` attribute as alternative if PSR dependency is optional
- No duplicate stoppable semantics — use PSR when available, fall back to AvaX attribute
- Stoppable events are the exception, not the default — most events are facts, not control flow

## 5. Event Naming

**Convention:** Past tense, fact-oriented, domain language.

| Good              | Bad              | Why                         |
|-------------------|------------------|-----------------------------|
| `UserRegistered`  | `RegisterUser`   | Past fact vs action request |
| `OrderPaid`       | `PayOrder`       | Past fact vs action request |
| `PasswordChanged` | `ChangePassword` | Past fact vs action request |
| `PaymentFailed`   | `FailPayment`    | Past fact vs action request |
| `CacheWarmed`     | `WarmCache`      | Past fact vs action request |

## 6. Command vs Event vs Listener

| Concept      | Tense      | Purpose                                | Example                                                   |
|--------------|------------|----------------------------------------|-----------------------------------------------------------|
| **Command**  | Imperative | Ask the system to DO something         | `RegisterUser`, `CancelOrder`, `SendInvoice`              |
| **Event**    | Past       | Record that something ALREADY HAPPENED | `UserRegistered`, `OrderCancelled`, `InvoiceSent`         |
| **Listener** | Reactive   | React to an event                      | `SendWelcomeEmail`, `UpdateUserProjection`, `NotifyAdmin` |

**Flow:**

```
Command → (action happens) → Event → Listener reacts
```

Commands are handled by command handlers (MessageBus/CommandBus).
Events are emitted after the action completes.
Listeners react to events asynchronously or synchronously.
