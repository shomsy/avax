# 📄 Kernel Context

>
> **The "Case File" that tracks the state and story of every resolution journey.**

---

## 🗺️ High-Level Perspective

The `KernelContext` is the "State Machine" of the container. It's an object that travels with your resolution request,
holding onto its breadcrumbs, metadata, and eventually the finished instance.

```mermaid
graph TD
    User([Request]) --> C[New KernelContext]
    C --> P[Resolution Pipeline]
    P -- "I need a dependency" --> C2[Child Context]
    C2 -- "Loops detected?" --> C
    C2 -- "Path tracking" --> C
    C -- "Resolution Success" --> Instance([Living Object])
```

---

## 🌟 Quick Summary

The KernelContext is the "Communication Hub" and "Memory" of the dependency resolution process. It carries the service
identifier, tracks the resolution path to detect circular dependencies, and provides a metadata store for inter-step
communication.

### 👨‍💻 For Humans: What This Means

Think of the KernelContext as a **Doctor's Chart**. When a patient (a service request) enters the clinic (the
container), a new chart is created. This chart follows the patient to every station—reception, the nurse, the doctor,
the lab. Each station reads what was written before and adds new notes. By looking at the chart, the staff knows where
the patient has been, what's been discovered so far, and eventually, the final diagnosis (the resolved object).

---

## 📕 Core Terminology

