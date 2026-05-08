# Data DSL — How This Works

## Three DSLs, One Architecture

AvaX Data provides three first-class DSL facades for working with data:

| DSL        | Purpose                          | Semantics              |
|------------|----------------------------------|------------------------|
| **Arrhae** | Array-native pipeline engine     | PHP arrays, dot-paths  |
| **Collection** | Item/object pipeline DSL     | Iterable, object items |
| **Json**   | JSON document DSL                | JSON decode/encode     |

All three share the same **method vocabulary** (map, filter, where, pluck, groupBy, sortBy, etc.) for consistent data manipulation.

## Dependency Direction

```
Arrhae  ←  independent (no imports from Collection or Json)
  ↑
  ├── Collection  composes Arrhae
  └── Json        composes Arrhae
```

- **Arrhae** is fully independent. It imports zero Collection classes and zero Json classes.
- **Collection** composes an `Arrhae` instance internally. Every pipeline method delegates to `$this->arrhae` and wraps the result back in a `Collection`.
- **Json** composes an `Arrhae` instance internally. Every pipeline method delegates to `$this->arrhae` and wraps the result back in a `Json`.

No traits are shared between the DSLs. No inheritance is used. Composition is the only sharing mechanism.

## What Each DSL Owns

### Arrhae (`Arrhae/Arrhae.php`)
- All pipeline methods implemented inline (map, filter, where, pluck, groupBy, sortBy, etc.)
- Dot-path access for nested array manipulation (via `DataPaths/DotPath`)
- Array-native semantics (native PHP array behavior)
- Factories: `make()`, `from()`, `wrap()`

### Collection (`Collection/Collection.php`)
- Composes `Arrhae` — constructor takes `Arrhae $arrhae`
- Owns: `IteratorAggregate`, `Countable`, read-only `ArrayAccess`
- Owns: collection-specific intent (iterable/object semantics)
- Factory: `Collection::make(iterable)` creates `new self(arrhae: Arrhae::make($items))`
- All pipeline methods delegate: `new self(arrhae: $this->arrhae->map($callback))`

### Json (`Json/Json.php`)
- Composes `Arrhae` — constructor takes `Arrhae $arrhae`
- Owns: `decode()`, `encode()`, `pretty()`, `validate()`, JSON Pointer access (`path()`)
- Factory: `Json::decode(string)` creates `new self(arrhae: Arrhae::make($decoded))`
- All pipeline methods delegate to `$this->arrhae`

## Shared Primitives (Neutral Ownership)

All primitives used by multiple DSLs live in neutral locations — never under a specific DSL's namespace:

| Primitive        | Location                          | Used By            |
|------------------|-----------------------------------|--------------------|
| `MutationGuard`  | `Foundation/Mutability/`          | All DSLs           |
| `MakeCollection` | `Foundation/Normalization/`       | All DSLs           |
| `WrapValue`      | `Foundation/Normalization/`       | All DSLs           |
| `NormalizedIterable` | `Foundation/Normalization/`   | Data structures    |
| `DotPath`        | `DataPaths/`                      | Arrhae, Json       |
| `Pair`           | `Structures/`                     | Arrhae, Collection |
| `Comparator`     | `Structures/`                     | Set, OrderedSet    |
| `MapEntry`       | `Structures/`                     | Map, OrderedMap    |
| `Option/Result`  | `Structures/Option/`, `Structures/Result/` | All DSLs    |

## Operators (Capabilities)

Pipeline operator classes live in capability folders by responsibility:

| Folder        | Responsibility                              |
|---------------|---------------------------------------------|
| `Transform/`  | map, filter, flatten, reduce, each, sort…   |
| `Selection/`  | get by path, has value                      |
| `Aggregate/`  | sum, average, min, max                      |
| `Search/`     | contains, search, text matching             |
| `DataPaths/`  | dot-path nested access                      |

## Architecture Law

```
folder = flow or capability
unit   = responsibility
method = exact action
```

- `Arrhae/` = capability (array pipeline engine)
- `Collection/` = capability (item/object pipeline DSL)
- `Json/` = capability (JSON document DSL)
- `Transform/` = capability (value transformation operators)
- `Selection/` = capability (value selection operators)

No technical dumping grounds. No `Services/`, `Helpers/`, `Utils/`, `Managers/`, or `Internal/` at the capability level.
