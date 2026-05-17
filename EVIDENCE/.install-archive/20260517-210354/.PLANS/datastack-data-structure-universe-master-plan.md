# DataStack Data Structure Universe Master Plan

Status: planned / evidence artifact
Owner: DataStack/Data
Created: 2026-05-09
Scope: `components/DataStack/Data/**`, future docs under `docs/DataStack/Data/**`, and focused tests under
`tests/Unit/Components/DataStack/Data/**`

This plan turns the requested "all data structures" roadmap into an AvaX-compliant execution plan.

It is intentionally a plan, not production code. It must not create empty structure folders or placeholder PHP classes.
Every future implementation step must prove behavior, invariants, storage, failure behavior, tests, documentation, and
complexity evidence.

## 0. Governance Position

### Active mode

Standard Mode.

### Work performed by this artifact

Create an enterprise-grade execution plan in `EVIDENCE/.PLANS`.

### Work not performed by this artifact

This artifact does not add production PHP classes. It does not add public API. It does not claim any new data structure
is implemented, green, production-ready, secure, fast, or complete.

### Governance documents read

- `AGENTS.md`
- `.agents/GOVERNANCE_INDEX.md`
- `CURRENT_TRUTH.md`
- `EVIDENCE/EXECUTION.md`
- `.agents/management/ACTIVE.md`
- `.agents/management/TODO.md`
- `.agents/management/BUGS.md`
- `how-to-write-avax.md`
- `.agents/how-to/how-to-architecture.md`
- `.agents/how-to/how-to-design-components.md`
- `.agents/how-to/how-to-use-advanced-architecture-patterns.md`
- `.agents/how-to/how-to-document.md`
- `.agents/how-to/how-to-production-readiness.md`
- `.agents/how-to/how-to-system-security.md`
- `.agents/how-to/how-to-system-performance.md`
- `.agents/how-to/how-to-coding-standards.md`
- `.agents/how-to/how-to-code-style.md`
- `.agents/how-to/how-to-clean-code.md`
- `.agents/how-to/how-to-unit-test.md`
- `.agents/how-to/how-to-code-review.md`

### Applied rules

- Folder says flow or capability.
- Unit says responsibility.
- Function says exact action.
- PublicSurface receives and delegates.
- Flows execute complete behavior.
- Capabilities power reusable behavior.
- Configuration assembles.
- Foundation stays small.
- No production behavior without tests.
- No performance claim without complexity proof or benchmark evidence.
- No simulation may claim runtime or CPU-level guarantees that PHP cannot own.
- No public facade is added until the implementation is stable and tested.

### Forbidden scope for this plan

- Empty production folders.
- Placeholder structure classes.
- PublicSurface expansion for exotic or unproven structures.
- Generic bucket folders for vague pattern words.
- Fake "lock-free", "wait-free", "CPU memory", or "database engine" claims.
- Copying old or theoretical structure lists into PHP without invariant tests.
- Making every structure inherit from one collection-shaped interface.

## 1. Plan Thesis

DataStack/Data becomes the AvaX data morphology engine.

The universe is not a pile of classes. A structure is:

```text
data + invariant + behavior
```

If the invariant is missing, it is not a data structure.
If storage is implicit, the design is incomplete.
If PHP cannot truthfully provide the runtime property, the implementation must be a model, simulation, or runtime
bridge with explicit boundaries.

## 2. Implementation Modes

| Mode           | Meaning                                                                                                                             | Claim allowed                                              |
|----------------|-------------------------------------------------------------------------------------------------------------------------------------|------------------------------------------------------------|
| Native         | Plain PHP can implement the structure truthfully with normal userland behavior.                                                     | Correct behavior and documented complexity profile.        |
| Model          | PHP can model the algorithm correctly, but not necessarily with the same low-level memory/runtime properties as a systems language. | Correct algorithmic model, no low-level performance claim. |
| Runtime Bridge | AvaX can expose a runtime-specific boundary backed by Redis, Swoole, parallel, shared memory, or another runtime dependency.        | Boundary behavior only for the named runtime backend.      |
| Simulation     | PHP can explain and simulate the concept, but cannot own the CPU/runtime/memory guarantee.                                          | Educational or validation model only.                      |

Mode is mandatory in the registry for every structure.

## 3. Existing Baseline

The repository already contains `components/DataStack/Data/System` with canonical component shape:

```text
PublicSurface/
Flows/
Capabilities/
Configuration/
Foundation/
```

Existing Data structure capability files include:

```text
Capabilities/Structures/Foundation/DataStructure.php
Capabilities/Structures/Functional/*
Capabilities/Structures/Linear/Sequence.php
Capabilities/Structures/Linear/DataList.php
Capabilities/Structures/Maps/Map.php
Capabilities/Structures/Maps/MultiMap.php
Capabilities/Structures/Maps/OrderedMap.php
Capabilities/Structures/Sets/Set.php
Capabilities/Structures/Sets/OrderedSet.php
PublicSurface/Sequence.php
PublicSurface/Map.php
PublicSurface/MultiMap.php
PublicSurface/OrderedMap.php
PublicSurface/Set.php
PublicSurface/OrderedSet.php
```

This plan must build from the existing component shape. It must not invent a second component or duplicate the
ownership of DataStack/Database, DataStack/Persistence, Operations/Concurrency, or Operations/Parallelism.

## 4. AvaX-Normalized Target Shape

The raw request includes useful concepts, but some names are generic pattern language. AvaX translates those into exact
responsibility names before implementation.

Target shape:

```text
components/DataStack/Data/
  System/
    PublicSurface/
      Data.php
      Sequence.php
      Stack.php
      Queue.php
      Deque.php
      Map.php
      Set.php
      OrderedMap.php
      OrderedSet.php
      MultiMap.php
      Heap.php
      PriorityQueue.php
      Tree.php
      Graph.php
      Matrix.php
      BloomFilter.php

    Capabilities/
      Forms/
      Shapes/
      Operators/
      Codecs/
      Lenses/
      Structures/
        Foundation/
        StructureStorage/
        Linear/
        Hashing/
        Maps/
        Sets/
        Priority/
        Trees/
        Graphs/
        Text/
        Numeric/
        Matrix/
        Spatial/
        Persistent/
        Functional/
        Probabilistic/
        Compressed/
        StorageModels/
        Algorithms/
        ConcurrentModels/
        SimulatedRuntime/

    Configuration/
      DataConfiguration.php
      StructureConfiguration.php

    Foundation/
      Values/
      Failure/
      Comparison/
      Hashing/
      Iteration/
      Mutability/
```

