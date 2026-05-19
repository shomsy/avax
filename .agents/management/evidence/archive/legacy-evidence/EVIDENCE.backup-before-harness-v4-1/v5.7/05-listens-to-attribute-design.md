# V5.7 — 05 #[ListensTo] Attribute Design

**Date:** 2026-05-12

## Attribute Declaration

```php
namespace Avax\Components\Operations\Events\System\Foundation;

use Attribute;

#[Attribute(Attribute::TARGET_CLASS)]
final readonly class ListensTo
{
    public function __construct(
        public string $event,
        public int $priority = 0,
    ) {}
}
```

## Usage

```php
use Avax\Components\Operations\Events\System\Foundation\ListensTo;

#[ListensTo(UserRegistered::class)]
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

## With Priority

```php
#[ListensTo(UserRegistered::class, priority: 100)]
final readonly class AuditUserRegistration
{
    public function __invoke(UserRegistered $event): void
    {
        // Audit runs first (priority 100 > default 0)
    }
}

#[ListensTo(UserRegistered::class, priority: 50)]
final readonly class SendWelcomeEmail
{
    public function __invoke(UserRegistered $event): void
    {
        // Email runs after audit (priority 50 < 100)
    }
}
```

## Decisions

### Target: Class-level only

**Decision:** Class-level attributes only for V5.7.

**Why:**

- Class-level is simpler and covers the vast majority of use cases
- Each listener class handles one event type — clean ownership
- Method-level attributes add complexity: which method to call? `__invoke` or named method?
- Method-level is a ROADMAP item for V5.7+ if genuinely needed
- Framework clarity: one class = one event = one reaction

### Parameters

| Parameter  | Type           | Default  | Purpose                                 |
|------------|----------------|----------|-----------------------------------------|
| `event`    | `class-string` | required | The event class this listener reacts to |
| `priority` | `int`          | `0`      | Execution order (higher = earlier)      |

### Reserved for future (NOT active in V5.7)

| Parameter     | Purpose                          | Status         |
|---------------|----------------------------------|----------------|
| `mode`        | sync/async execution mode        | ROADMAP        |
| `channel`     | Listener group/channel filtering | ROADMAP        |
| `afterCommit` | Defer until transaction commit   | ROADMAP (V5.8) |

## Rules

1. Attribute is **declaration only** — it does nothing at runtime by itself.
2. Attribute scanning happens **only in compile/build phase**, never in hot path.
3. Runtime dispatch **never reflects attributes**.
4. Attribute and DSL declarations compile into the **same registry**.
5. Decorative attributes are **forbidden** — `#[ListensTo]` must result in actual listener registration.
6. The compiled listener registry includes attribute-discovered listeners at boot time.

## Method-Level Attributes — V5.7 Decision

**Decision: OUT OF SCOPE for V5.7.**

If a class needs to listen to multiple events, create multiple listener classes.

```php
// V5.7 approach — separate classes
#[ListensTo(UserRegistered::class)]
final readonly class AuditUserRegistration { ... }

#[ListensTo(UserRegistered::class)]
final readonly class SendWelcomeEmail { ... }

// Method-level (ROADMAP) — NOT in V5.7
final class UserEventListener {
    #[ListensTo(UserRegistered::class)]
    public function onRegistered(UserRegistered $e) { ... }

    #[ListensTo(UserDeleted::class)]
    public function onDeleted(UserDeleted $e) { ... }
}
```

**Why:**

- Single-responsibility: one class = one reaction
- Simpler compilation: scan classes, not class+methods
- Easier testing: one listener class, one test
- Clean dependency injection per listener
- If multi-event listening is needed, the class can listen to a parent event type or use composition

## Acceptance Criteria for Future Implementation

- [ ] `#[ListensTo]` attribute discovered in compile path
- [ ] Compiled listener registry includes attribute-discovered listeners
- [ ] Dispatch invokes attribute-registered listeners
- [ ] Tests prove no hot-path reflection
- [ ] Tests prove compile-time discovery
