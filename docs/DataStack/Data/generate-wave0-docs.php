<?php

declare(strict_types=1);

/**
 * Generates Wave 0 documentation files from the master plan.
 *
 * Usage: php docs/DataStack/Data/generate-wave0-docs.php
 */

$docsDir = __DIR__;

// ============================================================
// 1. STRUCTURE_ATLAS.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_ATLAS.md', <<< 'ATLAS'
# Structure Atlas

Complete registry of every data structure in DataStack/Data.

Source of truth: `EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md` §8 (Complete Registry).

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
| Linear | 16 | 13 | 3 | 0 | 0 | 7 |
| Map | 10 | 8 | 2 | 0 | 0 | 4 |
| Set | 8 | 7 | 1 | 0 | 0 | 2 |
| Priority | 12 | 5 | 7 | 0 | 0 | 2 |
| Trees | 33 | 17 | 16 | 0 | 0 | 5 |
| Graphs | 20 | 11 | 9 | 0 | 0 | 3 |
| Text/Automata | 16 | 7 | 9 | 0 | 0 | 1 |
| Numeric/Matrix | 15 | 14 | 1 | 0 | 0 | 3 |
| Spatial | 20 | 5 | 15 | 0 | 0 | 1 |
| Probabilistic | 13 | 8 | 5 | 0 | 0 | 1 |
| Persistent/Functional | 15 | 3 | 12 | 0 | 0 | 0 |
| Compressed/Succinct | 8 | 2 | 6 | 0 | 0 | 0 |
| Storage Models | 16 | 4 | 12 | 0 | 0 | 0 |
| Algorithmic | 7 | 5 | 2 | 0 | 0 | 0 |
| Concurrent Models | 12 | 2 | 3 | 4 | 3 | 0 |
| Simulated Runtime | 13 | 3 | 2 | 0 | 8 | 0 |
| Hashing | 4 | 4 | 0 | 0 | 0 | 0 |
| **Total** | **238** | **118** | **109** | **4** | **11** | **29** |

## Implemented Structures (29)

| Structure | Category | Mode | Test Coverage |
|---|---|---|---|
| Sequence | Linear | N | CoreStructuresTest |
| Stack | Linear | N | CoreStructuresTest |
| Queue | Linear | N | CoreStructuresTest |
| Deque | Linear | N | CoreStructuresTest |
| RingBuffer | Linear | N | CoreStructuresTest |
| SparseArray | Linear | N | CoreStructuresTest |
| DataList | Linear | N | CollectionTest |
| Map | Maps | N | CollectionTest |
| OrderedMap | Maps | N | CollectionTest |
| MapEntry | Maps | N | CollectionTest |
| MultiMap | Maps | N | CollectionTest |
| Set | Sets | N | CoreStructuresTest |
| OrderedSet | Sets | N | CoreStructuresTest |
| BinaryHeap | Priority | N | CoreStructuresTest |
| PriorityQueue | Priority | N | CoreStructuresTest |
| BinaryTree | Trees | N | CoreStructuresTest |
| BinarySearchTree | Trees | N | CoreStructuresTest |
| Trie | Trees | N | CoreStructuresTest |
| FenwickTree | Trees | N | CoreStructuresTest |
| SegmentTree | Trees | N | CoreStructuresTest |
| Graph | Graphs | N | CoreStructuresTest |
| UnionFind | Graphs | N | CoreStructuresTest |
| WeightedGraph | Graphs | N | CoreStructuresTest |
| DenseMatrix | Matrix | N | CoreStructuresTest |
| SparseMatrix | Matrix | N | CoreStructuresTest |
| BloomFilter | Probabilistic | N | CoreStructuresTest |
| Point | Spatial | N | Foundation value |
| Option/Result | Functional | N | CollectionTest |
| Collection/Arrhae | Forms | N | ArrhaeTest/CollectionTest |

See `STRUCTURE_IMPLEMENTATION_MATRIX.md` for per-structure done status.
ATLAS
);

// ============================================================
// 2. STRUCTURE_IMPLEMENTATION_MATRIX.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_IMPLEMENTATION_MATRIX.md', <<< 'MATRIX'
# Structure Implementation Matrix

Tracks the 17-item definition of done for every structure (master plan §9).

## Legend

✅ = done  ⚠️ = partial  ❌ = not started  N/A = not meaningful

## Wave 2 — Stable Production Structures

