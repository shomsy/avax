# DataStack/Data Structure Implementation Matrix

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

This matrix converts the structure universe into implementation work. It records category, mode, canonical home, first
allowed wave, and promotion requirement.

Mode key:

```text
N = Native
M = Model
R = Runtime bridge
S = Simulation
```

## Linear

| Structure          | Mode | Wave                 | Canonical home |
|--------------------|------|----------------------|----------------|
| Sequence           | N    | 2                    | Linear         |
| DynamicArray       | N    | 2                    | Linear         |
| DataList           | N    | existing, revalidate | Linear         |
| LinkedList         | N    | 2                    | Linear         |
| SinglyLinkedList   | N    | 2                    | Linear         |
| DoublyLinkedList   | N    | 2                    | Linear         |
| CircularLinkedList | M    | 2                    | Linear         |
| Stack              | N    | 2                    | Linear         |
| Queue              | N    | 2                    | Linear         |
| Deque              | N    | 2                    | Linear         |
| RingBuffer         | N    | 2                    | Linear         |
| CircularBuffer     | N    | 2                    | Linear         |
| SparseArray        | N    | 2                    | Linear         |
| Buffer             | N    | 5                    | Linear         |
| DoubleBuffer       | N    | 5                    | Linear         |

## Maps, sets, and hashing

| Structure     | Mode | Wave                 | Canonical home |
|---------------|------|----------------------|----------------|
| Map           | N    | existing, revalidate | Maps           |
| OrderedMap    | N    | existing, revalidate | Maps           |
| SortedMap     | N    | 2                    | Maps           |
| LinkedHashMap | N    | 2                    | Maps           |
| MultiMap      | N    | existing, revalidate | Maps           |
| BiMap         | N    | 2                    | Maps           |
| EnumMap       | N    | 3                    | Maps           |
| IdentityMap   | N    | 3                    | Maps           |
| WeakMap       | N    | 3                    | Maps           |
| PersistentMap | M    | 3                    | Persistent     |
| Set           | N    | existing, revalidate | Sets           |
| OrderedSet    | N    | existing, revalidate | Sets           |
| SortedSet     | N    | 2                    | Sets           |
| LinkedHashSet | N    | 2                    | Sets           |
| MultiSet      | N    | 2                    | Sets           |
| Bag           | N    | 2                    | Sets           |
| HashSet       | N    | 2                    | Sets           |
| WeakSet       | N    | 3                    | Sets           |
| PersistentSet | M    | 3                    | Persistent     |
| HashTable     | N    | 2                    | Hashing        |
| HashMap       | N    | 2                    | Hashing        |
| HashMultimap  | N    | 2                    | Hashing        |
| HashMultiset  | N    | 2                    | Hashing        |

## Priority

| Structure     | Mode | Wave | Canonical home |
|---------------|------|------|----------------|
| Heap          | N    | 2    | Priority       |
| BinaryHeap    | N    | 2    | Priority       |
| MinHeap       | N    | 2    | Priority       |
| MaxHeap       | N    | 2    | Priority       |
| DaryHeap      | N    | 2    | Priority       |
| PriorityQueue | N    | 2    | Priority       |
| RadixHeap     | N    | 3    | Priority       |
| LeftistHeap   | M    | 3    | Priority       |
| SkewHeap      | M    | 3    | Priority       |
| BinomialHeap  | M    | 3    | Priority       |
| PairingHeap   | M    | 3    | Priority       |
| FibonacciHeap | M    | 3    | Priority       |
| SoftHeap      | M    | 5    | Priority       |
| BrodalQueue   | M    | 5    | Priority       |

## Trees and graph-shaped structures

| Structure        | Mode | Wave | Canonical home |
|------------------|------|------|----------------|
| Tree             | N    | 2    | Trees          |
| BinaryTree       | N    | 2    | Trees          |
| BinarySearchTree | N    | 2    | Trees          |
| Trie             | N    | 2    | Text           |
| SegmentTree      | N    | 2    | Trees          |
| LazySegmentTree  | N    | 3    | Trees          |
| FenwickTree      | N    | 2    | Trees          |
| IntervalTree     | N    | 3    | Trees          |
| AVLTree          | N    | 3    | Trees          |
| RedBlackTree     | N    | 3    | Trees          |
| SplayTree        | N    | 3    | Trees          |
| Treap            | N    | 3    | Trees          |
| BTree            | M    | 3    | Trees          |
| BPlusTree        | M    | 3    | Trees          |
| KDTree           | N    | 3    | Spatial        |
| QuadTree         | N    | 3    | Spatial        |
| RTree            | M    | 3    | Spatial        |
| MerkleTree       | N    | 4    | Trees          |
| SuffixArray      | N    | 3    | Text           |
| SuffixTree       | M    | 4    | Text           |
| LinkCutTree      | M    | 5    | Trees          |
| EulerTourTree    | M    | 5    | Trees          |
| VanEmdeBoasTree  | M    | 5    | Trees          |
| FusionTree       | M    | 5    | Trees          |