### Naming decisions

- Family promises live in `Capabilities/Structures/Foundation` unless a more exact capability owner is proven.
- Storage mechanics live in `Capabilities/Structures/StructureStorage`.
- Runtime-specific concurrency boundaries live under exact names in `ConcurrentModels`.
- CPU and memory concepts live under `SimulatedRuntime` unless backed by a real runtime boundary.
- Database-like structures live under `StorageModels` and must not duplicate `components/DataStack/Database`.
- Abstract data type promises do not get a user-facing implementation folder by default.

## 5. Public Surface Strategy

PublicSurface must stay thin.

Allowed first public facades:

```text
Sequence
Stack
Queue
Deque
Map
Set
OrderedMap
OrderedSet
MultiMap
Heap
PriorityQueue
Tree
Graph
Matrix
BloomFilter
```

Not allowed by default:

```text
one public facade per exotic structure
public facade before invariant tests
public facade before failure tests
public facade before serialization tests
public facade before documentation
public facade that exposes internal storage details
```

## 6. Structure Kernel

The Structure Kernel must be completed before broad implementation.

### 6.1 Family promises

Location:

```text
Capabilities/Structures/Foundation/
```

Initial family promises:

```text
DataStructure
LinearStructure
AssociativeStructure
SetStructure
MapStructure
TreeStructure
GraphStructure
HeapStructure
MatrixStructure
ProbabilisticStructure
PersistentStructure
ConcurrentStructure
SimulatedStructure
```

Rule:

Do not force every structure to look like a collection. Graphs, matrices, filters, trees, and storage models must keep
their own vocabulary.

Minimal shared behavior:

```text
count
isEmpty
toArray where it has stable meaning
toJson where it has stable meaning
```

### 6.2 Foundation values

Location:

```text
System/Foundation/Values/
```

Planned values:

```text
Pair
Tuple
Entry
Range
Interval
Coordinate
Point
Edge
WeightedEdge
Priority
```

### 6.3 Comparison and hashing

Location:

```text
System/Foundation/Comparison/
System/Foundation/Hashing/
```

Planned responsibilities:

```text
Comparator
Equality
Ordering
HashFunction
StableHash
StringHash
ObjectHash
```

### 6.4 Failure model

Location:

```text
System/Foundation/Failure/
```

Planned failures:

```text
EmptyStructure
InvalidStructureOperation
DuplicateKey
MissingKey
IndexOutOfBounds
InvalidCapacity
StructureInvariantBroken
```

### 6.5 Structure storage

Location:

```text
Capabilities/Structures/StructureStorage/
```

Planned storage responsibilities:

```text
ArrayStorage
PackedArrayStorage
AssociativeArrayStorage
RingBufferStorage
LinkedNodeStorage
BinaryNodeStorage
TreeNodeStorage
GraphAdjacencyStorage
MatrixDenseStorage
MatrixSparseStorage
BitStringStorage
RegisterStorage
PageStorage
FileBackedStorage
SimulatedMemoryStorage
```

Storage strategy is mandatory in every structure registry entry.

## 7. Execution Waves

### Wave 0 - Inventory and Atlas

Goal:

Create a complete registry before adding production behavior.

Artifacts:

```text
EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md
docs/DataStack/Data/STRUCTURE_ATLAS.md
docs/DataStack/Data/STRUCTURE_IMPLEMENTATION_MATRIX.md
docs/DataStack/Data/STRUCTURE_COMPLEXITY_TABLE.md
docs/DataStack/Data/STRUCTURE_STORAGE_STRATEGIES.md
docs/DataStack/Data/STRUCTURE_PUBLIC_SURFACE.md
docs/DataStack/Data/STRUCTURE_SIMULATION_BOUNDARIES.md
```

Done when:

```text
every planned structure has category, mode, storage, invariant, test plan, docs plan, and promotion gate
```

### Wave 1 - Structure Kernel

Goal:

Implement family promises, failures, comparison, hashing, storage primitives, serialization rules, and mutation policy.

Stable structures allowed:

```text
none beyond kernel behavior
```

Done when:

```text
kernel tests pass
family promises do not overfit collection behavior
storage primitives are covered by unit tests
simulation boundaries are documented
```

### Wave 2 - Stable Production Structures

Goal:

Implement the structures most likely to be used by application and framework code.

Structures:

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

Done when:

```text
each structure has invariant tests, edge-case tests, serialization tests, mutation tests, docs, and complexity notes
```

### Wave 3 - Advanced Production Structures

Structures:

```text
AVLTree
RedBlackTree
SplayTree
Treap
BTree
BPlusTree
SkipList
SuffixArray
AhoCorasickAutomaton
Rope
KDTree
QuadTree
RTree
CountMinSketch
HyperLogLog
PersistentList
PersistentMap
HashArrayMappedTrie
```

Done when:

```text
advanced invariants are executable through tests
complexity notes are written
benchmarks exist for hot structures only
```

### Wave 4 - Specialist Structures

Structures:

```text
SuffixTree
SuffixAutomaton
MerkleTree
WaveletTree
WaveletMatrix
FMIndex
CompressedSuffixArray
RoaringBitmap
EliasFano
LsmTree
SStable
ColumnStore
RowStore
SpatialHash
VoronoiDiagram
DelaunayTriangulation
```

Done when:

```text
specialist docs explain when not to use each structure
storage and memory behavior are bounded
tests prove canonical examples and failure behavior
```

### Wave 5 - Research, Runtime Bridge, and Simulation Structures

Structures:

```text
LinkCutTree
EulerTourTree
VanEmdeBoasTree
FusionTree
BrodalQueue
SoftHeap
DynamicConnectivity
SPQRTree
LockFreeQueue
WaitFreeQueue
MemoryPool
SlabAllocator
BuddyAllocator
PageTable
TranslationLookasideBuffer
CacheLine
```

Done when:

```text
every non-native structure is explicitly labeled model, runtime bridge, or simulation
no runtime guarantee is claimed without a real runtime backend and tests
```

## 8. Complete Registry

Mode key:

```text
N = Native
M = Model
R = Runtime Bridge
S = Simulation
```

### 8.1 Linear structures

| Structure          | Location | Mode | Storage                       | Invariant                                  |
|--------------------|----------|------|-------------------------------|--------------------------------------------|
| Sequence           | Linear   | N    | PackedArrayStorage            | ordered finite values                      |
| DynamicArray       | Linear   | N    | PackedArrayStorage            | indexed sequence with append growth        |
| DataList           | Linear   | N    | PackedArrayStorage            | list vocabulary over Sequence              |
| LinkedList         | Linear   | N    | LinkedNodeStorage             | node chain traversal                       |
| SinglyLinkedList   | Linear   | N    | LinkedNodeStorage             | next-only node chain                       |
| DoublyLinkedList   | Linear   | N    | LinkedNodeStorage             | previous and next links stay consistent    |
| CircularLinkedList | Linear   | M    | LinkedNodeStorage             | tail points to head                        |
| Stack              | Linear   | N    | ArrayStorage                  | LIFO                                       |
| Queue              | Linear   | N    | RingBufferStorage             | FIFO                                       |
| Deque              | Linear   | N    | RingBufferStorage             | double-ended FIFO operations               |
| PriorityQueue      | Priority | N    | BinaryHeap                    | dequeue follows priority order             |
| RingBuffer         | Linear   | N    | RingBufferStorage             | bounded circular index movement            |
| CircularBuffer     | Linear   | N    | RingBufferStorage             | bounded overwrite or rejection policy      |
| SparseArray        | Linear   | N    | AssociativeArrayStorage       | sparse integer keys map to values          |
| StringBuffer       | Text     | N    | ArrayStorage or string chunks | mutable text buffer model                  |
| Rope               | Text     | M    | BinaryNodeStorage             | concatenation tree stores weight invariant |
| GapBuffer          | Text     | M    | PackedArrayStorage            | gap represents cursor insertion zone       |
| PieceTable         | Text     | M    | ArrayStorage                  | immutable original/add buffers plus pieces |

### 8.2 Map, set, and hashing structures

| Structure     | Location   | Mode | Storage                                 | Invariant                                      |
|---------------|------------|------|-----------------------------------------|------------------------------------------------|
| Map           | Maps       | N    | AssociativeArrayStorage                 | key maps to one current value                  |
| OrderedMap    | Maps       | N    | AssociativeArrayStorage                 | insertion order is preserved                   |
| SortedMap     | Maps       | N    | BinarySearchTree or sorted array        | comparator order is preserved                  |
| LinkedHashMap | Maps       | N    | AssociativeArrayStorage plus link order | insertion order is stable after lookup         |
| MultiMap      | Maps       | N    | AssociativeArrayStorage                 | key maps to many values                        |
| BiMap         | Maps       | N    | dual Map storage                        | keys and values are both unique                |
| EnumMap       | Maps       | N    | AssociativeArrayStorage                 | keys belong to one enum type                   |
| IdentityMap   | Maps       | N    | ObjectHash                              | object identity is the key                     |
| WeakMap       | Maps       | N    | PHP WeakMap                             | weak object keys do not keep objects alive     |
| PersistentMap | Persistent | M    | HashArrayMappedTrie                     | mutation returns new map and preserves old map |
| Set           | Sets       | N    | AssociativeArrayStorage                 | value exists once                              |
| OrderedSet    | Sets       | N    | AssociativeArrayStorage                 | insertion order is preserved                   |
| SortedSet     | Sets       | N    | BinarySearchTree or sorted array        | comparator order is preserved                  |
| LinkedHashSet | Sets       | N    | AssociativeArrayStorage plus link order | insertion order is stable                      |
| MultiSet      | Sets       | N    | AssociativeArrayStorage                 | value maps to positive count                   |
| Bag           | Sets       | N    | AssociativeArrayStorage                 | duplicate count is preserved                   |
| HashSet       | Sets       | N    | AssociativeArrayStorage                 | hashed value exists once                       |
| WeakSet       | Sets       | N    | PHP WeakMap                             | weak object membership                         |
| PersistentSet | Persistent | M    | HashArrayMappedTrie                     | mutation returns new set and preserves old set |
| HashTable     | Hashing    | N    | AssociativeArrayStorage                 | hash bucket lookup has collision policy        |
| HashMap       | Hashing    | N    | AssociativeArrayStorage                 | hashed key maps to value                       |
| HashMultimap  | Hashing    | N    | AssociativeArrayStorage                 | hashed key maps to many values                 |
| HashMultiset  | Hashing    | N    | AssociativeArrayStorage                 | hashed value maps to count                     |

### 8.3 Priority structures

| Structure     | Location | Mode | Storage                      | Invariant                           |
|---------------|----------|------|------------------------------|-------------------------------------|
| Heap          | Priority | N    | ArrayStorage                 | parent priority dominates children  |
| BinaryHeap    | Priority | N    | ArrayStorage                 | binary heap order                   |
| MinHeap       | Priority | N    | ArrayStorage                 | smallest priority at root           |
| MaxHeap       | Priority | N    | ArrayStorage                 | largest priority at root            |
| DaryHeap      | Priority | N    | ArrayStorage                 | d-ary heap order                    |
| BinomialHeap  | Priority | M    | TreeNodeStorage              | binomial tree forest order          |
| FibonacciHeap | Priority | M    | TreeNodeStorage              | heap forest with lazy consolidation |
| PairingHeap   | Priority | M    | TreeNodeStorage              | pairwise merge heap order           |
| LeftistHeap   | Priority | M    | TreeNodeStorage              | null-path length ordering           |
| SkewHeap      | Priority | M    | TreeNodeStorage              | self-adjusting merge heap           |
| BrodalQueue   | Priority | M    | TreeNodeStorage              | research-grade priority queue model |
| SoftHeap      | Priority | M    | ArrayStorage or tree storage | allows bounded key corruption       |
| RadixHeap     | Priority | N    | ArrayStorage                 | monotonic integer priority buckets  |
| PriorityQueue | Priority | N    | BinaryHeap                   | dequeue follows priority order      |