- **[Service Identifier](../../glossary.md#service-identifier)**: The ID of the service currently being resolved.
- **[Resolution Path](../../glossary.md#resolution-flow)**: The trail of parents and children that led to this request.
- **[Circular Dependency Check](../../glossary.md#circular-dependency)**: The automated check that prevents stack
  overflows.
- **[Resolution Metadata](../../glossary.md#resolution-metadata)**: Temporary notes stored for the duration of the
  request.
- **[Child Context](../../glossary.md#nested-resolution)**: A smaller "Case File" for building a dependency of the main
  service.

These operational terms define the "Case File" mechanics. The service identifier is like the patient's name on the
chart, the resolution path is their medical history, the circular dependency check is the safety protocol that prevents
infinite loops, and metadata are the detailed lab notes shared between doctors. Together, they ensure that every
resolution is safe and documented.

## 💡 Concept: Think of It

Imagine you're solving a complex puzzle. You have a notepad where you write down which pieces you're currently looking
for. If you realize you're looking for Piece A because you need Piece B, and Piece B needs Piece A... you stop! That's
what the Context does: it keeps the "Looking For" list and raises a red flag if you start chasing your own tail.

### 👨‍💻 Concepts: For Humans

This analogy shows why Context exists: to provide a memory and a map. Without it, the Container would be "forgetful,"
potentially getting lost in recursive loops or losing track of progress.

## 📖 Story: Practical Example

Before we had Context, passing data between resolution steps meant using messy global state or huge parameter lists.
Now, if a `Guard` step needs to tell a `Build` step "this service is restricted," it just puts a note in the Context.

### 👨‍💻 Story: For Humans

This story illustrates the primary problem Context solves: shared state management. It's like a baton passed in a relay
race, where each runner (step) can see what the previous runners did.

## 👶 For Dummies: Deep Dive

Let's break this down like a treasure hunt:

1. **The Problem**: You need a map and a bag to store clues as you find them.
2. **The Context's Job**: It is the map (where we've been) and the bag (what we found).
3. **How You Use It**: You carry it from clue to clue until you find the treasure.
4. **Safety First**: If the map shows you're walking in a circle, it tells you to stop.

### 👨‍💻 Dummies: For Humans

The Context isn't the "Worker"—it's the "Information Carrier." It doesn't do the resolution itself; it just makes sure
everyone who *does* the work has all the facts.

## ⚙️ How It Works (Technical)

The `KernelContext` is a linked-list structure. Every time the container needs to resolve a dependency for another
service, it calls `child()`, which creates a new context pointing back to its parent. This allows for deep tree
traversal without complex management.

### 👨‍💻 Mechanics: For Humans

Under the hood, Context is like those nesting Russian dolls. Each doll (dependency) lives inside its parent, but they
all know who their parent is.

---

## 🛠️ Methods

>
> **Interactive API Map**
>
> - [__construct()](#__construct) — Seed the resolution context.
> - [child()](#child) — Create nested context.
> - [getInstance()](#getinstance) — Retrieve resolved object.
> - [contains()](#contains) — Loop detection check.
> - [getPath()](#getpath) — Resolution breadcrumbs.
> - [setMetaOnce()](#setmetaonce) — Guarded first-write metadata.
> - [setMeta()](#setmeta) — Store notes.
> - [putMeta()](#putmeta) — Unconditionally store notes.
> - [getMeta()](#getmeta) — Read notes.
> - [hasMeta()](#hasmeta) — Existence check.
> - [setInstanceSafe()](#setinstancesafe) — First-write wins instance.
> - [overwriteWith()](#overwritewith) — Replace instance.
> - [setInstance()](#setinstance) — Alias to resolvedWith.
> - [resolvedWith()](#resolvedwith) — Finish job with guard.
> - [__toString()](#__tostring) — Human-readable summary.
> - [isResolved()](#isresolved) — Resolution completion check.

<a name="__construct"></a>

### Method: `__construct(...)` {#method-__construct}

#### ⚙️ Technical: __construct()

Initializes the context with service ID, initial instance (optional), metadata, resolution flags, consumer, trace ID,
depth, parent, and overrides.

##### 👨‍💻 For Humans: __construct()

"Open a fresh case file with the request name, any existing object, notes, and flags about how to resolve it."

##### 📥 Parameters: __construct()

- `string $serviceId`: Identifier being resolved.
- `mixed|null $instance`: Pre-existing instance (rare).
- `array $metadata`: Initial metadata payload.
- `bool $debug`: Debug flag.
- `bool $allowAutowire`: Whether autowiring is permitted.
- `bool $manualInjection`: Skip constructor injection when true.
- `string|null $consumer`: Parent consumer ID.
- `string|null $traceId`: Trace correlation ID.
- `int $depth`: Current resolution depth.
- `KernelContext|null $parent`: Parent context link.
- `array $overrides`: Per-call parameter overrides.

##### 📤 Returns: __construct()

- `self`

##### ⚠️ Throws / Mistakes: __construct()

- No exceptions; mistake is seeding inconsistent flags (e.g., manual injection without providing dependencies).

---

<a name="child"></a>

### Method: `child(string $serviceId, array $overrides = []): self` {#method-child}

#### ⚙️ Technical: child()

Creates a new, nested context with incremented depth and parent linkage, inheriting debug/autowire/manual flags and
trace ID.

##### 👨‍💻 For Humans: child()

"I'm resolving Class A, but now I realize I need Class B. Hand me a new chart for Class B, and link it to this one."

##### 📥 Parameters: child()

- `string $serviceId`: The ID of the dependency we're about to fetch.
- `array $overrides`: Custom parameters for this specific dependency.

##### 📤 Returns: child()

- `self`: A fresh, linked context instance.

##### ⚠️ Throws / Mistakes: child()

- No exceptions; common mistake is forgetting overrides when delegating to another service.

---

<a name="getinstance"></a>

### Method: `getInstance(): mixed` {#method-getinstance}

#### ⚙️ Technical: getInstance()

Retrieves the resolved service instance stored in this context, or null if unresolved.

##### 👨‍💻 For Humans: getInstance()

"Give me the finished product we were working on."

##### 📤 Returns: getInstance()

- `mixed`: The living object (or null if not yet resolved).

---

<a name="contains"></a>

### Method: `contains(string $serviceId): bool` {#method-contains}

#### ⚙️ Technical: contains()

Checks the entire parent chain to see if a specific ID is already in the resolution path to detect cycles.

##### 👨‍💻 For Humans: contains()

"Wait, are we already working on this? Don't let me start Class A if its parent is already waiting for Class A."

##### 📤 Returns: contains()

- `bool`: `true` if a loop is found.

##### ⚠️ Throws / Mistakes: contains()

- No exceptions; mistake is ignoring the result and recursing into a cycle anyway.

---

<a name="getpath"></a>

### Method: `getPath(): string` {#method-getpath}

#### ⚙️ Technical: getPath()

Returns a string representation of the full resolution chain (e.g., `A -> B -> C`).

##### 👨‍💻 For Humans: getPath()

"Show me the trail from the original request to where we are now."

##### 📤 Returns: getPath()

- `string`: The human-readable path.

---

<a name="setmetaonce"></a>

### Method: `setMetaOnce(string $namespace, string $key, mixed $value): void` {#method-setmetaonce}

#### ⚙️ Technical: setMetaOnce()

Stores metadata only if the key is not already set; subsequent calls with the same value are no-ops, while conflicting
values throw to surface misuse.

##### 👨‍💻 For Humans: setMetaOnce()

"If there's no note yet, pin this one; if someone already pinned a different note, raise a flag instead of silently
overwriting."

##### 📥 Parameters: setMetaOnce()

- `string $namespace`: Logical bucket (e.g., `resolution`).
- `string $key`: Entry name.
- `mixed $value`: Data to store.

##### ⚠️ Throws / Mistakes: setMetaOnce()

- Throws `LogicException` on conflicting writes; mistake is assuming later writes will replace the first—use `putMeta()`
  if you need to overwrite.

---

<a name="setmeta"></a>

### Method: `setMeta(string $namespace, string $key, mixed $value): void` {#method-setmeta}

#### ⚙️ Technical: setMeta()

Writes metadata, replacing any existing value for the same namespace/key.

##### 👨‍💻 For Humans: setMeta()

"Write a sticky note in the 'Pipeline' folder named 'SecurityStatus' that says 'Passed'."

##### ⚠️ Throws / Mistakes: setMeta()

- No exceptions; mistake is overwriting data unintentionally—use `setMetaOnce()` if you need first-write-wins.

---

<a name="putmeta"></a>

### Method: `putMeta(string $namespace, string $key, mixed $value): void` {#method-putmeta}

#### ⚙️ Technical: putMeta()

Unconditionally stores metadata, identical to `setMeta` but explicit about direct writes.

##### 👨‍💻 For Humans: putMeta()

"Force the note to this value, even if something was there before."

---

<a name="getmeta"></a>

### Method: `getMeta(string $namespace, string $key, mixed $default = null): mixed` {#method-getmeta}

#### ⚙️ Technical: getMeta()

Retrieves saved metadata or returns a provided default.

##### 👨‍💻 For Humans: getMeta()

"Look for the note in the 'Pipeline' folder named 'SecurityStatus'. If it's not there, assume 'Unknown'."

---

<a name="hasmeta"></a>

### Method: `hasMeta(string $namespace, string $key): bool` {#method-hasmeta}

#### ⚙️ Technical: hasMeta()

Checks if a specific metadata entry exists.

##### 👨‍💻 For Humans: hasMeta()

"Is there already a note with this name?"

---

<a name="setinstancesafe"></a>

### Method: `setInstanceSafe(mixed $instance): void` {#method-setinstancesafe}

#### ⚙️ Technical: setInstanceSafe()

Sets the resolved instance only if it is currently null (first write wins).

##### 👨‍💻 For Humans: setInstanceSafe()

"Fill in the final object if nobody else has yet."

##### ⚠️ Throws / Mistakes: setInstanceSafe()

- No exceptions; mistake is assuming it overwrites—use `overwriteWith()` if replacement is required.

---

<a name="overwritewith"></a>

### Method: `overwriteWith(mixed $instance): void` {#method-overwritewith}

#### ⚙️ Technical: overwriteWith()

Replaces the stored instance unconditionally; metadata remains untouched.

##### 👨‍💻 For Humans: overwriteWith()

"Swap the object but keep all the notes."

---

<a name="setinstance"></a>

### Method: `setInstance(object $instance): void` {#method-setinstance}

#### ⚙️ Technical: setInstance()

Alias for `resolvedWith()` to set the resolved instance with guard semantics.

##### 👨‍💻 For Humans: setInstance()

"Mark this object as the official resolution."

---

<a name="resolvedwith"></a>

### Method: `resolvedWith(mixed $instance): void` {#method-resolvedwith}

#### ⚙️ Technical: resolvedWith()

Marks the resolution as complete by setting the instance; throws if already resolved to enforce single completion.

##### 👨‍💻 For Humans: resolvedWith()

"Job's done. Here is the final object. Seal the file."

##### ⚠️ Throws / Mistakes: resolvedWith()

- Throws `LogicException` on double writes; use `overwriteWith()` when replacement is intentional.

---

<a name="__tostring"></a>

### Method: `__toString(): string` {#method-__tostring}

#### ⚙️ Technical: __toString()

Renders a concise string with service ID, depth, and resolution status.

##### 👨‍💻 For Humans: __toString()

"Quick snapshot of which service this is, how deep we are, and whether it's done."

---

<a name="isresolved"></a>

### Method: `isResolved(): bool` {#method-isresolved}

#### ⚙️ Technical: isResolved()

Indicates whether an instance has been set (via any setter).

##### 👨‍💻 For Humans: isResolved()

"Have we already produced the final object?"

##### 📤 Returns: isResolved()

- `bool`: `true` when an instance exists.

## 🏗️ Architecture Role

KernelContext works with the entire pipeline ecosystem. The pipeline uses it as the communication medium, steps interact
with it during execution, and the kernel creates it to start the journey. It's the "Thread-Safe" state carrier of the
engine.

### 👨‍💻 Ecosystem: For Humans

In the container's world, the Pipeline is the **Road**, the Kernel is the **Manager**, and the Context is the **Car**.
The car carries all the passengers and luggage (data) from one end of the road to the other.
