# HOW_THIS_WORKS — DataStack/Data

## What This Component Does

Data is AvaX's general-purpose data manipulation toolkit. It provides three major capability families:

1. **Data Pipeline DSL** — fluent array/object transformation via `Arrhae`, `Collection`, and `Json` with map/filter/reduce/sort/group/partition operations
2. **Data Structures** — canonical implementations of lists, maps, sets, queues, stacks, heaps, trees, graphs, matrices, and probabilistic structures
3. **Value Objects** — typed domain values like `Money`, `Uuid`, `Currency`, `Percentage`, `Moment`

It also provides pipeline/batch orchestration flows and a dependency registration unit for assembling the `Data` service.

## What This Component Does NOT Do

- Does NOT handle database queries or ORM behavior
- Does NOT perform HTTP request/response handling
- Does NOT manage application-level validation beyond basic `Validator` stub
- Does NOT replace a full collection library like Laravel Collections — it is AvaX's own implementation
- Does NOT serialize/deserialize DataObjects (that's DataTransfer's job)

## Public API

### Data Service (DI-assembled entry point)

Assembled via `RegisterDataDependencies::build()`:

```php
use Avax\Components\DataStack\Data\System\Configuration\RegisterDataDependencies;

$data = (new RegisterDataDependencies())->build();

// Nested dot-path read/write
$value = $data->get($array, 'user.profile.name', $default = null);
$data->set($array, 'user.profile.name', 'New Name');

// Aggregation
$total = $data->sum($items, 'price');
$avg   = $data->avg($items, 'price');

// Collection creation
$collection = $data->collect([1, 2, 3]);

// Array reader/writer
$reader = $data->array();   // ArrayReader
$writer = $data->write();   // ArrayWriter
```

### Collection (fluent item pipeline)

```php
use Avax\Components\DataStack\Data\System\PublicSurface\Collection;

$collection = Collection::make([
    ['name' => 'John', 'age' => 30],
    ['name' => 'Jane', 'age' => 25],
]);

// Pipeline operations (all immutable — return new Collection)
$names  = $collection->pluck('name');              // ['John', 'Jane']
$adults = $collection->where('age', 30);           // filtered
$sorted = $collection->sortBy('age');              // ascending by age
$grouped = $collection->groupBy('age');            // array keyed by age

// Aggregate
$total = $collection->sum('age');                   // 55
$avg   = $collection->average('age');               // 27.5

// Conversion
$array = $collection->toArray();
$json  = $collection->toJson();
$xml   = $collection->toXml();
```

### Arrhae (array-native pipeline engine)

The core array-backed DSL. `Collection` composes `Arrhae` internally:

```php
use Avax\Components\DataStack\Data\System\Capabilities\Forms\ArrayForm\Arrhae;

$arr = Arrhae::make(['a' => 1, 'b' => 2, 'c' => 3]);

$arr->map(fn($v) => $v * 2)
    ->filter(fn($v) => $v > 3)
    ->sort()
    ->values()
    ->toArray();  // [4, 6]

// Dot-path access
$arr->get('nested.key.path', $default);
$arr->set('nested.key.path', $value);   // returns new Arrhae
$arr->forget('nested.key.path');         // returns new Arrhae

// Set algebra
$arr->merge([...]);
$arr->union([...]);
$arr->diff([...]);
$arr->intersect([...]);

// Query filtering
$arr->where('status', 'active');
$arr->whereIn('id', [1, 2, 3]);
$arr->whereBetween('age', [18, 65]);
$arr->whereNull('deleted_at');
$arr->whereNotNull('email');
```

### Json (JSON document DSL)

```php
use Avax\Components\DataStack\Data\System\PublicSurface\Json;

$json = Json::decode('{"name": "John"}');
$json = Json::from(['name' => 'John']);

$json->get('name');           // 'John'
$json->set('age', 30);        // new JsonForm
$json->toArray();             // ['name' => 'John', 'age' => 30]
$json->toJson();              // '{"name":"John","age":30}'
```

### Data Structures

