# DataStack/Data Structure Simulation Boundaries

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

Some structures from computer science and systems programming cannot be truthfully owned by pure PHP at the CPU,
threading, memory-layout, or hardware-cache level. AvaX may model or simulate them, but it must not lie about the
guarantees.

## Non-negotiable statement

Pure PHP does not own:

```text
Zend Engine memory layout
CPU cache lines
hardware memory barriers
true lock-free progress guarantees
true wait-free progress guarantees
native shared-memory safety
kernel page tables
real translation lookaside buffers
real processor cache behavior
```

## Mode boundaries

| Mode           | Boundary                                                      |
|----------------|---------------------------------------------------------------|
| Native         | Plain PHP owns the behavior.                                  |
| Model          | PHP owns algorithmic correctness, not low-level runtime form. |
| Runtime bridge | A named backend owns the runtime behavior.                    |
| Simulation     | PHP demonstrates the concept only.                            |

## Structures that must not claim native runtime behavior

| Structure                  | Required mode                             | Required wording                 |
|----------------------------|-------------------------------------------|----------------------------------|
| LockFreeQueue              | Simulation unless backed by named runtime | simulated lock-free queue model  |
| LockFreeStack              | Simulation unless backed by named runtime | simulated lock-free stack model  |
| WaitFreeQueue              | Simulation unless backed by named runtime | simulated wait-free queue model  |
| ReadCopyUpdate             | Simulation                                | simulated read-copy-update model |
| StackFrame                 | Simulation                                | simulated stack frame            |
| CallStack                  | Simulation                                | simulated call stack             |
| HeapMemory                 | Simulation                                | simulated heap memory            |
| SlabAllocator              | Simulation                                | simulated slab allocation        |
| BuddyAllocator             | Simulation                                | simulated buddy allocation       |
| PageTable                  | Simulation                                | simulated page table             |
| TranslationLookasideBuffer | Simulation                                | simulated TLB                    |
| CacheLine                  | Simulation                                | simulated cache line             |

## Runtime bridge naming

Runtime-backed implementations must name the backend directly:

```text
RedisBackedQueue
SwooleChannelQueue
ParallelChannelQueue
SharedMemoryQueue
```

They must document:

```text
backend dependency
timeout behavior
failure behavior
serialization behavior
ordering guarantee
delivery guarantee
observability signal
test backend
```

## Review rule

Any simulation or runtime bridge must include a negative test or documentation assertion that prevents accidental
promotion to native behavior. If docs say "lock-free" without the word "simulated" or a named backend, the change fails
review.
