# Transform

Transform operations modify and reshape collections.

## Purpose

This folder contains transformation operations. Each class owns one exact action and returns a new collection.

## Classes

| Class | Responsibility |
|-------|---------------|
| `MapValues.php` | Map callback over items |
| `FilterValues.php` | Filter by callback |
| `RejectValues.php` | Reject by callback |
| `ReduceValues.php` | Reduce to single value |
| `EachValue.php` | Execute callback for side effects |
| `TapCollection.php` | Tap for inspection |
| `FlattenValues.php` | Flatten nested arrays |
| `CollapseValues.php` | Collapse nested values |
| `FlipValues.php` | Flip keys and values |
| `UniqueValues.php` | Remove duplicates |
| `PartitionValues.php` | Partition by callback |
| `ChunkValues.php` | Chunk into groups |
| `GroupValues.php` | Group by key |