```php
use Avax\Components\DataStack\Data\System\PublicSurface\{Map, Set, Sequence, Queue, Stack, Deque, PriorityQueue, Heap, Graph, OrderedMap, OrderedSet, Matrix, BloomFilter};

// Linear structures
$seq = Sequence::make([1, 2, 3]);
$queue = Queue::make([1, 2, 3]);
$stack = Stack::make([1, 2, 3]);
$deque = Deque::make([1, 2, 3]);

// Maps
$map = Map::make(['a' => 1, 'b' => 2]);
$orderedMap = OrderedMap::make(['a' => 1, 'b' => 2]);
$multiMap = MultiMap::make(['tags' => ['php', 'ava'], 'tags' => ['framework']]);

// Sets
$set = Set::make([1, 2, 3]);
$orderedSet = OrderedSet::make([1, 2, 3]);
$bag = Bag::make([1, 1, 2, 3]);  // allows duplicates

// Priority structures
$heap = Heap::make([3, 1, 2]);
$minHeap = MinHeap::make([3, 1, 2]);
$maxHeap = MaxHeap::make([3, 1, 2]);
$pq = PriorityQueue::make();

// Graphs
$graph = Graph::make();
$weightedGraph = WeightedGraph::make();

// Matrices
$denseMatrix = DenseMatrix::make(rows: 3, cols: 3);
$sparseMatrix = SparseMatrix::make(rows: 100, cols: 100);

// Trees
$bst = BinarySearchTree::make();
$trie = Trie::make();
$fenwick = FenwickTree::make(size: 100);
$segment = SegmentTree::make(size: 100);

// Probabilistic
$bloom = BloomFilter::make(expectedItems: 1000);
$hll = HyperLogLog::make();
$cms = CountMinSketch::make();
```

### Pipeline & Batch Flows

```php
use Avax\Components\DataStack\Data\System\Flows\Pipeline\Pipeline;

$result = (new Pipeline())
    ->pipe(fn($v) => $v * 2, name: 'double')
    ->pipe(fn($v) => $v + 1, name: 'increment')
    ->process(5);  // 11

use Avax\Components\DataStack\Data\System\Flows\Batch\Batch;

foreach (Batch::from(range(1, 10), size: 3) as $batch) {
    // [1,2,3], [4,5,6], [7,8,9], [10]
}
```

### Value Objects

```php
use Avax\Components\DataStack\Data\System\Capabilities\Values\{Uuid, Money, Currency, Percentage};
use Avax\Components\DataStack\Data\System\Capabilities\Values\Temporal\Moment;

$uuid = Uuid::generate();
$uuid = new Uuid('550e8400-e29b-41d4-a716-446655440000');

$money = new Money(1000, Currency::USD);  // $10.00 (cents)
$money->add(new Money(500, Currency::USD)); // $15.00
$money->toFloat(); // 10.0

$percentage = new Percentage(75.5);

$moment = Moment::now();
```

### Functional Types

```php
use Avax\Components\DataStack\Data\System\Capabilities\Structures\Functional\{Option, Result, Pair, Record, Tuple2, Tuple3, Tuple4, Threshold};

// Option (Some/None)
$some = Some::of(42);
$none = None::of();

// Result (Success/Failure)
$success = Success::of('value');
$failure = Failure::of(new RuntimeException('error'));

// Pair
$pair = new Pair(first: 'key', second: 'value');
```

## Internal Flow

### Pipeline Composition

```
Collection → Arrhae → Individual Operator Classes
```

- `Collection` delegates all pipeline operations to its internal `Arrhae` instance
- `Arrhae` delegates each operation to a dedicated operator class in `Capabilities/Operators/`
- Each operator is `readonly` and takes `$items` as constructor argument
- Every mutating operation returns a **new** instance (immutability via `MutationGuard`)

### Immutability Model

All pipeline operations use `MutationGuard`:
- `set()`, `forget()`, `add()`, `map()`, etc. all assert mutable, then return a **new** instance
- `toImmutable()` / `lock()` freezes the guard — subsequent mutations throw `MutationException`
- ArrayAccess is read-only — `offsetSet` and `offsetUnset` throw `MutationException` with hints to use proper methods

### Data Structures

