# DataLoader

`ORM/DataLoader` provides batched relation loading and request-scoped loader coordination.

Primary responsibilities:

- define relation loader contracts
- batch key-based loading
- cache loaded batches during one request or unit of work
- register and resolve relation-specific loaders

This helps reduce N+1 access patterns while keeping batching logic outside repositories and entity metadata readers.
