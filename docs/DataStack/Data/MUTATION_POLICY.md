# DataStack/Data Mutation Policy

## Status: canonical governance document for DataStack/Data mutation behavior

## 1. Default Mutability Rule

All data structures in `DataStack/Data` are **immutable by default**.

Every mutation operation returns a **new instance** with the updated state.
The original instance remains unchanged and valid.

## 2. Immutable Structures

The following structures are immutable and return new instances on every mutation:

```text
Stack → push(), pop() return new Stack
Queue → enqueue(), dequeue() return new Queue
Deque → pushFront(), pushBack(), popFront(), popBack() return new Deque
Map → put(), remove() return new Map
Set → add(), remove() return new Set
OrderedMap → put(), remove() return new OrderedMap
OrderedSet → add(), remove() return new OrderedSet
BinaryHeap → insert(), extract() return new BinaryHeap
PriorityQueue → enqueue(), dequeue() return new PriorityQueue
Graph → addNode(), removeNode(), addEdge() return new Graph
Matrix → set() returns new Matrix
```

## 3. Storage Immutability

All storage primitives are immutable:

```text
ArrayStorage → append(), replace() return new ArrayStorage
AssociativeArrayStorage → write(), remove() return new AssociativeArrayStorage
RingBufferStorage → enqueue(), dequeue() return new RingBufferStorage
BitStringStorage → set(), clear() return new BitStringStorage
LinkedNodeStorage → prepend(), append() return new LinkedNodeStorage
BinaryNodeStorage → withLeft(), withRight(), withValue() return new BinaryNodeStorage
TreeNodeStorage → withChildren(), addChild() return new TreeNodeStorage
GraphAdjacencyStorage → addNode(), addEdge(), removeNode() return new GraphAdjacencyStorage
MatrixDenseStorage → set() returns new MatrixDenseStorage
MatrixSparseStorage → set() returns new MatrixSparseStorage
```

## 4. Value Object Immutability

All value objects in `Foundation/Values/` are readonly and immutable:

```text
Pair, Tuple, Entry, Range, Interval, Coordinate, Point, Edge, WeightedEdge, Priority
```

They provide `withX()` methods that return new instances rather than modifying state.

## 5. Mutation Exception

When a mutation operation cannot be completed, the structure throws:

```text
EmptyStructure — when popping/dequeuing from an empty structure
IndexOutOfBounds — when accessing an index outside valid range
InvalidCapacity — when exceeding storage capacity limits
InvalidStructureOperation — when the operation violates structure invariants
StructureInvariantBroken — when a mutation would break a required invariant
DuplicateKey — when inserting a key that must be unique
MissingKey — when a required key does not exist
```

## 6. Mutation Guard

`Foundation/Mutability/MutationGuard` provides opt-in mutation protection for
structures that need runtime mutation controls. It is not used by default in
immutable structures.

## 7. Why Immutable

```text
Predictable: The same input always produces the same output.
Testable: No hidden state changes between assertions.
Composable: Operations can be chained safely.
Thread-safe: No shared mutable state in worker runtimes.
Reversible: Old instances remain valid for rollback or undo.
```

## 8. Performance Note

Immutability in PHP creates new objects on each mutation. This has allocation cost.
For performance-critical paths, structures may provide batch operations that
minimize intermediate allocations. This is an optimization, not a mutability change.

The structure remains logically immutable — the caller sees the same API and
the same guarantee that the original instance is not modified.