### 8.4 Trees

| Structure               | Location | Mode | Storage           | Invariant                                      |
|-------------------------|----------|------|-------------------|------------------------------------------------|
| Tree                    | Trees    | N    | TreeNodeStorage   | acyclic parent-child ownership                 |
| BinaryTree              | Trees    | N    | BinaryNodeStorage | each node has at most two children             |
| BinarySearchTree        | Trees    | N    | BinaryNodeStorage | left < node < right by comparator              |
| AVLTree                 | Trees    | N    | BinaryNodeStorage | balance factor is -1, 0, or 1                  |
| RedBlackTree            | Trees    | N    | BinaryNodeStorage | red-black color and black-height rules         |
| SplayTree               | Trees    | N    | BinaryNodeStorage | access splays node toward root                 |
| Treap                   | Trees    | N    | BinaryNodeStorage | BST key order plus heap priority order         |
| CartesianTree           | Trees    | M    | BinaryNodeStorage | in-order sequence plus heap order              |
| ScapegoatTree           | Trees    | M    | BinaryNodeStorage | subtree rebuild balance rule                   |
| AATree                  | Trees    | M    | BinaryNodeStorage | AA level invariant                             |
| WeightBalancedTree      | Trees    | M    | BinaryNodeStorage | subtree weight ratio bound                     |
| BTree                   | Trees    | M    | PageStorage       | node keys are sorted and page capacity bounded |
| BPlusTree               | Trees    | M    | PageStorage       | values live in leaves and leaves are linked    |
| BStarTree               | Trees    | M    | PageStorage       | high node fill factor model                    |
| TwoThreeTree            | Trees    | M    | TreeNodeStorage   | each internal node has 2 or 3 children         |
| TwoThreeFourTree        | Trees    | M    | TreeNodeStorage   | each internal node has 2, 3, or 4 children     |
| SegmentTree             | Trees    | N    | ArrayStorage      | interval nodes aggregate ranges                |
| LazySegmentTree         | Trees    | N    | ArrayStorage      | deferred range updates remain composable       |
| FenwickTree             | Trees    | N    | ArrayStorage      | binary indexed prefix aggregates               |
| IntervalTree            | Trees    | N    | BinaryNodeStorage | interval overlap search uses max endpoint      |
| RangeTree               | Trees    | M    | TreeNodeStorage   | multi-dimensional ordered range search         |
| MerkleTree              | Trees    | N    | BinaryNodeStorage | parent hash commits to children                |
| ExpressionTree          | Trees    | N    | TreeNodeStorage   | expression nodes preserve evaluation order     |
| ParseTree               | Trees    | M    | TreeNodeStorage   | grammar production tree                        |
| DecisionTree            | Trees    | M    | TreeNodeStorage   | branch predicate reaches leaf decision         |
| TournamentTree          | Trees    | N    | ArrayStorage      | winner propagates upward                       |
| HeapOrderedTree         | Trees    | N    | TreeNodeStorage   | parent heap priority dominates child priority  |
| LinkCutTree             | Trees    | M    | TreeNodeStorage   | dynamic tree model                             |
| EulerTourTree           | Trees    | M    | Sequence storage  | dynamic forest represented by tours            |
| HeavyLightDecomposition | Trees    | M    | ArrayStorage      | paths decompose into heavy chains              |
| VanEmdeBoasTree         | Trees    | M    | ArrayStorage      | integer universe recursive clustering          |
| FusionTree              | Trees    | M    | ArrayStorage      | word-level integer search model                |

### 8.5 Graph structures

| Structure              | Location | Mode | Storage               | Invariant                                      |
|------------------------|----------|------|-----------------------|------------------------------------------------|
| Graph                  | Graphs   | N    | GraphAdjacencyStorage | nodes and edges stay consistent                |
| DirectedGraph          | Graphs   | N    | GraphAdjacencyStorage | edge direction is preserved                    |
| UndirectedGraph        | Graphs   | N    | GraphAdjacencyStorage | reciprocal adjacency is preserved              |
| WeightedGraph          | Graphs   | N    | GraphAdjacencyStorage | every edge has a weight                        |
| MultiGraph             | Graphs   | N    | GraphAdjacencyStorage | parallel edges are allowed and counted         |
| HyperGraph             | Graphs   | M    | GraphAdjacencyStorage | edge may connect more than two nodes           |
| AdjacencyList          | Graphs   | N    | GraphAdjacencyStorage | node maps to neighbor list                     |
| AdjacencyMatrix        | Graphs   | N    | MatrixDenseStorage    | matrix cell represents edge relation           |
| IncidenceMatrix        | Graphs   | N    | MatrixDenseStorage    | rows and columns represent node-edge incidence |
| EdgeList               | Graphs   | N    | ArrayStorage          | graph represented by edge collection           |
| CompressedSparseRow    | Graphs   | N    | ArrayStorage          | row offsets index compressed adjacency         |
| CompressedSparseColumn | Graphs   | N    | ArrayStorage          | column offsets index compressed adjacency      |
| UnionFind              | Graphs   | N    | ArrayStorage          | representative identifies connected component  |
| DisjointSetUnion       | Graphs   | N    | ArrayStorage          | same behavior as UnionFind vocabulary          |
| DynamicConnectivity    | Graphs   | M    | TreeNodeStorage       | connectivity changes over time                 |
| FlowNetwork            | Graphs   | N    | GraphAdjacencyStorage | capacity and flow constraints are preserved    |
| TopologicalOrdering    | Graphs   | N    | ArrayStorage          | DAG order places sources before dependents     |
| DominatorTree          | Graphs   | M    | TreeNodeStorage       | dominance relation in directed graph           |
| BlockCutTree           | Graphs   | M    | TreeNodeStorage       | articulation/block decomposition               |
| SPQRTree               | Graphs   | M    | TreeNodeStorage       | triconnected decomposition model               |