Each structure follows a consistent pattern:
- **Structure class** — public API with type-safe operations
- **Storage class** — internal data representation (e.g., `ArrayStorage`, `LinkedNodeStorage`, `GraphAdjacencyStorage`)
- **Foundation values** — `Entry`, `Pair`, `Edge`, `WeightedEdge`, `Point`, `Range`, etc.

### Failure Model

Exceptions in `Foundation/Exceptions/`:
- `DataException` — base exception
- `InvalidFlowException` — flow configured incorrectly (bad batch size, empty pipeline, bad window size)
- `InvalidValueException` — value out of range or invalid
- `MutationException` — attempted mutation on locked structure

Failure types in `Foundation/Failure/`:
- `DataFailure` — generic failure wrapper
- `DuplicateKey`, `MissingKey`, `IndexOutOfBounds` — structure access errors
- `EmptyStructure` — operation on empty collection/queue/stack
- `InvalidCapacity`, `InvalidJson`, `InvalidStructureOperation` — configuration/state errors
- `StructureInvariantBroken` — internal consistency violation

## Dependencies

- **PHP 8+** — relies on typed properties, readonly classes, match expressions, named arguments
- **No external packages** — pure PHP
- **Internal dependencies:**
  - `MutationGuard` — immutability enforcement
  - `MakeCollection` / `WrapValue` — iterable normalization
  - `NormalizedIterable` — converts iterables to arrays consistently
  - `DotPath` — nested array path resolution
  - Individual operator classes (MapValues, FilterValues, SortValues, etc.)
  - Codec classes (EncodeJson, EncodeXml, EncodeArray)

## Failure Behavior

| Scenario | Behavior |
|----------|----------|
| Pipeline with no stages | `InvalidFlowException::pipelineHasNoStages()` |
| Batch size <= 0 | `InvalidFlowException::invalidBatchSize($size)` |
| Window size <= 0 | `InvalidFlowException::invalidWindowSize($size)` |
| Mutation on locked structure | `MutationException` with hint message |
| Offset set/unset on Collection | `MutationException::arrayStyleMutationNotSupported()` |
| Empty structure operation (pop, peek, etc.) | `EmptyStructure` failure or specific exception |
| Index out of bounds | `IndexOutOfBounds` failure |
| Missing key in strict mode | `MissingKey` failure |
| Duplicate key in unique structure | `DuplicateKey` failure |
| Invalid UUID format | `InvalidArgumentException` |
| Currency mismatch in Money operations | `InvalidArgumentException` |
| Invalid JSON during decode | `JsonException` (thrown, not caught) |

## Runtime Safety

- **Immutability by default** — all pipeline operations return new instances; original data is never mutated
- **MutationGuard** — prevents accidental mutation after `lock()` / `toImmutable()`
- **Depth safety** — data structure operations use bounded iteration (no unbounded recursion in standard structures)
- **No hidden I/O** — no database, filesystem, or network calls
- **No container dependency** — `RegisterDataDependencies` assembles services without a DI container
- **Read-only ArrayAccess** — prevents `$collection['key'] = $value` from silently mutating; throws descriptive exception

## Examples

### Fluent Collection Pipeline

```php
$orders = Collection::make($orderData);

$report = $orders
    ->whereNotNull('shipped_at')
    ->where('status', 'completed')
    ->sortBy('shipped_at')
    ->groupBy(fn($order) => $order['region'])
    ;
```

### Immutable Data Manipulation

```php
$arr = Arrhae::make(['name' => 'John']);

$modified = $arr
    ->set('age', 30)
    ->set('address.city', 'NYC')
    ->forget('name');

// $arr is unchanged, $modified is new instance
```

### Pipeline with Named Stages

```php
$pipeline = (new Pipeline())
    ->pipe(fn($data) => $data->validate(), 'validate')
    ->pipe(fn($data) => $data->sanitize(), 'sanitize')
    ->pipe(fn($data) => $data->persist(), 'persist');

$result = $pipeline->process($input);
$pipeline->stageNames(); // ['validate', 'sanitize', 'persist']
```

### Processing in Batches

