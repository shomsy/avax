# Structure Atlas

Knowledge map of every data structure planned for DataStack/Data.

Source of truth: `EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md` §8 (Complete Registry).

> **This is a knowledge map, not a delivery obligation.**
> The atlas explains what structures exist conceptually. It does not mean every structure must be implemented.
> Structures are implemented only when there is a real use case, not because the atlas lists them.

## Mode Key

| Mode | Meaning |
|---|---|
| N | Native — PHP implements truthfully |
| M | Model — correct algorithm, no low-level claim |
| R | Runtime Bridge — backend-backed boundary |
| S | Simulation — PHP cannot own the guarantee |

## Summary by Category

| Category | Total | N | M | R | S | Implemented |
|---|---|---|---|---|---|---|
| Linear | 16 | 13 | 3 | 0 | 0 | 10 |
| Map | 10 | 8 | 2 | 0 | 0 | 4 |
| Set | 8 | 7 | 1 | 0 | 0 | 3 |
| Priority | 12 | 5 | 7 | 0 | 0 | 4 |
| Trees | 33 | 17 | 16 | 0 | 0 | 5 |
| Graphs | 20 | 11 | 9 | 0 | 0 | 3 |
| Text/Automata | 16 | 7 | 9 | 0 | 0 | 1 |
| Numeric/Matrix | 15 | 14 | 1 | 0 | 0 | 3 |
| Spatial | 20 | 5 | 15 | 0 | 0 | 1 |
| Probabilistic | 13 | 8 | 5 | 0 | 0 | 3 |
| Persistent/Functional | 15 | 3 | 12 | 0 | 0 | 0 |
| Compressed/Succinct | 8 | 2 | 6 | 0 | 0 | 0 |
| Storage Models | 16 | 4 | 12 | 0 | 0 | 0 |
| Algorithmic | 7 | 5 | 2 | 0 | 0 | 0 |
| Concurrent Models | 12 | 2 | 3 | 4 | 3 | 0 |
| Simulated Runtime | 13 | 3 | 2 | 0 | 8 | 0 |
| Hashing | 4 | 4 | 0 | 0 | 0 | 0 |
| **Total** | **238** | **118** | **109** | **4** | **11** | **38** |

## Classification

Each implemented structure is classified by its role in the public API surface.

### CORE_PUBLIC — Stable public API

Structures that form the primary DataStack/Data surface. Tested, documented, safe for general use.

| Structure | Category | Public Facade |
|---|---|---|
| Sequence | Linear | `PublicSurface/Sequence.php` |
| Stack | Linear | `PublicSurface/Stack.php` |
| Queue | Linear | `PublicSurface/Queue.php` |
| Deque | Linear | `PublicSurface/Deque.php` |
| RingBuffer | Linear | — |
| SparseArray | Linear | — |
| DataList | Linear | — |
| Map | Maps | `PublicSurface/Map.php` |
| OrderedMap | Maps | `PublicSurface/OrderedMap.php` |
| MultiMap | Maps | `PublicSurface/MultiMap.php` |
| Set | Sets | `PublicSurface/Set.php` |
| OrderedSet | Sets | `PublicSurface/OrderedSet.php` |
| BinaryHeap | Priority | `PublicSurface/Heap.php` |
| PriorityQueue | Priority | `PublicSurface/PriorityQueue.php` |
| BinaryTree | Trees | — |
| BinarySearchTree | Trees | — |
| Trie | Trees | — |
| Graph | Graphs | `PublicSurface/Graph.php` |
| UnionFind | Graphs | — |
| DenseMatrix | Matrix | `PublicSurface/Matrix.php` |
| SparseMatrix | Matrix | `PublicSurface/Matrix.php` |
| Point | Spatial (Foundation value) | — |

### CORE_INTERNAL — Used internally by DataStack/Data

Kernel structures, foundation values, storage primitives, and failure types that support other structures.