| # | Structure | Class | Storage | Invariants | Core Ops | Traversal | toArray | toJson | Failure | Edge Tests | Invariant Tests | Serialization | Mutation Tests | Complexity | Docs | Example | Status |
|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|---|
| 1 | Sequence | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 2 | Stack | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 3 | Queue | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 4 | Deque | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 5 | RingBuffer | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 6 | DataList | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 7 | Map | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 8 | Set | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 9 | OrderedMap | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 10 | OrderedSet | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 11 | MultiMap | ✅ | ✅ | ⚠️ | ✅ | ✅ | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | ❌ | ❌ | YELLOW |
| 12 | BinaryHeap | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 13 | PriorityQueue | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 14 | BinaryTree | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 15 | BinarySearchTree | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 16 | Trie | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 17 | FenwickTree | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 18 | SegmentTree | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 19 | Graph | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 20 | UnionFind | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 21 | WeightedGraph | ✅ | ✅ | ⚠️ | ⚠️ | ⚠️ | ⚠️ | N/A | ⚠️ | ❌ | ❌ | ❌ | ❌ | ⚠️ | ❌ | ❌ | RED |
| 22 | DenseMatrix | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 23 | SparseMatrix | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 24 | BloomFilter | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 25 | SparseArray | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |

## Wave 2 — Missing (to implement)

| # | Structure | Category | Priority |
|---|---|---|---|
| 26 | Bag | Sets | High |
| 27 | MultiSet | Sets | High |
| 28 | SortedMap | Maps | Medium |
| 29 | LinkedHashMap | Maps | Medium |
| 30 | BiMap | Maps | Medium |
| 31 | SortedSet | Sets | Medium |
| 32 | MinHeap | Priority | High |
| 33 | MaxHeap | Priority | High |
| 34 | LinkedList | Linear | High |
| 35 | DoublyLinkedList | Linear | Medium |
| 36 | DynamicArray | Linear | Medium |
| 37 | IntervalTree | Trees | Medium |
| 38 | MerkleTree | Trees | Medium |
| 39 | DirectedGraph | Graphs | Medium |
| 40 | UndirectedGraph | Graphs | Medium |
| 41 | MultiGraph | Graphs | Low |
| 42 | FlowNetwork | Graphs | Medium |
| 43 | TopologicalOrdering | Graphs | Medium |
| 44 | CsrMatrix | Matrix | Low |
| 45 | SuffixArray | Text | Medium |
| 46 | AhoCorasickAutomaton | Text | Medium |
| 47 | SkipList | Probabilistic | High |
| 48 | CountMinSketch | Probabilistic | Medium |
| 49 | HyperLogLog | Probabilistic | Medium |
MATRIX
);

// ============================================================
// 3. STRUCTURE_COMPLEXITY_TABLE.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_COMPLEXITY_TABLE.md', <<< 'COMPLEXITY'
# Structure Complexity Table

Time and space complexity for implemented structures.

No structure is called "fast", "optimized", or "high-performance" without benchmark evidence.

## Linear Structures

| Structure | insert | delete | lookup | traversal | memory |
|---|---|---|---|---|---|
| Sequence | O(1) append | O(n) | O(n) | O(n) | O(n) |
| Stack | O(1) push | O(1) pop | O(1) peek | O(n) | O(n) |
| Queue | O(1) enqueue | O(1) dequeue | O(1) front | O(n) | O(n) |
| Deque | O(1) push | O(1) pop | O(1) peek | O(n) | O(n) |
| RingBuffer | O(1) enqueue | O(1) dequeue | O(1) front | O(n) | O(n) bounded |
| SparseArray | O(1) put | O(1) remove | O(1) get | O(k) | O(k) k=non-null |

## Map Structures

| Structure | insert | delete | lookup | traversal | memory |
|---|---|---|---|---|---|
| Map | O(1) average | O(1) average | O(1) average | O(n) | O(n) |
| OrderedMap | O(1) average | O(1) average | O(1) average | O(n) ordered | O(n) |
| MultiMap | O(1) average | O(k) | O(1) average | O(n) | O(n) |

## Set Structures

| Structure | insert | delete | lookup | traversal | memory |
|---|---|---|---|---|---|
| Set | O(1) average | O(1) average | O(1) average | O(n) | O(n) |
| OrderedSet | O(1) average | O(1) average | O(1) average | O(n) ordered | O(n) |

## Priority Structures

| Structure | insert | extract | peek | traversal | memory |
|---|---|---|---|---|---|
| BinaryHeap | O(log n) | O(log n) | O(1) | O(n) | O(n) |
| PriorityQueue | O(log n) | O(log n) | O(1) | O(n) | O(n) |