## Graphs

| Structure              | Mode | Wave | Canonical home |
|------------------------|------|------|----------------|
| Graph                  | N    | 2    | Graphs         |
| DirectedGraph          | N    | 2    | Graphs         |
| UndirectedGraph        | N    | 2    | Graphs         |
| WeightedGraph          | N    | 2    | Graphs         |
| MultiGraph             | N    | 3    | Graphs         |
| HyperGraph             | M    | 3    | Graphs         |
| AdjacencyList          | N    | 2    | Graphs         |
| AdjacencyMatrix        | N    | 2    | Graphs         |
| IncidenceMatrix        | N    | 3    | Graphs         |
| EdgeList               | N    | 2    | Graphs         |
| CompressedSparseRow    | N    | 3    | Graphs         |
| CompressedSparseColumn | N    | 3    | Graphs         |
| UnionFind              | N    | 2    | Graphs         |
| DisjointSetUnion       | N    | 2    | Graphs         |
| DynamicConnectivity    | M    | 5    | Graphs         |
| FlowNetwork            | N    | 3    | Graphs         |
| TopologicalOrdering    | N    | 3    | Graphs         |
| DominatorTree          | M    | 4    | Graphs         |
| BlockCutTree           | M    | 4    | Graphs         |
| SPQRTree               | M    | 5    | Graphs         |

## Text, numeric, matrix, and spatial

| Structure                  | Mode | Wave | Canonical home |
|----------------------------|------|------|----------------|
| StringBuffer               | N    | 2    | Text           |
| CharacterArray             | N    | 2    | Text           |
| Rope                       | M    | 3    | Text           |
| Cord                       | M    | 3    | Text           |
| GapBuffer                  | M    | 3    | Text           |
| PieceTable                 | M    | 3    | Text           |
| RadixTree                  | N    | 3    | Text           |
| PatriciaTrie               | N    | 3    | Text           |
| CompressedTrie             | N    | 4    | Text           |
| SuffixAutomaton            | M    | 4    | Text           |
| DirectedAcyclicWordGraph   | M    | 4    | Text           |
| AhoCorasickAutomaton       | N    | 3    | Text           |
| FiniteAutomaton            | N    | 3    | Text           |
| RegularExpressionAutomaton | M    | 4    | Text           |
| WaveletTree                | M    | 4    | Text           |
| WaveletMatrix              | M    | 4    | Text           |
| Vector                     | N    | 2    | Numeric        |
| Tensor                     | M    | 4    | Numeric        |
| BitVector                  | N    | 2    | Numeric        |
| BitSet                     | N    | 2    | Numeric        |
| Bitmap                     | N    | 3    | Numeric        |
| BitMatrix                  | N    | 3    | Numeric        |
| Matrix                     | N    | 2    | Matrix         |
| DenseMatrix                | N    | 2    | Matrix         |
| SparseMatrix               | N    | 2    | Matrix         |
| CsrMatrix                  | N    | 3    | Matrix         |
| CscMatrix                  | N    | 3    | Matrix         |
| CooMatrix                  | N    | 3    | Matrix         |
| DiagonalMatrix             | N    | 3    | Matrix         |
| TriangularMatrix           | N    | 3    | Matrix         |
| BandedMatrix               | N    | 3    | Matrix         |
| SpatialHash                | N    | 4    | Spatial        |
| Geohash                    | N    | 4    | Spatial        |
| ZOrderCurve                | N    | 4    | Spatial        |
| HilbertCurve               | M    | 4    | Spatial        |
| VoronoiDiagram             | M    | 4    | Spatial        |
| DelaunayTriangulation      | M    | 4    | Spatial        |

## Persistent, probabilistic, compressed, and storage models

