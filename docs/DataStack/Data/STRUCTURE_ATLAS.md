# DataStack/Data Structure Atlas

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

This atlas names the complete data structure universe planned for `DataStack/Data`. It is the source a reader uses to
find the canonical home, implementation mode, and proof expectation for each structure family.

The governing evidence plan is:

```text
EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md
```

## Core law

```text
Structure = data + invariant + behavior
```

If an implementation does not protect an invariant, it is not complete. If it does not declare storage, it is not ready
for public use. If PHP cannot truthfully own the runtime property, the structure is a model, runtime bridge, or
simulation.

## Implementation modes

| Mode           | Meaning                                                                                     |
|----------------|---------------------------------------------------------------------------------------------|
| Native         | Plain PHP can implement the behavior truthfully.                                            |
| Model          | PHP can model the algorithm, but not the same low-level runtime form as a systems language. |
| Runtime bridge | The behavior depends on a named runtime backend.                                            |
| Simulation     | PHP can explain and simulate the concept, but cannot own the runtime guarantee.             |

## Canonical families

| Family            | Canonical home                             | Purpose                                                              |
|-------------------|--------------------------------------------|----------------------------------------------------------------------|
| Linear            | `Capabilities/Structures/Linear`           | Ordered values, stacks, queues, buffers, sparse arrays               |
| Hashing           | `Capabilities/Structures/Hashing`          | Hash tables, hash maps, hash multisets                               |
| Maps              | `Capabilities/Structures/Maps`             | Key-value structures and uniqueness variants                         |
| Sets              | `Capabilities/Structures/Sets`             | Membership structures and counted membership                         |
| Priority          | `Capabilities/Structures/Priority`         | Heap and priority queue behavior                                     |
| Trees             | `Capabilities/Structures/Trees`            | Hierarchical, ordered, range, text, dynamic, and cryptographic trees |
| Graphs            | `Capabilities/Structures/Graphs`           | Node-edge structures, graph storage, connectivity, flow, ordering    |
| Text              | `Capabilities/Structures/Text`             | Text buffers, tries, suffix structures, automata, wavelet structures |
| Numeric           | `Capabilities/Structures/Numeric`          | Vectors, tensors, bit vectors, bit sets, bitmaps                     |
| Matrix            | `Capabilities/Structures/Matrix`           | Dense, sparse, coordinate, banded, and bit matrix forms              |
| Spatial           | `Capabilities/Structures/Spatial`          | Spatial indexes, spatial hashes, grids, curves, geometry models      |
| Persistent        | `Capabilities/Structures/Persistent`       | Version-preserving immutable structures                              |
| Functional        | `Capabilities/Structures/Functional`       | Functional cursors, trees, queues, and persistent vector models      |
| Probabilistic     | `Capabilities/Structures/Probabilistic`    | Filters, sketches, sampling, approximate counting                    |
| Compressed        | `Capabilities/Structures/Compressed`       | Succinct, packed, run-length, suffix, bitmap compression structures  |
| Storage models    | `Capabilities/Structures/StorageModels`    | Database-like structure models that do not own SQL execution         |
| Algorithms        | `Capabilities/Structures/Algorithms`       | Algorithm-shaped structures and decomposition helpers                |
| Concurrent models | `Capabilities/Structures/ConcurrentModels` | Runtime bridges and concurrency models                               |
| Simulated runtime | `Capabilities/Structures/SimulatedRuntime` | Memory and CPU concept simulations                                   |

## First production wave

Wave 2 is the first broad production structure wave. It may start only after Wave 1 Structure Kernel is proven.

Planned structures:

```text
Sequence
Stack
Queue
Deque
RingBuffer
Map
Set
OrderedMap
OrderedSet
MultiMap
Bag
BinaryHeap
PriorityQueue
Tree
BinaryTree
Trie
Graph
UnionFind
FenwickTree
SegmentTree
BloomFilter
Matrix
SparseMatrix
```

## Existing implemented baseline

Existing DataStack/Data already has early structure behavior for:

```text
Sequence
DataList
Map
MultiMap
OrderedMap
Set
OrderedSet
Pair
Tuple2
Tuple3
Tuple4
Record
OperationResult
Option
Result
```

These existing files are useful baseline evidence. They are not automatically considered complete for the universe
standard until they have explicit storage, invariant, failure, serialization, mutation, documentation, and complexity
proof.

## Atlas rule

Each future implementation must update this atlas and the implementation matrix in the same change as the code. A
structure that exists in code but not in the atlas is undocumented. A structure listed as complete without validation
evidence is not complete.
