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