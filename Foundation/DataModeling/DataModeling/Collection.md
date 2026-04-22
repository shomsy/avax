# Collection

Fluent state owner for chainable array operations.

## Purpose

`Collection` provides the full fluent API for array manipulation. It is the primary state owner and delegates to capability owners beneath.

## Public API

### Read Operations

| Method | Description |
|--------|-------------|
| `all()` | Get all items |
| `get()` | Get value by key |
| `has()` | Check key exists |
| `first()` | Get first item |
| `last()` | Get last item |
| `pluck()` | Extract values by key |

### Write Operations

| Method | Description |
|--------|-------------|
| `set()` | Set value by key |
| `forget()` | Remove value |
| `add()` | Append value |
| `pull()` | Remove and return |
| `merge()` | Merge arrays |

### Transform Operations

| Method | Description |
|--------|-------------|
| `map()` | Map callback |
| `filter()` | Filter by callback |
| `reduce()` | Reduce to value |
| `chunk()` | Chunk into groups |
| `groupBy()` | Group by key |
| `unique()` | Remove duplicates |

### Aggregate Operations

| Method | Description |
|--------|-------------|
| `sum()` | Sum numeric values |
| `average()` | Average numeric values |
| `min()` | Find minimum |
| `max()` | Find maximum |

### Search Operations

| Method | Description |
|--------|-------------|
| `contains()` | Check value exists |
| `search()` | Find index |
| `where()` | Filter by key/value |
| `whereIn()` | Filter by key in array |

### Order Operations

| Method | Description |
|--------|-------------|
| `sort()` | Sort items |
| `sortBy()` | Sort by key |
| `reverse()` | Reverse order |
| `shuffle()` | Randomize order |

### Convert Operations

| Method | Description |
|--------|-------------|
| `toArray()` | Convert to array |
| `toJson()` | Convert to JSON |
| `toXml()` | Convert to XML |

## Design Rules

- State owner pattern
- Immutable operations
- Delegates to capability owners