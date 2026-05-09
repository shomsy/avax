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
| 21 | WeightedGraph | ✅ | ✅ | ⚠️ | ⚠️ | ✅ | ⚠️ | N/A | ⚠️ | ✅ | ✅ | ❌ | ✅ | ⚠️ | ❌ | ❌ | YELLOW |
| 22 | DenseMatrix | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 23 | SparseMatrix | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 24 | BloomFilter | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 25 | SparseArray | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | GREEN |
| 26 | Bag | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 27 | MinHeap | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 28 | MaxHeap | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 29 | LinkedList | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 30 | DoublyLinkedList | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 31 | DynamicArray | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ❌ | ❌ | GREEN |
| 32 | CountMinSketch | ✅ | ✅ | ✅ | ✅ | N/A | ✅ | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ⚠️ | ❌ | YELLOW |
| 33 | HyperLogLog | ✅ | ✅ | ✅ | ✅ | N/A | N/A | N/A | ✅ | ✅ | ✅ | N/A | ✅ | ✅ | ⚠️ | ❌ | YELLOW |

## WeightedGraph — Remaining Requirements for GREEN

Current status: YELLOW. WeightedGraph has minimum vocabulary and 7 tests but needs:

| Requirement | Effort | Priority |
|---|---|---|
| `removeNode(node)` — remove node and all incident edges | Small | High |
| `removeEdge(from, to)` — remove single edge | Small | High |
| `edges()` — list all edges with weights | Small | Medium |
| Per-structure documentation (when to use / not use) | Small | Medium |
| Edge case tests: self-loops, duplicate edges, weight overwrite | Small | Medium |

Do not promote to GREEN until removeNode, removeEdge, and documentation exist.

## Wave 2 — Still Missing (Roadmap Only)

These structures are listed in the atlas but not yet implemented. They are **not delivery obligations** — each will be evaluated individually before implementation.

| Structure | Category | Priority | Reason to Implement |
|---|---|---|---|
| MultiSet | Sets | Medium | If Bag doesn't cover the use case |
| SortedMap | Maps | Low | If OrderedMap comparator control is insufficient |
| LinkedHashMap | Maps | Low | If insertion-order stability after lookup is needed |
| BiMap | Maps | Low | If bidirectional key-value mapping is needed |
| SortedSet | Sets | Low | If OrderedSet comparator control is insufficient |
| IntervalTree | Trees | Low | If interval overlap search is needed |
| MerkleTree | Trees | Low | If hash-based data integrity verification is needed |
| DirectedGraph | Graphs | Low | If explicit direction enforcement is needed |
| UndirectedGraph | Graphs | Low | If reciprocal adjacency enforcement is needed |
| MultiGraph | Graphs | Low | If parallel edges are needed |
| FlowNetwork | Graphs | Low | If capacity/flow constraints are needed |
| TopologicalOrdering | Graphs | Low | If DAG ordering is needed |
| CsrMatrix | Matrix | Low | If compressed sparse row format is needed |
| SuffixArray | Text | Low | If suffix-based text search is needed |
| AhoCorasickAutomaton | Text | Low | If multi-pattern text search is needed |
| SkipList | Probabilistic | Low | Already attempted — needs proper rewrite |

## Wave 3+ — Advanced and Specialist (Roadmap Only)

All Wave 3, 4, and 5 structures from the master plan remain roadmap-only. Notable examples:

- **Trees**: AVLTree, RedBlackTree, SplayTree, BTree, BPlusTree
- **Text**: SuffixTree, SuffixAutomaton, WaveletTree
- **Compressed**: RoaringBitmap, EliasFano, FMIndex
- **Spatial**: KDTree, QuadTree, RTree
- **Persistent**: PersistentList, PersistentMap, HashArrayMappedTrie
- **Research**: VanEmdeBoasTree, FusionTree, LinkCutTree

None of these are delivery obligations. Each will be evaluated when there is a real use case.
