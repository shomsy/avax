# Data Structures Roadmap

**Date:** 2026-05-08
**Stage:** V3 Data Engine — Phase 4 Roadmap
**Status:** Planning

## Overview

This roadmap classifies the full universe of data structures into maturity tiers for the AvaX Data Engine. Only existing
structures are in the current scope. New structures are added incrementally, justified by use, and proven with tests.

## CORE_NOW — Implemented, Production-Ready

| Structure  | Purpose                       | Built on Arrhae?        | Public API? | Status                                                      | Tests needed        | Promotion criteria |
|------------|-------------------------------|-------------------------|-------------|-------------------------------------------------------------|---------------------|--------------------|
| Map        | Key→value mapping             | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| Set        | Unique value collection       | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| Sequence   | Integer-indexed ordered list  | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| OrderedMap | Insertion-order key→value     | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| OrderedSet | Insertion-order unique values | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| MultiMap   | Key→multiple values           | No (own implementation) | Yes         | Implemented, production-ready                               | Existing unit tests | Already promoted   |
| DataList   | Extended ordered list         | No (own implementation) | Yes         | Implemented, production-ready (rename to Sequence deferred) | Existing unit tests | Already promoted   |

### Existing Structures Notes

- **Map**: `Capabilities/Map/Map.php` — associative array with type-safe access
- **Set**: `Capabilities/Set/Set.php` — unique values with JSON-based hashing
- **Sequence**: `Capabilities/Sequence/Sequence.php` — minimal integer-indexed list
- **OrderedMap**: `Capabilities/OrderedMap/OrderedMap.php` — preserves insertion order
- **OrderedSet**: `Capabilities/OrderedSet/OrderedSet.php` — unique + ordered
- **MultiMap**: `Capabilities/MultiMap/MultiMap.php` — one key, many values
- **DataList**: `Capabilities/DataList/DataList.php` — richer list (append, prepend, first, last)

### DataList vs Sequence Decision

Both exist with different implementations:

- Sequence: minimal (all, map, filter, reduce, toDataList)
- DataList: richer (all, append, prepend, map, filter, first, last, toCollection)

**Decision**: Keep both for now. `Sequence` is the canonical name for integer-indexed ordered collections. `DataList` is
the richer implementation. Future work: merge implementations, make Sequence the public facade with DataList's behavior.

## CORE_LATER — Planned, Not Yet Implemented

These structures address real use cases but are not needed until a concrete use case emerges.

| Structure        | Purpose                    | Built on Arrhae? | Public API? | Status      | Tests needed                                       | Promotion criteria                                |
|------------------|----------------------------|------------------|-------------|-------------|----------------------------------------------------|---------------------------------------------------|
| Queue            | FIFO buffer                | No               | Yes         | Not started | Unit tests: push, pop, peek, isEmpty, size         | Real use case in framework or components          |
| Stack            | LIFO buffer                | No               | Yes         | Not started | Unit tests: push, pop, peek, isEmpty, size         | Real use case in framework or components          |
| Deque            | Double-ended queue         | No               | Yes         | Not started | Unit tests: pushFront, pushBack, popFront, popBack | Needed when Queue + Stack both exist              |
| PriorityQueue    | Priority-ordered dequeue   | Heap-based       | Yes         | Not started | Unit tests: priority ordering, empty behavior      | Real scheduling use case                          |
| Heap             | Heap tree structure        | No               | Internal    | Not started | Unit tests: heap invariant, insertion, extraction  | Needed for PriorityQueue                          |
| MinHeap          | Min-heap implementation    | Heap             | Internal    | Not started | Inherits from Heap tests                           | Needed for PriorityQueue with lowest-first        |
| MaxHeap          | Max-heap implementation    | Heap             | Internal    | Not started | Inherits from Heap tests                           | Needed for PriorityQueue with highest-first       |
| Tree             | Generic tree structure     | No               | Internal    | Not started | Unit tests: parent-child, traversal                | Real tree use case (e.g., AST, DOM)               |
| BinaryTree       | Binary tree                | Tree             | Internal    | Not started | Inherits Tree tests + binary invariant             | Needed for BST or balanced trees                  |
| BinarySearchTree | BST with ordering          | BinaryTree       | Yes         | Not started | Unit tests: insert, search, delete, ordering       | Real search tree use case                         |
| Trie             | Prefix tree for strings    | No               | Yes         | Not started | Unit tests: insert, search, prefix, delete         | Real autocomplete/routing use case                |
| Graph            | Node-edge structure        | No               | Internal    | Not started | Unit tests: add/remove nodes/edges, traversal      | Real graph use case (e.g., dependency resolution) |
| DirectedGraph    | Directed edges             | Graph            | Yes         | Not started | Inherits Graph tests + direction invariant         | Real directed graph use case                      |
| WeightedGraph    | Weighted edges             | DirectedGraph    | Yes         | Not started | Inherits DirectedGraph tests + weight operations   | Real weighted graph use case (e.g., routing)      |
| UnionFind        | Disjoint set union         | No               | Internal    | Not started | Unit tests: union, find, connected components      | Needed for graph algorithms                       |
| BiMap            | Bidirectional map          | Map              | Yes         | Not started | Unit tests: forward + reverse lookup, uniqueness   | Real bidirectional mapping use case               |
| SortedMap        | Key-sorted map             | Map + Comparator | Yes         | Not started | Unit tests: sorted order, insertion, deletion      | Real sorted key use case                          |
| SortedSet        | Value-sorted set           | Set + Comparator | Yes         | Not started | Unit tests: sorted order, uniqueness               | Real sorted value use case                        |
| MultiSet         | Bag / multiset             | Set              | Yes         | Not started | Unit tests: count, add, remove, contains           | Real frequency counting use case                  |
| RingBuffer       | Fixed-size circular buffer | No               | Internal    | Not started | Unit tests: wrap-around, full, empty               | Real circular buffer use case (e.g., streaming)   |
| BitSet           | Space-efficient bit flags  | No               | Internal    | Not started | Unit tests: set, clear, toggle, intersection       | Real flag set with many bits                      |
| Matrix           | 2D array operations        | No               | Yes         | Not started | Unit tests: multiply, transpose, access            | Real matrix math use case                         |