### 8.6 Text and automata structures

| Structure                  | Location | Mode | Storage                              | Invariant                                        |
|----------------------------|----------|------|--------------------------------------|--------------------------------------------------|
| CharacterArray             | Text     | N    | PackedArrayStorage                   | characters keep order                            |
| Cord                       | Text     | M    | BinaryNodeStorage                    | concatenation tree similar to Rope               |
| Trie                       | Text     | N    | TreeNodeStorage                      | path labels form prefixes                        |
| PrefixTree                 | Text     | N    | TreeNodeStorage                      | alias vocabulary for Trie                        |
| RadixTree                  | Text     | N    | TreeNodeStorage                      | edges store compressed labels                    |
| PatriciaTrie               | Text     | N    | TreeNodeStorage                      | unary paths are compressed                       |
| CompressedTrie             | Text     | N    | TreeNodeStorage                      | compressed prefix representation                 |
| SuffixArray                | Text     | N    | ArrayStorage                         | suffix positions sorted lexicographically        |
| SuffixTree                 | Text     | M    | TreeNodeStorage                      | compressed trie of suffixes                      |
| SuffixAutomaton            | Text     | M    | ArrayStorage                         | states represent end-position equivalence        |
| DirectedAcyclicWordGraph   | Text     | M    | GraphAdjacencyStorage                | minimal acyclic word automaton                   |
| AhoCorasickAutomaton       | Text     | N    | GraphAdjacencyStorage                | failure links preserve multi-pattern search      |
| FiniteAutomaton            | Text     | N    | GraphAdjacencyStorage                | state transition relation is total as configured |
| RegularExpressionAutomaton | Text     | M    | GraphAdjacencyStorage or preg bridge | regex behavior is represented or delegated       |
| WaveletTree                | Text     | M    | BitStringStorage                     | rank/select over symbol ranges                   |
| WaveletMatrix              | Text     | M    | BitStringStorage                     | wavelet matrix bit-level representation          |

### 8.7 Numeric, matrix, tensor, and bit structures

| Structure        | Location | Mode | Storage             | Invariant                                     |
|------------------|----------|------|---------------------|-----------------------------------------------|
| Vector           | Numeric  | N    | PackedArrayStorage  | ordered numeric coordinates                   |
| Tensor           | Numeric  | M    | PackedArrayStorage  | shape dimensions match stored values          |
| BitVector        | Numeric  | N    | BitStringStorage    | bits indexed by position                      |
| BitSet           | Numeric  | N    | BitStringStorage    | integer membership represented by bits        |
| Bitmap           | Numeric  | N    | BitStringStorage    | pixel or bit cell state                       |
| BitMatrix        | Numeric  | N    | BitStringStorage    | two-dimensional bit cells                     |
| Matrix           | Matrix   | N    | MatrixDenseStorage  | row and column dimensions are stable          |
| DenseMatrix      | Matrix   | N    | MatrixDenseStorage  | every cell is materialized                    |
| SparseMatrix     | Matrix   | N    | MatrixSparseStorage | only non-default cells are stored             |
| CsrMatrix        | Matrix   | N    | MatrixSparseStorage | compressed sparse row form                    |
| CscMatrix        | Matrix   | N    | MatrixSparseStorage | compressed sparse column form                 |
| CooMatrix        | Matrix   | N    | MatrixSparseStorage | coordinate list form                          |
| DiagonalMatrix   | Matrix   | N    | MatrixSparseStorage | non-default cells are on diagonal             |
| TriangularMatrix | Matrix   | N    | MatrixSparseStorage | cells outside triangle are default            |
| BandedMatrix     | Matrix   | N    | MatrixSparseStorage | non-default cells stay inside configured band |

### 8.8 Spatial structures

| Structure               | Location | Mode | Storage                                   | Invariant                                        |
|-------------------------|----------|------|-------------------------------------------|--------------------------------------------------|
| Point                   | Spatial  | N    | Value object                              | coordinate dimensionality is stable              |
| BoundingBox             | Spatial  | N    | Value object                              | min bounds do not exceed max bounds              |
| Grid                    | Spatial  | N    | MatrixDenseStorage or MatrixSparseStorage | cells map to spatial buckets                     |
| SpatialHash             | Spatial  | N    | AssociativeArrayStorage                   | coordinate maps to bucket key                    |
| Geohash                 | Spatial  | N    | StringHash                                | spatial point maps to geohash string             |
| KDTree                  | Spatial  | N    | BinaryNodeStorage                         | split dimension alternates or is selected        |
| QuadTree                | Spatial  | N    | TreeNodeStorage                           | region subdivides into four quadrants            |
| Octree                  | Spatial  | M    | TreeNodeStorage                           | region subdivides into eight octants             |
| RTree                   | Spatial  | M    | TreeNodeStorage                           | bounding rectangles cover child entries          |
| RPlusTree               | Spatial  | M    | TreeNodeStorage                           | region overlap is reduced by split policy        |
| RStarTree               | Spatial  | M    | TreeNodeStorage                           | reinsertion and split heuristics model           |
| BallTree                | Spatial  | M    | TreeNodeStorage                           | ball radius covers child points                  |
| VPTree                  | Spatial  | M    | TreeNodeStorage                           | metric partition by vantage point                |
| BSPTree                 | Spatial  | M    | TreeNodeStorage                           | space partitioned by hyperplanes                 |
| CoverTree               | Spatial  | M    | TreeNodeStorage                           | nested cover levels                              |
| BoundingVolumeHierarchy | Spatial  | M    | TreeNodeStorage                           | parent volume bounds child volumes               |
| ZOrderCurve             | Spatial  | N    | Bit interleaving                          | coordinates map to Morton order                  |
| HilbertCurve            | Spatial  | M    | Bit interleaving                          | coordinates map to Hilbert order                 |
| VoronoiDiagram          | Spatial  | M    | GraphAdjacencyStorage                     | cells partition nearest-site regions             |
| DelaunayTriangulation   | Spatial  | M    | GraphAdjacencyStorage                     | triangulation satisfies empty circumcircle model |

