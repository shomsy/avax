---
title: DataStack-Data-how-this-works
owner: DataStack/Data
last_reviewed: 2026-05-09
classification: internal
---

# DataStack Data How This Works

## What this folder is

`DataStack/Data` owns in-memory data shape, traversal, transformation, serialization, and data structure behavior for
AvaX. It is the place where reusable data structures are implemented once and then exposed through a small public
surface only after their invariants are proven.

It does not own persistence, SQL execution, database connections, queue runtimes, process parallelism, or real CPU
memory control.

## Real commands or triggers that reach this folder

- PHPUnit tests under `tests/Unit/Components/DataStack/Data`
- Framework or component code that imports `Avax\Components\DataStack\Data\System\PublicSurface\*`
- Internal DataStack/Data flows such as collection creation, nested value reading, transformation, aggregation, and
  serialization

## Exact upstream handoffs

- `System/PublicSurface/Data.php` receives broad user-facing data operations.
- `System/PublicSurface/Sequence.php`, `Map.php`, `Set.php`, and related public surface files receive stable structure
  usage.
- Public surface units delegate into `System/Flows`, `System/Capabilities`, or `System/Configuration`.
- Structure implementations live under `System/Capabilities/Structures`.

## The simplest story

- A caller asks the public surface for a stable data behavior.
- The public surface delegates to a flow or capability that owns the behavior.
- The capability protects the invariant, chooses storage, handles failure, and returns a stable result.
- The caller receives a value or a clear failure, not leaked internal storage.

## The first important path

When a caller creates a sequence through the public surface:

```php
$sequence = Sequence::of([1, 2, 3]);
```

the public surface must remain thin. The actual ordered-value invariant belongs to the linear structure capability, and
the implementation must prove traversal, mutation policy, serialization behavior, and edge cases through tests.

## Ownership map

| Folder                     | Owns                                                                      | Does not own                          |
|----------------------------|---------------------------------------------------------------------------|---------------------------------------|
| `PublicSurface/`           | Stable user-facing entrypoints                                            | Storage internals or heavy algorithms |
| `Flows/`                   | Complete data actions                                                     | Reusable storage mechanics            |
| `Capabilities/Structures/` | Reusable structure behavior                                               | Public API expansion by default       |
| `Configuration/`           | Data component assembly                                                   | Structure algorithms                  |
| `Foundation/`              | Tiny neutral values, failures, comparison, hashing, iteration, mutability | Large reusable behavior               |

## Structure universe boundary

The structure universe is executed in waves:

1. Wave 0 documents the atlas, implementation matrix, storage strategy, public surface policy, complexity profile, and
   simulation boundaries.
2. Wave 1 builds the Structure Kernel.
3. Later waves add production structures only when each structure has tests, docs, invariant proof, failure behavior,
   and complexity evidence.

## Failure behavior

Data structures must fail explicitly. Examples:

- empty stack pop
- missing map key when no default is allowed
- invalid matrix dimensions
- duplicate key where uniqueness is required
- invalid capacity
- broken invariant after attempted mutation

Silent mutation, broad catch-and-ignore behavior, and hidden I/O are not allowed.

## Where to debug first

- Public API behavior: start in `System/PublicSurface`.
- End-to-end data actions: start in `System/Flows`.
- Structure invariants: start in `System/Capabilities/Structures`.
- Shared failure or value behavior: start in `System/Foundation`.
- Test proof: start in `tests/Unit/Components/DataStack/Data`.

## Terms easy to confuse

| Term           | Meaning here                                                                     |
|----------------|----------------------------------------------------------------------------------|
| Structure      | Data plus invariant plus behavior                                                |
| Storage        | The internal representation used by a structure                                  |
| Public surface | Stable user-facing entrypoint                                                    |
| Model          | Correct algorithmic representation without low-level runtime claim               |
| Runtime bridge | Behavior provided by a named runtime backend                                     |
| Simulation     | Educational or validation model for behavior PHP cannot own at CPU/runtime level |