## Tree Structures

| Structure | insert | delete | lookup | traversal | memory |
|---|---|---|---|---|---|
| BinaryTree | O(n) worst | O(n) worst | O(n) worst | O(n) | O(n) |
| BinarySearchTree | O(h) | O(h) | O(h) | O(n) | O(n) |
| Trie | O(k) | O(k) | O(k) | O(n) | O(n*k) |
| FenwickTree | O(log n) update | N/A | O(log n) prefix | O(n) build | O(n) |
| SegmentTree | O(n) build | O(log n) update | O(log n) range | O(n) | O(n) |

## Graph Structures

| Structure | addNode | addEdge | hasEdge | traversal | memory |
|---|---|---|---|---|---|
| Graph | O(1) | O(1) | O(degree) | O(V+E) | O(V+E) |
| UnionFind | O(1) | O(α(n)) union | O(α(n)) find | O(n) | O(n) |
| WeightedGraph | O(1) | O(1) | O(degree) | O(V+E) | O(V+E) |

## Matrix Structures

| Structure | get | set | traversal | memory |
|---|---|---|---|---|
| DenseMatrix | O(1) | O(1) | O(n*m) | O(n*m) |
| SparseMatrix | O(1) average | O(1) average | O(k) k=non-default | O(k) |

## Probabilistic Structures

| Structure | add | query | memory |
|---|---|---|---|
| BloomFilter | O(k) | O(k) | O(m) m=bits |

k = hash count, n = elements, m = bits, h = tree height, V = vertices, E = edges
COMPLEXITY
);

// ============================================================
// 4. STRUCTURE_STORAGE_STRATEGIES.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_STORAGE_STRATEGIES.md', <<< 'STORAGE'
# Structure Storage Strategies

Every structure must declare its storage strategy. Storage is never implicit.

## Storage Implementations

| Storage | Location | Purpose | Used By |
|---|---|---|---|
| ArrayStorage | StructureStorage | Simple indexed storage | Stack, SparseArray |
| AssociativeArrayStorage | StructureStorage | Key-value storage | Map, Set, OrderedMap |
| RingBufferStorage | StructureStorage | Bounded circular buffer | Queue, Deque, RingBuffer |
| LinkedNodeStorage | StructureStorage | Singly-linked node chain | (available) |
| BinaryNodeStorage | StructureStorage | Binary tree node | BinaryTree, BST, Trie |
| TreeNodeStorage | StructureStorage | N-ary tree node | (available) |
| GraphAdjacencyStorage | StructureStorage | Adjacency list | Graph, WeightedGraph |
| MatrixDenseStorage | StructureStorage | Row-major matrix | DenseMatrix |
| MatrixSparseStorage | StructureStorage | Sparse coordinate | SparseMatrix |
| BitStringStorage | StructureStorage | Bit string | BloomFilter |

## Storage Selection Rules

1. **Simplest storage that satisfies the invariant wins.**
2. Storage classes are internal capabilities, not public API.
3. Storage implementations are immutable where possible.
4. Storage does not define the structure's public vocabulary.
5. A structure may compose multiple storage types (e.g., LinkedHashMap = AssociativeArrayStorage + LinkedNodeStorage).

## Storage Not Yet Consumed

These storage classes exist but are not yet used by any structure:
- `LinkedNodeStorage` — for LinkedList, SkipList, PersistentList
- `TreeNodeStorage` — for Tree, ExpressionTree, n-ary trees
- `MatrixDenseStorage` — available for AdjacencyMatrix, CountMinSketch
- `MatrixSparseStorage` — available for CsrMatrix, CscMatrix

## Simulation and Model Storage

Structures with mode M or S may use storage classes for modeling purposes only.
They must not claim runtime properties that PHP cannot own.
STORAGE
);

// ============================================================
// 5. STRUCTURE_PUBLIC_SURFACE.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_PUBLIC_SURFACE.md', <<< 'SURFACE'
# Structure Public Surface

PublicSurface/ must stay thin. It receives and delegates. It does not contain behavior.

## Existing Public Facades