| Structure | Owner |
|---|---|
| DataStructure (family promise) | Foundation |
| LinearStructure | Foundation |
| AssociativeStructure | Foundation |
| SetStructure | Foundation |
| MapStructure | Foundation |
| TreeStructure | Foundation |
| GraphStructure | Foundation |
| HeapStructure | Foundation |
| MatrixStructure | Foundation |
| ProbabilisticStructure | Foundation |
| PersistentStructure | Foundation |
| ConcurrentStructure | Foundation |
| SimulatedStructure | Foundation |
| ArrayStorage | StructureStorage |
| AssociativeArrayStorage | StructureStorage |
| RingBufferStorage | StructureStorage |
| LinkedNodeStorage | StructureStorage |
| BinaryNodeStorage | StructureStorage |
| TreeNodeStorage | StructureStorage |
| GraphAdjacencyStorage | StructureStorage |
| MatrixDenseStorage | StructureStorage |
| MatrixSparseStorage | StructureStorage |
| BitStringStorage | StructureStorage |
| StructureStorage (interface) | StructureStorage |
| RequiredDataShape | Foundation |
| EmptyStructure, DuplicateKey, MissingKey, etc. | Failure |
| Comparator, Equality, Ordering | Comparison |
| HashFunction, StableHash, StringHash, ObjectHash | Hashing |
| Pair, Tuple, Entry, Range, Interval, Edge, WeightedEdge, Priority | Values |

### ADVANCED_PUBLIC — Useful but not beginner/core API

Advanced structures that are tested and documented but not part of the minimal public surface.

| Structure | Category | Notes |
|---|---|---|
| FenwickTree | Trees | Binary indexed tree — specialist use |
| SegmentTree | Trees | Range queries — specialist use |
| WeightedGraph | Graphs | Basic vocabulary (hasNode, nodes, neighborsOf, isEmpty) — no removeNode/removeEdge |
| Bag | Sets | Multiset with duplicate counts |
| MinHeap | Priority | Extract-min binary heap |
| MaxHeap | Priority | Extract-max binary heap |
| LinkedList | Linear | Immutable singly-linked list |
| DoublyLinkedList | Linear | Immutable doubly-linked list (array-backed) |
| DynamicArray | Linear | Indexed sequence with capacity growth |
| CountMinSketch | Probabilistic | Frequency estimation — LABS boundary |
| HyperLogLog | Probabilistic | Cardinality estimation — LABS boundary |
| MapEntry | Maps | Value object for map entries |
| Trie | Text/Trees | Prefix tree |
| Option/Some/None | Functional | Optional value pattern |
| Result/Success/Failure | Functional | Error handling pattern |
| Collection/Arrhae | Forms | Collection abstraction |

### LABS_ONLY — Probabilistic, research, simulation, specialist

Structures that are experimental, probabilistic, or not yet production-grade.

| Structure | Category | Reason |
|---|---|---|
| BloomFilter | Probabilistic | Probabilistic — false positive rate depends on bit count and hash count. No deletion support. No merge support. Has public facade but classified LABS because probabilistic structures require error rate documentation and benchmark evidence before CORE promotion. |
| CountMinSketch | Probabilistic | Probabilistic — never undercounts but may overcount. No merge support. 32-bit hash limit. No benchmark evidence. |
| HyperLogLog | Probabilistic | Probabilistic — cardinality estimate with configurable error. No membership queries. No merge support. 32-bit hash limit. |

### ROADMAP_ONLY — Listed in atlas but not implemented

All remaining structures from the atlas (≈200 structures) are roadmap-only. They are not delivery obligations.
Each will be evaluated individually before implementation based on:

- Real use case in AvaX framework or application code
- PHP can truthfully provide the behavior
- Tests, documentation, and evidence can be produced
- Public surface value justifies the facade

See `STRUCTURE_IMPLEMENTATION_MATRIX.md` for detailed per-structure status.
