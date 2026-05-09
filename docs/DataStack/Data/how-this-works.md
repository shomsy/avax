# DataStack Data — How This Works

DataStack/Data is the AvaX data morphology engine.

A structure is: **data + invariant + behavior**.

If the invariant is missing, it is not a data structure.
If storage is implicit, the design is incomplete.
If PHP cannot truthfully provide the runtime property, the implementation is a model, simulation, or runtime bridge with explicit boundaries.

## What It Owns

```text
Data structures with explicit invariants.
Storage strategies that are visible and documented.
Failure behavior that is testable and named.
Complexity profiles that are evidence-based.
```

## What It Does Not Own

```text
Database engine behavior (that is DataStack/Database).
Runtime-level concurrency guarantees (that is Operations/Concurrency).
CPU cache line or memory pool ownership (those are simulations).
```

## Quick Start

### Through PublicSurface (recommended)

```php
use Avax\Components\DataStack\Data\System\PublicSurface\Stack;
use Avax\Components\DataStack\Data\System\PublicSurface\Queue;
use Avax\Components\DataStack\Data\System\PublicSurface\Map;
use Avax\Components\DataStack\Data\System\PublicSurface\Set;
use Avax\Components\DataStack\Data\System\PublicSurface\BloomFilter;

$stack = Stack::make(values: ['A', 'B']);
$queue = Queue::make(values: ['A', 'B']);
$filter = BloomFilter::empty(bits: 256, hashCount: 5)->add(value: 'user:1');
```

### Direct capability access

```php
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Trees\FenwickTree;
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Matrix\DenseMatrix;

$tree = FenwickTree::withSize(size: 100)->add(index: 0, delta: 5);
$matrix = DenseMatrix::filled(rows: 3, columns: 3, value: 0);
```

## Structure Categories

| Category | Purpose | Example Structures |
|---|---|---|
| Linear | Ordered sequences | Stack, Queue, Deque, RingBuffer |
| Maps | Key-value associations | Map, OrderedMap, MultiMap |
| Sets | Unique value membership | Set, OrderedSet |
| Priority | Priority-ordered extraction | BinaryHeap, PriorityQueue |
| Trees | Hierarchical data | BinaryTree, BinarySearchTree, Trie, FenwickTree, SegmentTree |
| Graphs | Node-edge relationships | Graph, UnionFind, WeightedGraph |
| Matrix | Two-dimensional numeric data | DenseMatrix, SparseMatrix |
| Probabilistic | Approximate membership | BloomFilter |
| Text | String and pattern structures | Trie, SuffixArray |
| Spatial | Geometric and spatial data | Point, KDTree, QuadTree |

## Implementation Modes

| Mode | Meaning |
|---|---|
| **Native (N)** | PHP implements truthfully |
| **Model (M)** | Correct algorithm, no low-level claim |
| **Runtime Bridge (R)** | Backend-backed boundary |
| **Simulation (S)** | PHP cannot own the guarantee |

See `STRUCTURE_ATLAS.md` for the complete registry.
See `STRUCTURE_SIMULATION_BOUNDARIES.md` for what PHP cannot own.

## Governance

- Folder = flow or capability
- File = responsibility
- Function = exact action
- No production behavior without tests
- No performance claim without evidence
- PublicSurface stays thin — factory only

See `STRUCTURE_PUBLIC_SURFACE.md` for facade rules.
See `STRUCTURE_IMPLEMENTATION_MATRIX.md` for done status.