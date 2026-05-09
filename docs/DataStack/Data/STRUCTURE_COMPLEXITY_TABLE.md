# DataStack/Data Structure Complexity Table

Status: Wave 0 canonical design document
Owner: DataStack/Data
Last reviewed: 2026-05-09

## Purpose

This table records intended complexity profiles. It is not benchmark evidence. A structure may use this document as a
complexity proof only when the implementation matches the stated storage and algorithm.

No structure may be called fast, optimized, scalable, high-performance, or production-ready from this table alone.

## Core structures

| Structure     | Insert              | Delete              | Lookup                       | Traversal                  | Memory      |
|---------------|---------------------|---------------------|------------------------------|----------------------------|-------------|
| Sequence      | O(1) append average | O(n) by value       | O(1) by index                | O(n)                       | O(n)        |
| DynamicArray  | O(1) append average | O(n) by index shift | O(1) by index                | O(n)                       | O(n)        |
| LinkedList    | O(1) at known node  | O(1) at known node  | O(n)                         | O(n)                       | O(n)        |
| Stack         | O(1) push           | O(1) pop            | O(1) peek                    | O(n)                       | O(n)        |
| Queue         | O(1) enqueue        | O(1) dequeue        | O(1) front                   | O(n)                       | O(n)        |
| Deque         | O(1) ends           | O(1) ends           | O(1) ends                    | O(n)                       | O(n)        |
| RingBuffer    | O(1)                | O(1)                | O(1) by relative index       | O(n)                       | O(capacity) |
| SparseArray   | O(1) average        | O(1) average        | O(1) average                 | O(k)                       | O(k)        |
| Map           | O(1) average        | O(1) average        | O(1) average                 | O(n)                       | O(n)        |
| OrderedMap    | O(1) average        | O(1) average        | O(1) average                 | O(n)                       | O(n)        |
| SortedMap     | O(log n) target     | O(log n) target     | O(log n) target              | O(n)                       | O(n)        |
| MultiMap      | O(1) average        | O(k) per key        | O(1) average by key          | O(n)                       | O(n)        |
| BiMap         | O(1) average        | O(1) average        | O(1) average both directions | O(n)                       | O(n)        |
| Set           | O(1) average        | O(1) average        | O(1) average                 | O(n)                       | O(n)        |
| MultiSet      | O(1) average        | O(1) average        | O(1) average                 | O(n)                       | O(n)        |
| BinaryHeap    | O(log n)            | O(log n) extract    | O(1) peek                    | O(n)                       | O(n)        |
| PriorityQueue | O(log n)            | O(log n) dequeue    | O(1) peek                    | O(n) destructive or copied | O(n)        |

## Trees

| Structure        | Insert                  | Delete                | Lookup                   | Traversal      | Memory         |
|------------------|-------------------------|-----------------------|--------------------------|----------------|----------------|
| BinarySearchTree | O(h)                    | O(h)                  | O(h)                     | O(n)           | O(n)           |
| AVLTree          | O(log n)                | O(log n)              | O(log n)                 | O(n)           | O(n)           |
| RedBlackTree     | O(log n)                | O(log n)              | O(log n)                 | O(n)           | O(n)           |
| SplayTree        | O(log n) amortized      | O(log n) amortized    | O(log n) amortized       | O(n)           | O(n)           |
| Treap            | O(log n) expected       | O(log n) expected     | O(log n) expected        | O(n)           | O(n)           |
| BTree            | O(log n) model          | O(log n) model        | O(log n) model           | O(n)           | O(n)           |
| SegmentTree      | O(log n) update         | O(log n) update       | O(log n) range query     | O(n)           | O(n)           |
| LazySegmentTree  | O(log n) range update   | O(log n) range update | O(log n) range query     | O(n)           | O(n)           |
| FenwickTree      | O(log n) update         | O(log n) update       | O(log n) prefix query    | O(n)           | O(n)           |
| Trie             | O(m) word length        | O(m) word length      | O(m) word length         | O(total chars) | O(total chars) |
| SuffixArray      | O(n log n) build target | n/a                   | O(m log n) search target | O(n)           | O(n)           |

## Graphs and matrices

| Structure             | Insert                  | Delete                  | Lookup                 | Traversal         | Memory            |
|-----------------------|-------------------------|-------------------------|------------------------|-------------------|-------------------|
| AdjacencyList Graph   | O(1) edge average       | O(degree)               | O(degree) edge         | O(V + E)          | O(V + E)          |
| AdjacencyMatrix Graph | O(1) edge               | O(1) edge               | O(1) edge              | O(V^2)            | O(V^2)            |
| EdgeList Graph        | O(1) edge               | O(E) edge               | O(E) edge              | O(V + E)          | O(E)              |
| UnionFind             | O(alpha n)              | n/a                     | O(alpha n) connected   | O(n)              | O(n)              |
| DenseMatrix           | O(1) cell write         | O(1) clear cell         | O(1) cell read         | O(rows * columns) | O(rows * columns) |
| SparseMatrix          | O(1) average cell write | O(1) average cell clear | O(1) average cell read | O(k)              | O(k)              |
| CsrMatrix             | O(nnz) build            | expensive mutation      | O(row nnz)             | O(nnz)            | O(nnz + rows)     |
| CscMatrix             | O(nnz) build            | expensive mutation      | O(column nnz)          | O(nnz)            | O(nnz + columns)  |

## Probabilistic and compressed

| Structure             | Insert        | Delete        | Lookup                       | Traversal | Memory         |
|-----------------------|---------------|---------------|------------------------------|-----------|----------------|
| BloomFilter           | O(k)          | n/a           | O(k)                         | n/a       | O(m)           |
| CountingBloomFilter   | O(k)          | O(k)          | O(k)                         | n/a       | O(m counters)  |
| CountMinSketch        | O(d)          | n/a           | O(d) estimate                | n/a       | O(w * d)       |
| HyperLogLog           | O(1) average  | n/a           | O(m) estimate                | n/a       | O(m registers) |
| MinHash               | O(k)          | n/a           | O(k) compare                 | n/a       | O(k)           |
| BitPackedArray        | O(1) by index | O(1) by index | O(1) by index                | O(n)      | O(n * width)   |
| RunLengthEncodedArray | O(r)          | O(r)          | O(r) by index unless indexed | O(r)      | O(r)           |

## Evidence rule

When code lands, this table must be backed by one of:

```text
implementation proof that matches the described algorithm
focused benchmark for hot structures
explicit "not claimed" note
```