| Facade | Delegates To | Status |
|---|---|---|
| Data | ArrayReader, ArrayWriter, ReadNestedValue, WriteNestedValue, SumValues, AverageValues | Stable |
| Sequence | Linear\Sequence | Stable |
| Stack | Linear\Stack | Stable |
| Queue | Linear\Queue | Stable |
| Deque | Linear\Deque | Stable |
| Map | Maps\Map | Stable |
| Set | Sets\Set | Stable |
| OrderedMap | Maps\OrderedMap | Stable |
| OrderedSet | Sets\OrderedSet | Stable |
| MultiMap | Maps\MultiMap | Stable |
| Heap | Priority\BinaryHeap | Stable |
| PriorityQueue | Priority\PriorityQueue | Stable |
| Graph | Graphs\Graph | Stable |
| Matrix | Matrix\DenseMatrix + SparseMatrix | Stable |
| BloomFilter | Probabilistic\BloomFilter | Stable |

## Public Surface Rules

1. One facade per stable structure family.
2. Facade is a factory only — static methods returning capability instances.
3. No public facade before invariant tests are green.
4. No public facade before failure tests are green.
5. No public facade before serialization tests are green.
6. No public facade before documentation exists.
7. No facade that exposes internal storage details.
8. No facade per exotic structure — group by family where appropriate.

## Facades Not Yet Added

These structures from Wave 2 need public facades once implemented:
- `Tree` — for BinaryTree, BinarySearchTree family
- `LinkedList` — for LinkedList, DoublyLinkedList family
SURFACE
);

// ============================================================
// 6. STRUCTURE_SIMULATION_BOUNDARIES.md
// ============================================================
file_put_contents($docsDir . '/STRUCTURE_SIMULATION_BOUNDARIES.md', <<< 'SIMULATION'
# Structure Simulation Boundaries

## Required Boundary Statement

Pure PHP does not own Zend Engine memory layout, CPU cache lines, real lock-free progress guarantees,
or wait-free progress guarantees. Structures in these categories are simulations or runtime-specific
bridges unless backed by an explicit runtime dependency and tests.

## Simulation Structures (Mode S)

These structures are simulations only. Their names must not imply real runtime guarantees.

| Structure | Simulates | Why PHP Cannot Own It |
|---|---|---|
| LockFreeQueue | CPU-level lock-free FIFO | Requires atomic CAS instructions |
| LockFreeStack | CPU-level lock-free LIFO | Requires atomic CAS instructions |
| WaitFreeQueue | CPU-level wait-free FIFO | Requires hardware atomic ops |
| ReadCopyUpdate | RCU read-side critical sections | Requires kernel-level thread scheduling |
| StackFrame | Call stack frame layout | Zend Engine owns the real call stack |
| CallStack | Runtime call stack | Zend Engine owns the real call stack |
| HeapMemory | Process heap allocation | Zend Engine owns PHP memory |
| FreeList | Heap free block tracking | No direct heap access in PHP |
| SlabAllocator | Kernel slab allocator | No kernel memory access in PHP |
| BuddyAllocator | Buddy system page allocation | No direct page table access |
| PageTable | Virtual-to-physical page mapping | OS owns page tables |
| TranslationLookasideBuffer | CPU TLB caching | Hardware-level, not accessible |
| CacheLine | CPU cache line behavior | Hardware-level, not accessible |

## Model Structures (Mode M)

These structures are algorithmic models. They implement the correct algorithm but cannot claim
the same low-level memory/runtime properties as systems languages.

Key model structures: BTree, BPlusTree, FibonacciHeap, SuffixTree, RTree, Octree, VanEmdeBoasTree,
FusionTree, HyperLogLog, PersistentMap, RoaringBitmap, LsmTree, and many more.

## Runtime Bridge Structures (Mode R)

These structures require an explicit runtime backend. They must be named after their backend:

| Structure | Required Backend |
|---|---|
| ConcurrentQueue | Redis, Swoole, parallel, shared memory |
| ConcurrentStack | Redis, Swoole, parallel, shared memory |
| ConcurrentHashMap | Redis, Swoole, parallel |
| BlockingQueue | Redis, Swoole, parallel |
SIMULATION
);

echo "Wave 0 docs generated successfully.\n";
echo "Files:\n";
foreach ([
    'STRUCTURE_ATLAS.md',
    'STRUCTURE_IMPLEMENTATION_MATRIX.md',
    'STRUCTURE_COMPLEXITY_TABLE.md',
    'STRUCTURE_STORAGE_STRATEGIES.md',
    'STRUCTURE_PUBLIC_SURFACE.md',
    'STRUCTURE_SIMULATION_BOUNDARIES.md',
] as $file) {
    $path = $docsDir . '/' . $file;
    echo "  - {$file} (" . strlen(file_get_contents($path)) . " bytes)\n";
}