### 8.9 Persistent and functional structures

| Structure                | Location   | Mode | Storage                            | Invariant                                        |
|--------------------------|------------|------|------------------------------------|--------------------------------------------------|
| PersistentList           | Persistent | N    | LinkedNodeStorage                  | mutation returns new list and preserves old list |
| PersistentStack          | Persistent | N    | LinkedNodeStorage                  | push/pop return new stack versions               |
| PersistentQueue          | Persistent | M    | LinkedNodeStorage                  | queue versions remain valid                      |
| PersistentVector         | Persistent | M    | TreeNodeStorage                    | indexed updates share tree structure             |
| PersistentMap            | Persistent | M    | HashArrayMappedTrie                | map versions share trie nodes                    |
| PersistentSet            | Persistent | M    | HashArrayMappedTrie                | set versions share trie nodes                    |
| PersistentSegmentTree    | Persistent | M    | TreeNodeStorage                    | range updates preserve prior roots               |
| FingerTree               | Functional | M    | TreeNodeStorage                    | measured tree supports ends and splits           |
| Zipper                   | Functional | N    | path focus storage                 | focus and breadcrumbs reconstruct whole          |
| OkasakiQueue             | Functional | N    | LinkedNodeStorage                  | front and rear lists preserve amortized FIFO     |
| FunctionalRedBlackTree   | Functional | M    | BinaryNodeStorage                  | immutable red-black invariants                   |
| HashArrayMappedTrie      | Functional | M    | TreeNodeStorage                    | hash fragments index trie levels                 |
| RelaxedRadixBalancedTree | Functional | M    | TreeNodeStorage                    | relaxed branching supports concatenation         |
| ClojurePersistentVector  | Functional | M    | TreeNodeStorage                    | 32-way indexed persistent vector model           |
| ImmutableVector          | Functional | M    | PackedArrayStorage or tree storage | mutation returns new vector                      |

### 8.10 Probabilistic structures

| Structure           | Location      | Mode | Storage            | Invariant                                      |
|---------------------|---------------|------|--------------------|------------------------------------------------|
| BloomFilter         | Probabilistic | N    | BitStringStorage   | false negatives are forbidden                  |
| CountingBloomFilter | Probabilistic | N    | RegisterStorage    | counters track approximate membership removals |
| StableBloomFilter   | Probabilistic | M    | RegisterStorage    | decay policy bounds filter age                 |
| CuckooFilter        | Probabilistic | M    | ArrayStorage       | fingerprints occupy candidate buckets          |
| QuotientFilter      | Probabilistic | M    | BitStringStorage   | quotient and remainder encoding                |
| SkipList            | Probabilistic | N    | LinkedNodeStorage  | levels are probabilistically assigned          |
| CountMinSketch      | Probabilistic | N    | MatrixDenseStorage | estimates never undercount                     |
| HyperLogLog         | Probabilistic | N    | RegisterStorage    | registers track max leading-zero observations  |
| MinHash             | Probabilistic | N    | RegisterStorage    | signatures approximate Jaccard similarity      |
| SimHash             | Probabilistic | N    | BitStringStorage   | weighted features map to bit signature         |
| TDigest             | Probabilistic | M    | ArrayStorage       | centroids approximate quantiles                |
| KllSketch           | Probabilistic | M    | ArrayStorage       | compaction approximates quantiles              |
| ReservoirSampling   | Probabilistic | N    | ArrayStorage       | fixed sample represents stream                 |

### 8.11 Compressed and succinct structures

| Structure             | Location   | Mode | Storage          | Invariant                                       |
|-----------------------|------------|------|------------------|-------------------------------------------------|
| SuccinctTree          | Compressed | M    | BitStringStorage | tree encoded with rank/select support           |
| SuccinctGraph         | Compressed | M    | BitStringStorage | graph encoded compactly with navigation support |
| CompressedSuffixArray | Compressed | M    | BitStringStorage | suffix array compressed with lookup support     |
| FMIndex               | Compressed | M    | BitStringStorage | backward search over Burrows-Wheeler transform  |
| BitPackedArray        | Compressed | N    | BitStringStorage | fixed-width integers packed into bits           |
| RunLengthEncodedArray | Compressed | N    | ArrayStorage     | consecutive equal runs are grouped              |
| EliasFano             | Compressed | M    | BitStringStorage | monotone integer set compressed with select     |
| RoaringBitmap         | Compressed | M    | BitStringStorage | integer set split into compressed containers    |

RadixTree, PatriciaTrie, CompressedTrie, WaveletTree, WaveletMatrix, CompressedSparseRow, and CompressedSparseColumn
have
canonical homes above and may be indexed in compressed documentation without duplicating implementation.

### 8.12 Storage model structures

| Structure            | Location      | Mode | Storage               | Invariant                                |
|----------------------|---------------|------|-----------------------|------------------------------------------|
| LsmTree              | StorageModels | M    | FileBackedStorage     | writes move through levels by compaction |
| SStable              | StorageModels | M    | FileBackedStorage     | immutable sorted table                   |
| Memtable             | StorageModels | N    | SortedMap or SkipList | mutable ordered write buffer             |
| HashIndex            | StorageModels | N    | HashMap               | key maps to record pointer               |
| BitmapIndex          | StorageModels | N    | BitSet                | value maps to bitset of row ids          |
| InvertedIndex        | StorageModels | N    | Map plus Set          | term maps to document ids                |
| ClusteredIndex       | StorageModels | M    | BPlusTree             | table order follows primary key model    |
| NonClusteredIndex    | StorageModels | M    | BPlusTree             | index points to external row reference   |
| RTreeIndex           | StorageModels | M    | RTree                 | spatial index backed by RTree model      |
| FractalTreeIndex     | StorageModels | M    | TreeNodeStorage       | buffered tree index model                |
| GiST                 | StorageModels | M    | TreeNodeStorage       | generalized search tree model            |
| SpGiST               | StorageModels | M    | TreeNodeStorage       | space-partitioned search tree model      |
| BrinIndex            | StorageModels | M    | ArrayStorage          | block range summaries                    |
| ColumnStore          | StorageModels | M    | FileBackedStorage     | values are grouped by column             |
| RowStore             | StorageModels | M    | FileBackedStorage     | values are grouped by row                |
| LogStructuredStorage | StorageModels | M    | FileBackedStorage     | append-only writes plus compaction       |

