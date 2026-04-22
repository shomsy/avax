# Foundation

Small neutral primitives and exceptions.

## Purpose

This folder holds the lowest-level primitives that the system stands on.

## Components

| Path | Responsibility |
|------|---------------|
| `Exceptions/DataModelingException.php` | Base exception |
| `Exceptions/CollectionMutationException.php` | Mutation violation |
| `Exceptions/InvalidCollectionPathException.php` | Invalid path |
| `Exceptions/InvalidSearchThresholdException.php` | Invalid threshold |
| `Exceptions/CollectionEncodingException.php` | Encoding error |

## Design Rules

- Only exceptions and technical atoms
- No business logic
- No helper drawer