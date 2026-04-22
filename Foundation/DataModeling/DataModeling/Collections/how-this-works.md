# Collections

Capability zones for array operations.

## Purpose

This folder organizes array operations by capability ownership. Each subfolder owns one kind of operation.

## Subfolders

| Folder | Capability |
|--------|------------|
| `Internal/` | Shared machinery |
| `Read/` | Getter operations |
| `Write/` | Setter operations |
| `Transform/` | Map/filter/reduce |
| `Aggregate/` | Sum/avg/min/max |
| `Search/` | Contains/fuzzy |
| `Order/` | Sort/reverse |
| `Convert/` | toJson/toArray |
| `Strings/` | String ops |

## Design Rules

- One capability per subfolder
- Internal machinery in `Internal/`
- No junk drawer