| Structure                | Mode | Wave | Canonical home |
|--------------------------|------|------|----------------|
| PersistentList           | N    | 3    | Persistent     |
| PersistentStack          | N    | 3    | Persistent     |
| PersistentQueue          | M    | 3    | Persistent     |
| PersistentVector         | M    | 3    | Persistent     |
| PersistentSegmentTree    | M    | 3    | Persistent     |
| FingerTree               | M    | 4    | Functional     |
| Zipper                   | N    | 3    | Functional     |
| OkasakiQueue             | N    | 3    | Functional     |
| FunctionalRedBlackTree   | M    | 3    | Functional     |
| HashArrayMappedTrie      | M    | 3    | Functional     |
| RelaxedRadixBalancedTree | M    | 4    | Functional     |
| ClojurePersistentVector  | M    | 4    | Functional     |
| ImmutableVector          | M    | 3    | Functional     |
| BloomFilter              | N    | 2    | Probabilistic  |
| CountingBloomFilter      | N    | 3    | Probabilistic  |
| StableBloomFilter        | M    | 3    | Probabilistic  |
| CuckooFilter             | M    | 4    | Probabilistic  |
| QuotientFilter           | M    | 4    | Probabilistic  |
| SkipList                 | N    | 3    | Probabilistic  |
| CountMinSketch           | N    | 3    | Probabilistic  |
| HyperLogLog              | N    | 3    | Probabilistic  |
| MinHash                  | N    | 3    | Probabilistic  |
| SimHash                  | N    | 3    | Probabilistic  |
| TDigest                  | M    | 4    | Probabilistic  |
| KllSketch                | M    | 4    | Probabilistic  |
| ReservoirSampling        | N    | 3    | Probabilistic  |
| BitPackedArray           | N    | 4    | Compressed     |
| RunLengthEncodedArray    | N    | 4    | Compressed     |
| EliasFano                | M    | 4    | Compressed     |
| RoaringBitmap            | M    | 4    | Compressed     |
| SuccinctTree             | M    | 4    | Compressed     |
| SuccinctGraph            | M    | 4    | Compressed     |
| CompressedSuffixArray    | M    | 4    | Compressed     |
| FMIndex                  | M    | 4    | Compressed     |
| LsmTree                  | M    | 4    | StorageModels  |
| SStable                  | M    | 4    | StorageModels  |
| Memtable                 | N    | 4    | StorageModels  |
| HashIndex                | N    | 4    | StorageModels  |
| BitmapIndex              | N    | 4    | StorageModels  |
| InvertedIndex            | N    | 4    | StorageModels  |
| ColumnStore              | M    | 4    | StorageModels  |
| RowStore                 | M    | 4    | StorageModels  |
| LogStructuredStorage     | M    | 4    | StorageModels  |

## Algorithmic, concurrent, and simulated runtime

| Structure                  | Mode | Wave | Canonical home   |
|----------------------------|------|------|------------------|
| SparseTable                | N    | 3    | Algorithms       |
| MonotonicQueue             | N    | 3    | Algorithms       |
| MonotonicStack             | N    | 3    | Algorithms       |
| CentroidDecomposition      | M    | 4    | Algorithms       |
| SquareRootDecomposition    | N    | 3    | Algorithms       |
| MosAlgorithm               | M    | 4    | Algorithms       |
| DancingLinks               | N    | 4    | Algorithms       |
| ConcurrentQueue            | R    | 5    | ConcurrentModels |
| ConcurrentStack            | R    | 5    | ConcurrentModels |
| ConcurrentHashMap          | R    | 5    | ConcurrentModels |
| ConcurrentSkipList         | M    | 5    | ConcurrentModels |
| BlockingQueue              | R    | 5    | ConcurrentModels |
| WorkStealingDeque          | M    | 5    | ConcurrentModels |
| CopyOnWriteArray           | N    | 5    | ConcurrentModels |
| CopyOnWriteList            | N    | 5    | ConcurrentModels |
| ReadCopyUpdate             | S    | 5    | ConcurrentModels |
| LockFreeQueue              | S    | 5    | ConcurrentModels |
| LockFreeStack              | S    | 5    | ConcurrentModels |
| WaitFreeQueue              | S    | 5    | ConcurrentModels |
| StackFrame                 | S    | 5    | SimulatedRuntime |
| CallStack                  | S    | 5    | SimulatedRuntime |
| HeapMemory                 | S    | 5    | SimulatedRuntime |
| FreeList                   | S    | 5    | SimulatedRuntime |
| MemoryPool                 | M    | 5    | SimulatedRuntime |
| SlabAllocator              | S    | 5    | SimulatedRuntime |
| BuddyAllocator             | S    | 5    | SimulatedRuntime |
| PageTable                  | S    | 5    | SimulatedRuntime |
| TranslationLookasideBuffer | S    | 5    | SimulatedRuntime |
| CacheLine                  | S    | 5    | SimulatedRuntime |
| ObjectPool                 | M    | 5    | SimulatedRuntime |

## Promotion rule

Every `existing, revalidate` item needs the same proof as a new structure before it can be marked complete under this
matrix.
