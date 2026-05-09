# DataStack/Data Structure Storage Strategies

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

Every structure must say what it is made of. Storage is not an implementation detail that may stay hidden until a
bug appears. Storage determines mutation cost, traversal behavior, serialization shape, and failure risk.

## Canonical storage responsibilities

| Storage responsibility  | Planned home                               | Used by                                         |
|-------------------------|--------------------------------------------|-------------------------------------------------|
| ArrayStorage            | `Capabilities/Structures/StructureStorage` | Stack, heap, vector, dense structures           |
| PackedArrayStorage      | `Capabilities/Structures/StructureStorage` | Sequence, dynamic array, vector                 |
| AssociativeArrayStorage | `Capabilities/Structures/StructureStorage` | Map, set, sparse structures                     |
| RingBufferStorage       | `Capabilities/Structures/StructureStorage` | Queue, deque, ring buffer                       |
| LinkedNodeStorage       | `Capabilities/Structures/StructureStorage` | Linked lists, skip list, persistent list        |
| BinaryNodeStorage       | `Capabilities/Structures/StructureStorage` | Binary trees and heap-like tree models          |
| TreeNodeStorage         | `Capabilities/Structures/StructureStorage` | General trees and model structures              |
| GraphAdjacencyStorage   | `Capabilities/Structures/StructureStorage` | Graphs, automata, graph models                  |
| MatrixDenseStorage      | `Capabilities/Structures/StructureStorage` | Dense matrices, adjacency matrices              |
| MatrixSparseStorage     | `Capabilities/Structures/StructureStorage` | Sparse matrices, CSR, CSC, COO                  |
| BitStringStorage        | `Capabilities/Structures/StructureStorage` | Bit vectors, Bloom filters, succinct structures |
| RegisterStorage         | `Capabilities/Structures/StructureStorage` | Counting filters, sketches, HyperLogLog         |
| PageStorage             | `Capabilities/Structures/StructureStorage` | BTree, BPlusTree, storage models                |
| FileBackedStorage       | `Capabilities/Structures/StructureStorage` | LSM and table models                            |
| SimulatedMemoryStorage  | `Capabilities/Structures/StructureStorage` | Simulated runtime and memory structures         |

## Storage declaration rule

Every implementation must include a documented storage line:

```text
Stack = ArrayStorage
Queue = RingBufferStorage
Graph = GraphAdjacencyStorage
BloomFilter = BitStringStorage
BTree = PageStorage
CallStack = SimulatedMemoryStorage
```

If the storage changes later, tests and complexity docs must be updated in the same change.

## Mutation policy by storage

| Storage                      | Default mutation policy                                                             |
|------------------------------|-------------------------------------------------------------------------------------|
| Array and packed array       | Copy-on-write value-safe mutation unless a mutable class explicitly owns mutation   |
| Associative array            | Replace by key with explicit missing-key behavior                                   |
| Ring buffer                  | Capacity is explicit; overflow policy is reject, overwrite, or grow, never implicit |
| Linked node                  | Node links must be repaired atomically within the operation                         |
| Tree node                    | Rotations and rebalancing must preserve all invariants before returning             |
| Graph adjacency              | Node and edge removal must keep adjacency consistent                                |
| Matrix storage               | Dimensions are fixed unless resize is an explicit operation                         |
| Bit string                   | Index bounds are checked before mutation                                            |
| Register storage             | Counter/register width and overflow policy are explicit                             |
| Page and file-backed storage | I/O behavior, sync policy, and corruption failure are explicit                      |
| Simulated memory             | Docs must state that Zend Engine memory is not controlled                           |

## Hidden I/O rule

Native in-memory structures must not perform hidden file, network, database, cache, or process I/O. Any storage that
touches external state belongs to a named storage model or runtime bridge and must expose timeout, failure, and
observability behavior where applicable.
