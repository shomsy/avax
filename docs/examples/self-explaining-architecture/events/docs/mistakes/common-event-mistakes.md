# Common Event Mistakes

## Mistake 1: Using Events as Commands

**Wrong:**
```php
// Event says "do this" — that's a command, not an event
class ProcessPayment {
    public function __construct(public int $orderId) {}
}

// Listener does the actual work
class ProcessPaymentListener {
    public function handle(ProcessPayment $event): void {
        $this->paymentGateway->charge($event->orderId);
    }
}
```

**Right:**
```php
// Event says "this happened" — past tense
class OrderPlaced {
    public function __construct(
        public int $orderId,
        public string $userId,
        public float $total,
    ) {}
}

// Flow does the work directly
class PlaceOrderFlow extends Flow {
    public function execute(): void {
        $this->paymentGateway->charge($order->id); // Flow does the work
        $this->events->dispatch(new OrderPlaced(
            $order->id, $order->userId, $order->total
        )); // Event announces it happened
    }
}
```

**Why it matters:** Events are notifications, not instructions. Using events as commands adds indirection and makes the system harder to understand. If you need something done, call a Capability. If you want to announce it happened, emit an event.

---

## Mistake 2: Letting Listener Exceptions Break the Flow

**Wrong:**
```php
class Dispatcher {
    public function dispatch(Event $event): void {
        foreach ($this->listeners[$event::class] as $listener) {
            $listener->handle($event); // If this throws, Flow breaks!
        }
    }
}
```

**Right:**
```php
class Dispatcher {
    public function dispatch(Event $event): void {
        foreach ($this->listeners[$event::class] as $listener) {
            try {
                $listener->handle($event);
            } catch (\Throwable $e) {
                $this->logger->error("Listener failed: {$listener::class}", [
                    'event' => $event::class,
                    'exception' => $e,
                ]);
                // Continue to next listener — do NOT rethrow
            }
        }
    }
}
```

**Why it matters:** Listeners are observers. If a welcome email fails, the user registration should still succeed. Listener failures must be logged but must not break the Flow.

---

## Mistake 3: Depending on Listener Execution Order

**Wrong:**
```php
// Listener A must run before Listener B — but order is not guaranteed!
#[Listen(UserRegistered::class, priority: 1)]
class SendWelcomeEmail { /* ... */ }

#[Listen(UserRegistered::class, priority: 2)]
class CreateUserAuditRecord { /* ... */ }
```

**Right:**
```php
// If order matters, combine into one listener or use a single Capability
#[Listen(UserRegistered::class)]
class OnUserRegistered {
    public function handle(UserRegistered $event): void {
        $this->sendWelcomeEmail($event->userId);
        $this->createUserAuditRecord($event->userId);
        // Order is explicit: email first, then audit
    }
}
```

**Why it matters:** Listener order is not guaranteed (especially for async events). If order matters, the coordination should be explicit in a single listener or a Capability, not implicit in listener registration.

---

## Mistake 4: Slow Synchronous Listeners

**Wrong:**
```php
#[Listen(OrderPlaced::class)]
class GenerateInvoicePdf {
    public function handle(OrderPlaced $event): void {
        // This takes 3-5 seconds — user waits for the PDF!
        $pdf = $this->pdfGenerator->generate($event->orderId);
        $this->storage->save("invoices/{$event->orderId}.pdf", $pdf);
    }
}
```

**Right:**
```php
#[Listen(OrderPlaced::class, async: true)]
class GenerateInvoicePdf {
    public function handle(OrderPlaced $event): void {
        // Queued for background execution — user doesn't wait
        $this->queue->push(new GenerateInvoicePdfJob($event->orderId));
    }
}
```

**Why it matters:** Synchronous listeners add to response time. If a listener is slow (PDF generation, email sending, external API calls), it should be async (queued for background execution).

---

## Mistake 5: Not Documenting Events

**Wrong:**
```php
class UserRegistered {
    public function __construct(public int $userId) {}
}
// No documentation. Other components don't know this event exists or what it contains.
```

**Right:**
```php
/**
 * Emitted when a new user completes registration.
 *
 * Listeners: SendWelcomeEmail, CreateUserAuditRecord, UpdateSearchIndex
 *
 * @property int $userId The ID of the newly registered user
 * @property string $userEmail The email address of the new user
 * @property string $registeredAt ISO 8601 timestamp of registration
 */
class UserRegistered {
    public function __construct(
        public int $userId,
        public string $userEmail,
        public string $registeredAt,
    ) {}
}
```

**Why it matters:** Events are a public API between components. Changing an event's shape breaks all listeners. Events must be documented so other teams know what to depend on.
