# Structure Public Surface

PublicSurface/ must stay thin. It receives and delegates. It does not contain behavior.

> **Rule: Do not expose all 236 atlas structures.**
> Only stable, useful, tested structures get public facades.
> The atlas is a knowledge map, not a public API catalog.

## Existing Public Facades

| Facade | Delegates To | Classification | Status |
|---|---|---|---|
| Data | ArrayReader, ArrayWriter, ReadNestedValue, WriteNestedValue, SumValues, AverageValues | CORE_PUBLIC | Stable |
| Sequence | Linear\Sequence | CORE_PUBLIC | Stable |
| Stack | Linear\Stack | CORE_PUBLIC | Stable |
| Queue | Linear\Queue | CORE_PUBLIC | Stable |
| Deque | Linear\Deque | CORE_PUBLIC | Stable |
| Map | Maps\Map | CORE_PUBLIC | Stable |
| Set | Sets\Set | CORE_PUBLIC | Stable |
| OrderedMap | Maps\OrderedMap | CORE_PUBLIC | Stable |
| OrderedSet | Sets\OrderedSet | CORE_PUBLIC | Stable |
| MultiMap | Maps\MultiMap | CORE_PUBLIC | Stable |
| Heap | Priority\BinaryHeap | CORE_PUBLIC | Stable |
| PriorityQueue | Priority\PriorityQueue | CORE_PUBLIC | Stable |
| Graph | Graphs\Graph | CORE_PUBLIC | Stable |
| Matrix | Matrix\DenseMatrix + SparseMatrix | CORE_PUBLIC | Stable |
| BloomFilter | Probabilistic\BloomFilter | LABS_ONLY | Stable facade, LABS classification |
| Json | JsonForm\Json | CORE_INTERNAL | Stable |
| Collection | CollectionForm\Collection | CORE_INTERNAL | Stable |
| Arrhae | ArrayForm\Arrhae | CORE_INTERNAL | Stable |

## Facades Intentionally Not Added

These structures exist but deliberately have no public facade:

| Structure | Reason |
|---|---|
| RingBuffer | Specialist — use through Sequence/Deque when needed |
| SparseArray | Specialist — internal use |
| DataList | Legacy vocabulary — use Sequence |
| BinaryHeap | Exposed via Heap facade |
| MinHeap / MaxHeap | Advanced — use via Heap facade or directly if needed |
| BinaryTree / BinarySearchTree | Advanced — specialist tree operations |
| Trie | Advanced — specialist text operations |
| FenwickTree / SegmentTree | Advanced — specialist range query operations |
| UnionFind | Advanced — specialist graph operations |
| WeightedGraph | Incomplete tests — RED status |
| Bag | Advanced — multiset with duplicate counts |
| LinkedList / DoublyLinkedList | Advanced — specialist linear structures |
| DynamicArray | Advanced — internal capacity growth behavior |
| CountMinSketch | LABS — probabilistic frequency estimation |
| HyperLogLog | LABS — probabilistic cardinality estimation |
| All functional types (Option, Result, Tuple, etc.) | Used internally, not framework public API |
| All foundation values | Internal kernel — used by structures |
| All storage primitives | Internal kernel — used by structures |
| All failure types | Internal kernel — thrown by structures |

## Public Surface Rules

1. One facade per stable structure family.
2. Facade is a factory only — static methods returning capability instances.
3. No public facade before invariant tests are green.
4. No public facade before failure tests are green.
5. No public facade before serialization tests are green.
6. No public facade before documentation exists.
7. No facade that exposes internal storage details.
8. No facade per exotic structure — group by family where appropriate.
9. **Probabilistic structures require error rate documentation, merge support, and benchmark evidence before CORE promotion.** BloomFilter has a facade for historical reasons but is classified LABS_ONLY until these are met.
10. **Advanced structures require "when not to use" documentation before public facade.**
11. **No new probabilistic facades will be added until the above gate is satisfied.**

## Decision: 236 Structures Are Not a Public API Goal

The atlas lists ~236 conceptual structures. The public surface will never expose all of them.

The public surface is intentionally small:

- **15 stable facades** for core framework use
- **3 internal facades** for framework-internal use
- **0 probabilistic facades** until error rates and benchmarks are documented
- **0 advanced facades** until "when not to use" documentation exists

This is by design. AvaX is not a data structure library. DataStack/Data is a data morphology engine for the AvaX framework. It exposes only what the framework and its applications need.
