---
frontmatter:
  type: capability-folder
  name: Directories
  description: Directory lifecycle and traversal operations
  triggers: [create, ensure exists, delete, list, clear]
  owner: Avax\Filesystem\Directories
---

# Directories Capability

## Overview
This folder owns directory lifecycle operations.

| File | Responsibility |
|------|----------------|
| `CreateDirectory.php` | Create a directory |
| `EnsureDirectoryExists.php` | Ensure directory exists |
| `EnsureDirectoryIsWritable.php` | Ensure directory is writable |
| `DeleteDirectory.php` | Delete a directory |
| `ListDirectoryFiles.php` | List files in directory |
| `ClearDirectory.php` | Clear directory contents |
| `DirectoryCreateFailed.php` | Directory creation failure |
| `DirectoryDeleteFailed.php` | Directory deletion failure |
| `DirectoryClearFailed.php` | Directory clear failure |