BTree, BPlusTree, SkipList, and MerkleTree keep canonical homes above and may be reused here by composition.

### 8.13 Algorithmic structures

| Structure               | Location   | Mode | Storage           | Invariant                             |
|-------------------------|------------|------|-------------------|---------------------------------------|
| SparseTable             | Algorithms | N    | ArrayStorage      | idempotent range query table          |
| MonotonicQueue          | Algorithms | N    | Deque             | values preserve monotonic order       |
| MonotonicStack          | Algorithms | N    | Stack             | values preserve monotonic order       |
| CentroidDecomposition   | Algorithms | M    | TreeNodeStorage   | tree decomposed by centroids          |
| SquareRootDecomposition | Algorithms | N    | ArrayStorage      | blocks summarize ranges               |
| MosAlgorithm            | Algorithms | M    | ArrayStorage      | offline range query ordering          |
| DancingLinks            | Algorithms | N    | LinkedNodeStorage | four-way node links can unlink/relink |

UnionFind, SegmentTree, LazySegmentTree, FenwickTree, CartesianTree, HeavyLightDecomposition, Treap, LinkCutTree,
SuffixAutomaton, and AhoCorasickAutomaton keep canonical homes above and may be indexed here without duplicating
implementation.

### 8.14 Concurrent model structures

| Structure          | Location         | Mode | Storage                | Invariant                                       |
|--------------------|------------------|------|------------------------|-------------------------------------------------|
| ConcurrentQueue    | ConcurrentModels | R    | runtime backend        | FIFO within backend guarantees                  |
| ConcurrentStack    | ConcurrentModels | R    | runtime backend        | LIFO within backend guarantees                  |
| ConcurrentHashMap  | ConcurrentModels | R    | runtime backend        | concurrent key-value boundary                   |
| ConcurrentSkipList | ConcurrentModels | M    | LinkedNodeStorage      | ordered probabilistic list model                |
| BlockingQueue      | ConcurrentModels | R    | runtime backend        | dequeue waits by backend rules                  |
| WorkStealingDeque  | ConcurrentModels | M    | Deque                  | owner and thief ends are modeled                |
| CopyOnWriteArray   | ConcurrentModels | N    | PackedArrayStorage     | write returns copied storage                    |
| CopyOnWriteList    | ConcurrentModels | N    | PackedArrayStorage     | write returns copied list                       |
| ReadCopyUpdate     | ConcurrentModels | S    | ArrayStorage           | read/write epoch behavior is simulated          |
| LockFreeQueue      | ConcurrentModels | S    | SimulatedMemoryStorage | cannot claim CPU-level lock freedom in pure PHP |
| LockFreeStack      | ConcurrentModels | S    | SimulatedMemoryStorage | cannot claim CPU-level lock freedom in pure PHP |
| WaitFreeQueue      | ConcurrentModels | S    | SimulatedMemoryStorage | cannot claim CPU-level wait freedom in pure PHP |

Runtime-backed variants must name the backend directly, for example RedisBackedQueue, SwooleChannelQueue,
ParallelChannelQueue, or SharedMemoryQueue.

### 8.15 Simulated runtime and memory structures

| Structure                  | Location         | Mode | Storage                        | Invariant                                         |
|----------------------------|------------------|------|--------------------------------|---------------------------------------------------|
| StackFrame                 | SimulatedRuntime | S    | SimulatedMemoryStorage         | frame has locals, return address, and parent      |
| CallStack                  | SimulatedRuntime | S    | SimulatedMemoryStorage         | frames push and pop in call order                 |
| HeapMemory                 | SimulatedRuntime | S    | SimulatedMemoryStorage         | allocations are simulated blocks                  |
| FreeList                   | SimulatedRuntime | S    | SimulatedMemoryStorage         | free blocks are tracked                           |
| MemoryPool                 | SimulatedRuntime | M    | ArrayStorage                   | objects are checked out and returned              |
| SlabAllocator              | SimulatedRuntime | S    | SimulatedMemoryStorage         | fixed-size slots are modeled                      |
| BuddyAllocator             | SimulatedRuntime | S    | SimulatedMemoryStorage         | split/merge power-of-two blocks                   |
| PageTable                  | SimulatedRuntime | S    | SimulatedMemoryStorage         | virtual pages map to physical frames in model     |
| TranslationLookasideBuffer | SimulatedRuntime | S    | SimulatedMemoryStorage         | cache entries model page translations             |
| CacheLine                  | SimulatedRuntime | S    | SimulatedMemoryStorage         | fixed-size cache line model                       |
| Buffer                     | Linear           | N    | ArrayStorage or string storage | bounded byte sequence                             |
| DoubleBuffer               | Linear           | N    | ArrayStorage                   | front/back buffers swap atomically at model level |
| ObjectPool                 | SimulatedRuntime | M    | ArrayStorage                   | objects are reused with reset rules               |

### 8.16 Abstract data type promises

These are concepts, not implementation folders. They should usually be represented by family promises in
`Capabilities/Structures/Foundation` or by the concrete structure implementation.

Concepts:

```text
ListType
SetType
MapType
StackType
QueueType
DequeType
PriorityQueueType
GraphType
TreeType
BagType
MultiSetType
DictionaryType
SequenceType
TableType
MatrixType
StreamType
```

Rule:

Do not create a user-facing class just because an abstract data type exists in the atlas.

## 9. Per-Structure Definition of Done

Every implemented structure must include:

