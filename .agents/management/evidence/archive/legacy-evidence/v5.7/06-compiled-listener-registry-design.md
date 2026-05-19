# V5.7 — 06 Compiled Listener Registry Design

**Date:** 2026-05-12

## Core Concepts

### ListenerDeclaration

```php
final readonly class ListenerDeclaration
{
    public function __construct(
        public string $eventClass,
        public string|callable $listener,
        public int $priority = 0,
        public string $sourceType = 'dsl',    // 'dsl' | 'attribute' | 'config' | 'future'
        public string $sourceFile = '',       // File where declaration originated
    ) {}
}
```

### ListenerPriority (enum)

```php
enum ListenerPriority: int
{
    case Critical = 1000;
    case High     = 100;
    case Normal   = 0;
    case Low      = -100;
    case Deferred = -1000;
}
```

### ListenerExecutionMode (enum)

```php
enum ListenerExecutionMode: string
{
    case Sync = 'sync';       // V5.7 — only sync supported
    case Async = 'async';     // ROADMAP
    case AfterCommit = 'after_commit'; // ROADMAP (V5.8)
}
```

### CompiledListener

```php
final readonly class CompiledListener
{
    public function __construct(
        public string $eventClass,
        public string $listenerClass,
        public int $priority,
        public ListenerExecutionMode $mode,
        public string $sourceType,
    ) {}
}
```

### CompiledListenerRegistry

```php
final class CompiledListenerRegistry
{
    /** @var array<string, list<CompiledListener>> */
    private array $listeners = [];

    public function register(CompiledListener $listener): void
    {
        $this->listeners[$listener->eventClass][] = $listener;
    }

    /**
     * @return list<CompiledListener> Sorted by priority descending
     */
    public function getListenersFor(string $eventClass): array
    {
        if (! isset($this->listeners[$eventClass])) {
            return [];
        }

        $sorted = $this->listeners[$eventClass];
        usort($sorted, static fn ($a, $b) => $b->priority <=> $a->priority);
        return $sorted;
    }

    public function freeze(): void
    {
        // Mark registry as immutable after compilation
        // Future registrations throw
    }
}
```

### CompileEventListeners (Flow)

Scans `#[ListensTo]` attributes across registered listener paths and compiles them into `CompiledListenerRegistry`.

**Input:** Listener paths (config), DSL registrations, attribute declarations
**Output:** Frozen `CompiledListenerRegistry`

### ListenerCompiler (Capability)

Converts `ListenerDeclaration` objects into `CompiledListener` objects.

### ResolveEventListeners (Capability)

Resolves listener class strings to actual listener instances via container/callable resolver.

### InvokeEventListener (Capability)

Executes a single compiled listener against an event object.

## Runtime Dispatch Path

```
emit($event)
  → EventEmitter
  → EventDispatcher
  → ListenerProvider
  → CompiledListenerRegistry
  → ResolveEventListeners (container resolution)
  → InvokeEventListener
  → Return event
```

**Rules:**

1. NO reflection in runtime dispatch path.
2. NO attribute scanning on every event emit.
3. Compiled registry is built at boot time, frozen before first dispatch.
4. If disk persistence is not implemented in V5.7, the compiled registry is in-memory only.
5. Disk persistence is ROADMAP — mark it, do not fake it.

## Disk Persistence — V5.7 Decision

**Decision:** In-memory compiled registry only for V5.7.

**Why:**

- V5.7 is the first pass — get the API right before optimizing persistence
- Existing compiled metadata infrastructure (from FailureBoundary and DataTransfer) may be reusable
- Adding disk persistence now risks API design mistakes
- ROADMAP: integrate with existing `CompileClassAttributes` infrastructure from DataTransfer

## Registry Supports

| Feature                 | V5.7            | Notes                             |
|-------------------------|-----------------|-----------------------------------|
| Event class             | YES             | class-string key                  |
| Listener class/callable | YES             | class-string or callable          |
| Priority                | YES             | int, higher = earlier             |
| Source type             | YES             | dsl / attribute / config          |
| Source file/class       | YES             | For debugging and compilation     |
| Method                  | YES (if needed) | For non-invokable listeners       |
| Execution mode          | YES (sync only) | Async is ROADMAP                  |
| Deterministic order     | YES             | Priority then registration order  |
| Stable serialization    | ROADMAP         | If disk persistence added later   |
| Version/schema field    | ROADMAP         | For compiled cache invalidation   |
| Checksum/fingerprint    | ROADMAP         | For source invalidation detection |
