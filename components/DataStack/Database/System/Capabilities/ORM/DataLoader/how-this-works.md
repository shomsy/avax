# How This Works

`ORM/DataLoader` owns batched relation loading and request-scoped loader coordination.

- `DataLoaderInterface.php` defines the narrow contract for relation-specific loaders.
- `BatchLoader.php` provides key-batched execution plus request cache semantics.
- `LoaderRegistry.php` maps relation names to concrete loaders.
- `LoaderContext.php` carries request-local metadata needed by loaders.
- `LoaderCallback.php` documents the callback shape for ad-hoc registrations.

This slice exists to prevent N+1 access patterns without leaking batching details into repositories or entity metadata.