```text
1. production class
2. family promise only when it adds real pressure relief
3. explicit storage strategy
4. invariant documentation
5. constructor or factory
6. core operations
7. traversal model
8. stable toArray where meaningful
9. stable toJson where meaningful
10. failure behavior
11. edge-case tests
12. invariant tests
13. serialization tests
14. mutation or immutability tests
15. complexity note
16. docs page under docs/DataStack/Data
17. example usage through PublicSurface only when promoted
```

If any item is missing, the structure is partial.

## 10. Testing Plan

Test root:

```text
tests/Unit/Components/DataStack/Data/
```

Planned test groups:

```text
Structures/Linear
Structures/Maps
Structures/Sets
Structures/Priority
Structures/Trees
Structures/Graphs
Structures/Text
Structures/Numeric
Structures/Matrix
Structures/Spatial
Structures/Persistent
Structures/Functional
Structures/Probabilistic
Structures/Compressed
Structures/StorageModels
Structures/Algorithms
Structures/ConcurrentModels
Structures/SimulatedRuntime
```

Behavior proof examples:

```text
Set: adding same value twice does not increase count
Map: setting same key replaces value and does not duplicate key
BiMap: duplicate value is rejected or replaces by explicit policy
Stack: pop order is reverse push order
Queue: dequeue order equals enqueue order
Deque: both ends preserve configured order
Heap: repeated extract returns priority order
Tree: traversal respects structure ordering
Graph: removing a node removes incident edges
UnionFind: connected relation is transitive
BloomFilter: inserted values are never reported absent
Matrix: invalid dimensions fail explicitly
PersistentMap: old version remains valid after mutation
LockFreeQueue simulation: docs and type name do not claim real CPU lock freedom
```

## 11. Documentation Plan

Canonical long-form docs live in:

```text
docs/DataStack/Data/
```

Planned docs:

```text
how-this-works.md
STRUCTURE_ATLAS.md
STRUCTURE_IMPLEMENTATION_MATRIX.md
STRUCTURE_COMPLEXITY_TABLE.md
STRUCTURE_STORAGE_STRATEGIES.md
STRUCTURE_PUBLIC_SURFACE.md
STRUCTURE_SIMULATION_BOUNDARIES.md
```

Required simulation boundary statement:

```text
Pure PHP does not own Zend Engine memory layout, CPU cache lines, real lock-free progress guarantees, or wait-free
progress guarantees. These structures are simulations or runtime-specific bridges unless backed by an explicit runtime
dependency and tests.
```

## 12. Complexity Evidence

Each structure must have one of:

```text
complexity proof
benchmark evidence
explicit "not claimed" note
```

Examples:

| Structure        | insert       | delete       | lookup       | traversal | memory |
|------------------|--------------|--------------|--------------|-----------|--------|
| Stack            | O(1)         | O(1)         | O(1) peek    | O(n)      | O(n)   |
| Queue            | O(1)         | O(1)         | O(1) front   | O(n)      | O(n)   |
| HashMap          | O(1) average | O(1) average | O(1) average | O(n)      | O(n)   |
| BinarySearchTree | O(h)         | O(h)         | O(h)         | O(n)      | O(n)   |
| AVLTree          | O(log n)     | O(log n)     | O(log n)     | O(n)      | O(n)   |
| BloomFilter      | O(k)         | n/a          | O(k)         | n/a       | O(m)   |

No implementation may be called fast, optimized, scalable, high-performance, or production-ready without evidence.

## 13. Promotion Gates

### Internal capability gate

Required:

```text
class exists
storage strategy exists
invariant tests pass
edge-case tests pass
failure behavior documented
```

### PublicSurface gate

Required:

```text
internal capability gate is green
public API shape is stable
public API tests pass
serialization behavior is stable
docs include example usage
public surface checker passes
```

### Advanced structure gate

Required:

```text
basic family structures are green
complexity proof exists
algorithm-specific invariant tests exist
docs say when not to use it
```

### Simulation gate

Required:

```text
name does not imply real runtime guarantee
docs explicitly state simulated boundary
tests prove model behavior only
no production readiness claim
```

## 14. Validation Commands

Focused validation for documentation-only plan updates:

```bash
test -s EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md
git diff --check -- EVIDENCE/.PLANS/datastack-data-structure-universe-master-plan.md
php tooling/refactor/check-advanced-pattern-folder-violations.php
```

Focused validation for the first code wave:

```bash
composer dump-autoload -o
vendor/bin/phpunit tests/Unit/Components/DataStack/Data --no-coverage
vendor/bin/phpstan analyse components/DataStack/Data tests/Unit/Components/DataStack/Data --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/refactor/check-public-surface.php
```

Full validation after any public surface, component shape, namespace, runtime behavior, security-sensitive behavior, or
performance-sensitive behavior change:

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
php tooling/audit_broken_refs.php
php avax runtime:doctor
php tooling/governance/check-governance-index-current.php
php tooling/governance/check-stage-lock.php
php tooling/refactor/check-component-canonical-shape.php
php tooling/refactor/check-advanced-pattern-folder-violations.php
php tooling/security/check-security-governance.php
php tooling/performance/check-performance-governance.php
```

If any command does not exist, report it as:

```text
PLANNED / NOT IMPLEMENTED
```

## 15. Red Flags

Do not:

```text
make every structure inherit from Collection
wrap PHP arrays and call that sufficient
hide invariants
hide storage strategy
add untested public API
promote simulations as native runtime behavior
duplicate implementation across canonical families
create public facades for every exotic structure
implement research structures before the kernel is green
claim performance without evidence
claim production readiness without full validation
```

Must:

```text
document invariant
document storage
document mutation policy
document failure model
test behavior
test edge cases
test serialization where meaningful
document complexity
record evidence
keep PublicSurface thin
```

## 16. Next Allowed Action

Next action:

```text
Create the Wave 0 Structure Atlas docs under docs/DataStack/Data and convert this registry into a checked implementation
matrix.
```

The first code action after Wave 0:

```text
Implement Structure Kernel only: family promises, failures, comparison, hashing, storage primitives, and tests.
```

No broad structure implementation should start before the Structure Kernel has proof.
