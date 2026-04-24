# How This Works

`Foundation/Exceptions` owns the DataModeling exception hierarchy.

- `DataModelingException.php` is the base runtime exception.
- `CollectionMutationException.php` covers immutable/locked mutation attempts.
- `CollectionEncodingException.php` covers conversion failures.
- `InvalidCollectionPathException.php` and `InvalidSearchThresholdException.php` cover invalid internal arguments.

All component-specific failures should terminate through this hierarchy rather than raw generic exceptions where ownership matters.
