# Foundation

Foundation contains small neutral primitives and exceptions.

## Purpose

This folder holds the lowest-level primitives that the system stands on. It is not a helper drawer. Only exceptions and
truly neutral technical atoms live here.

## Components

| Path                                             | Responsibility            |
|--------------------------------------------------|---------------------------|
| `Exceptions/DataModelingException.php`           | Base exception            |
| `Exceptions/CollectionMutationException.php`     | Mutation policy violation |
| `Exceptions/InvalidCollectionPathException.php`  | Invalid dot path          |
| `Exceptions/InvalidSearchThresholdException.php` | Invalid search threshold  |
| `Exceptions/CollectionEncodingException.php`     | JSON/XML encoding error   |

## Design Rules

- Only exceptions and tiny technical primitives
- No business logic
- No cross-cutting concerns that belong elsewhere
- Domain logic stays in capability zones