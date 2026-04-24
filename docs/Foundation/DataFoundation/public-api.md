# Public API

Stable root public entrypoints:

- `Avax\DataFoundation\Arrhae`
- `Avax\DataFoundation\Collection`
- `Avax\DataFoundation\Contracts\ArrhaeInterface`
- `Avax\DataFoundation\Contracts\CollectionInterface`

First-class public type families:

- `Values/*`
- `Composites/*`
- `Collections/DataList`, `Set`, `OrderedSet`, `Map`, `OrderedMap`, `MultiMap`, `Sequence`
- `Structures/*`
- `Flows/*`
- `Interop/*`

API notes:

- write-like operations remain immutable-first
- `Collection::pull()` and `Arrhae::pull()` now return `Pair<removedValue, nextState>`
