# Internal

Internal machinery provides reusable components behind the public surface.

## Purpose

This folder contains shared infrastructure that does not belong to a single capability zone but is needed by multiple
operations. It is not a dumping ground for unrelated helpers.

## Components

| File                          | Responsibility                        |
|-------------------------------|---------------------------------------|
| `CollectionState.php`         | Holds immutable collection state      |
| `CollectionItems.php`         | Items container with validation       |
| `DotPath.php`                 | Dot notation parsing and manipulation |
| `CollectionMutationGuard.php` | Mutability policy enforcement         |
| `SearchThreshold.php`         | Search threshold value object         |
| `NormalizedIterable.php`      | Iterable normalization                |
| `JsonEncoding.php`            | JSON encoding helpers                 |
| `JsonDecoding.php`            | JSON decoding helpers                 |
| `OperationResult.php`         | Result wrapper for operations         |

## Design Rules

- These are internal implementation details
- Not part of public API
- Used by capability owner classes
- No business logic here, only technical machinery