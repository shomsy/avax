# Structure Simulation Boundaries

## Required Boundary Statement

Pure PHP does not own Zend Engine memory layout, CPU cache lines, real lock-free progress guarantees,
or wait-free progress guarantees. Structures in these categories are simulations or runtime-specific
bridges unless backed by an explicit runtime dependency and tests.

## Simulation Structures (Mode S)

These structures are simulations only. Their names must not imply real runtime guarantees.

| Structure | Simulates | Why PHP Cannot Own It |
|---|---|---|
| LockFreeQueue | CPU-level lock-free FIFO | Requires atomic CAS instructions |
| LockFreeStack | CPU-level lock-free LIFO | Requires atomic CAS instructions |
| WaitFreeQueue | CPU-level wait-free FIFO | Requires hardware atomic ops |
| ReadCopyUpdate | RCU read-side critical sections | Requires kernel-level thread scheduling |
| StackFrame | Call stack frame layout | Zend Engine owns the real call stack |
| CallStack | Runtime call stack | Zend Engine owns the real call stack |
| HeapMemory | Process heap allocation | Zend Engine owns PHP memory |
| FreeList | Heap free block tracking | No direct heap access in PHP |
| SlabAllocator | Kernel slab allocator | No kernel memory access in PHP |
| BuddyAllocator | Buddy system page allocation | No direct page table access |
| PageTable | Virtual-to-physical page mapping | OS owns page tables |
| TranslationLookasideBuffer | CPU TLB caching | Hardware-level, not accessible |
| CacheLine | CPU cache line behavior | Hardware-level, not accessible |

## Model Structures (Mode M)

These structures are algorithmic models. They implement the correct algorithm but cannot claim
the same low-level memory/runtime properties as systems languages.

Key model structures: BTree, BPlusTree, FibonacciHeap, SuffixTree, RTree, Octree, VanEmdeBoasTree,
FusionTree, HyperLogLog, PersistentMap, RoaringBitmap, LsmTree, and many more.

## Runtime Bridge Structures (Mode R)

These structures require an explicit runtime backend. They must be named after their backend:

| Structure | Required Backend |
|---|---|
| ConcurrentQueue | Redis, Swoole, parallel, shared memory |
| ConcurrentStack | Redis, Swoole, parallel, shared memory |
| ConcurrentHashMap | Redis, Swoole, parallel |
| BlockingQueue | Redis, Swoole, parallel |