## LABS_ONLY — Experimental, Not for Production

These belong in `labs/` only. They are interesting computer science structures but not needed for framework operations.

| Structure             | Purpose                                  | Why Labs Only                               |
|-----------------------|------------------------------------------|---------------------------------------------|
| BloomFilter           | Probabilistic set membership             | Niche use case, false positives             |
| CountMinSketch        | Approximate frequency counting           | Specialized streaming use case              |
| HyperLogLog           | Approximate cardinality                  | Specialized analytics use case              |
| Rope                  | Efficient string concatenation           | Specialized text editor use case            |
| PieceTable            | Efficient document editing               | Specialized editor use case                 |
| MerkleTree            | Hash tree for verification               | Specialized distributed systems use case    |
| PersistentMap         | Immutable map with structural sharing    | Specialized functional programming use case |
| PersistentSet         | Immutable set with structural sharing    | Specialized functional programming use case |
| PersistentVector      | Immutable vector with structural sharing | Specialized functional programming use case |
| CRDT structures       | Conflict-free replicated data types      | Specialized distributed systems use case    |
| Lock-free structures  | Concurrent data structures               | Specialized high-performance use case       |
| Compressed structures | Space-optimized variants                 | Specialized memory-constrained use case     |

## NON_GOAL — Not in Scope

These are explicitly not part of the AvaX Data Engine:

- Custom database indexes (use database-specific indexes)
- Custom storage engines (use established storage libraries)
- Custom distributed database structures (use distributed database solutions)
- Custom low-level memory allocators (use system allocators)
- Production lock-free concurrency primitives (use language/runtime primitives)

## Promotion Criteria

A structure moves from one tier to the next when:

1. **Labs → CORE_LATER**: A concrete use case emerges in the framework or a component.
2. **CORE_LATER → CORE_NOW**: The structure is implemented, tested (unit + integration), documented, and proven in at
   least one real use case.

## Implementation Principles

1. **No placeholders**: Do not create empty classes or skeleton implementations.
2. **Tests first**: Every structure starts with tests that prove its invariant.
3. **Canonical names**: Use Map, Set, Queue, Stack — not DataMap, DataSet.
4. **Composition**: New structures compose existing ones when possible (e.g., PriorityQueue composes Heap).
5. **Arrhae where useful**: Structures may use Arrhae for array operations if it simplifies implementation, but
   structures with strict invariants (Map, Set) implement their own internals for correctness.
6. **Foundation dependencies only**: Structures depend only on Foundation, not on Forms, Operators, or other
   Structures (except composition like PriorityQueue → Heap).
