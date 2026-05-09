# DataStack/Data Structure Public Surface

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

PublicSurface receives. It does not own heavy algorithms, storage mechanics, runtime bridges, or structure internals.

The structure universe may contain many internal capabilities. Only stable, useful, tested structures become public
surface entries.

## Initial allowed public structures

| Public surface | Promotion condition                                                     |
|----------------|-------------------------------------------------------------------------|
| Sequence       | ordered finite values, traversal, serialization, mutation policy proven |
| Stack          | LIFO invariant and empty failure proven                                 |
| Queue          | FIFO invariant and empty failure proven                                 |
| Deque          | both-end operation invariant proven                                     |
| Map            | key-to-one-current-value invariant proven                               |
| Set            | unique membership invariant proven                                      |
| OrderedMap     | insertion order invariant proven                                        |
| OrderedSet     | insertion order membership invariant proven                             |
| MultiMap       | key-to-many-values invariant proven                                     |
| Heap           | heap invariant proven                                                   |
| PriorityQueue  | priority extraction invariant proven                                    |
| Tree           | tree ownership and traversal proven                                     |
| Graph          | node-edge consistency proven                                            |
| Matrix         | dimensions and cell access proven                                       |
| BloomFilter    | no false-negative behavior proven for inserted values                   |

## Not public by default

These categories remain internal until a real user-facing need is proven:

```text
research-grade structures
simulated runtime structures
storage model structures
runtime bridge structures
compressed structures
specialist spatial structures
advanced probabilistic sketches
```

## Public surface promotion checklist

Before a structure gets a public facade:

```text
[ ] internal implementation exists
[ ] invariant tests pass
[ ] edge-case tests pass
[ ] failure behavior is explicit
[ ] serialization behavior is stable
[ ] mutation policy is stable
[ ] docs include example usage
[ ] public surface delegates rather than doing heavy work
[ ] public surface checker passes
```

## Public surface anti-patterns

Forbidden:

```text
public class for every exotic structure
public API before invariant tests
public API that exposes storage internals
public API that hides I/O
public API that claims performance without evidence
public API for a simulation that sounds like a real runtime guarantee
```

## Example shape

Expected user-facing style:

```php
$stack = Stack::empty()
    ->push('A')
    ->push('B');
```

The fluent API is allowed only when it keeps mutation policy obvious. If an operation returns a new structure, docs and
types must make that clear.
