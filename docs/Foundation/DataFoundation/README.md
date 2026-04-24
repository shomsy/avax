# DataFoundation

DataFoundation is the semantic data foundation layer for PHP in this repository.

It provides:

- root array and collection facades through `Arrhae` and `Collection`
- semantic values such as `Option`, `Result`, `Uuid`, `NonEmptyString`, `Money`, and ranges
- fixed-shape composites such as `Pair`, `Tuple`, `Record`, and `MapEntry`
- collection families such as `DataList`, `Set`, `Map`, `MultiMap`, and `Sequence`
- real data structures such as `Stack`, `Queue`, `Deque`, `PriorityQueue`, `RingBuffer`, and `Tree`
- flow abstractions such as `Pipeline`, `LazySequence`, `Batch`, and `Window`
- explicit interop boundaries for arrays, iterables, generators, JSON, and XML

See the companion documents in this folder for mission, layering, dependency, public API, and migration details.