```php
$users = getUserIds(); // large iterable

foreach (Batch::from($users, size: 100) as $batch) {
    processBatch($batch); // 100 users at a time
}
```

### Data Structure Usage

```php
// Queue for job processing
$queue = Queue::make($jobs);
while (! $queue->isEmpty()) {
    $job = $queue->dequeue();
    process($job);
}

// Set for deduplication
$unique = Set::make($allTags);
$common = $unique->intersect($requiredTags);

// Map for typed key-value storage
$config = Map::make(['timeout' => 30, 'retries' => 3]);
$config->get('timeout');       // 30
$config->has('retries');       // true
$updated  = $config->put('timeout', 60); // new Map
```

## Known Limits

1. **Validator is minimal** — `Validator::validate()` only checks if required fields exist. It does NOT implement the full rule syntax implied by string rules like `'required|string|min:3'`. It's a stub migrated from legacy DataFoundation.
2. **Validator::fails() is just negation** — `fails()` simply returns `! validate()`, no detailed error collection.
3. **ValueCasterInterface mismatch (DataTransfer side)** — The Data component does not implement the DataTransfer caster interface; they are separate systems.
4. **Probabilistic structures are present but may not be fully tested** — BloomFilter, HyperLogLog, and CountMinSketch exist as capabilities but their correctness properties need evidence.
5. **Graph algorithms are structural** — `Graph` and `WeightedGraph` provide adjacency storage and UnionFind, but higher-level algorithms (Dijkstra, BFS, DFS, topological sort) may be in capabilities, not necessarily exposed as public flows.
6. **Money only supports add() and toFloat()** — No subtract, multiply, divide, compare, or format methods. Currency comparison is strict equality.
7. **Uuid::generate() uses mt_rand()** — Not cryptographically secure. Uses `mt_rand()` which is not suitable for security-sensitive UUID generation.
8. **Collection ArrayAccess is read-only** — `$collection['key'] = $value` throws an exception. This is intentional (immutability) but may surprise users coming from Laravel Collections.
9. **Some structures lack full CRUD** — e.g., `Map` has `get/has/put/remove` but no bulk operations. `Set` has `add/remove/union/intersect/diff/contains` but no map/filter.
10. **shortcuts.php is empty** — The file exists as an autoload target but defines no helper functions.

## Current Status

- **Arrhae (array pipeline):** Fully implemented — map, filter, reduce, sort, group, partition, search, set algebra, dot-path access, immutability
- **Collection (item pipeline):** Fully implemented — wraps Arrhae with IteratorAggregate, Countable, read-only ArrayAccess
- **Json (JSON DSL):** Implemented — decode/from + delegates to Arrhae for operations
- **Data service:** Implemented — DI-assembled entry point with get/set/sum/avg/collect/array/write
- **Pipeline/Batch flows:** Implemented — immutable pipeline with named stages, fixed-size batch iteration
- **Linear structures:** Implemented — DataList, Sequence, Queue, Stack, Deque, DoublyLinkedList, LinkedList, RingBuffer, DynamicArray, SparseArray
- **Map structures:** Implemented — Map, OrderedMap, MultiMap, MapEntry
- **Set structures:** Implemented — Set, OrderedSet, Bag
- **Priority structures:** Implemented — BinaryHeap, MinHeap, MaxHeap, PriorityQueue
- **Tree structures:** Implemented — BinaryTree, BinarySearchTree, FenwickTree, SegmentTree, Trie
- **Graph structures:** Implemented — Graph, WeightedGraph, UnionFind
- **Matrix structures:** Implemented — DenseMatrix, SparseMatrix
- **Probabilistic structures:** Implemented — BloomFilter, HyperLogLog, CountMinSketch
- **Functional types:** Implemented — Option (Some/None), Result (Success/Failure), Pair, Record, Tuple2-4, Threshold, OperationResult
- **Value objects:** Partially implemented — Uuid (generate + validate), Money (add + toFloat only), Currency, Percentage, Moment
- **Validation:** Stub — basic required-field check only, no full rule engine
- **Tests:** Exist in central test tree — verify through `vendor/bin/phpunit`
