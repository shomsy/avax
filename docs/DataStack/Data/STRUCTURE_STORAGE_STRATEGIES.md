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