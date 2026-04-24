---
frontmatter:
  type: capability-root
  name: Filesystem
  description: Unified filesystem operations facade with disk abstraction
  triggers: [file read, file write, directory operations, disk resolution]
---

# Filesystem

## Overview

`Foundation/Filesystem` exposes one small public facade and keeps the real work in capability owners.

Reading order:

1. `Filesystem.php`
2. `Files/*` and `Directories/*`
3. `Paths/*` for low-level path signals
4. `Disks/*` for backend resolution and local implementation
5. `Configuration/*` for container wiring

## Public Surface

| File                           | Responsibility           |
|--------------------------------|--------------------------|
| `Filesystem.php`               | Root runtime facade      |
| `FilesystemInterface.php`      | Sync contract            |
| `AsyncFilesystemInterface.php` | Async companion contract |

## Invariants

- The root facade stays thin and delegates to action owners.
- `Disks/Disk.php` is the backend contract; `Disks/Local/LocalDisk.php` is the default implementation.
- `Files/*` own file lifecycle behavior.
- `Directories/*` own directory lifecycle behavior, including writability enforcement.
- `Paths/*` remain low-level path checks instead of a second public facade.
