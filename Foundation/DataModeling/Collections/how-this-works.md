# Collections

Collections capability zone contains all array manipulation operations.

## Purpose

This folder organizes operations by capability ownership. Each subfolder owns one kind of operation. Internal shared machinery lives in `Internal/`.

## Subfolders

| Folder | Ownership | Examples |
|--------|-----------|---------|
| `Internal/` | Shared machinery, state holders | `CollectionState`, `DotPath` |
| `Create/` | Factory operations | `CreateCollection`, `WrapIntoCollection` |
| `Read/` | Getter operations | `ReadValue`, `ReadValueByPath` |
| `Write/` | Setter operations | `PutValue`, `PutValueByPath` |
| `Transform/` | Transform operations | `MapValues`, `FilterValues` |
| `Aggregate/` | Aggregation operations | `SumValues`, `AverageValues` |
| `Search/` | Search and match operations | `ContainsValue`, `MatchTextFuzzily` |
| `Order/` | Ordering operations | `SortValues`, `ReverseValues` |
| `Convert/` | Conversion operations | `ConvertCollectionToArray` |
| `Strings/` | String operations | `JoinValues`, `TrimValues` |

## Design Rules

- Each subfolder owns exactly one kind of operation
- Public API lives in root `Arrhae` and `Collection`
- Internal machinery stays in `Internal/`
- No junk drawer or utility bucket