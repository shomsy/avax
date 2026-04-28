---
frontmatter:
  type: configuration-lane
  name: Configuration
  description: Wiring and registration
  triggers: [register filesystem, config]
  owner: Avax\Filesystem\Configuration
---

# Configuration

Configuration owns assembly only. Runtime file and directory behavior must not leak into this folder.

Reading order:

1. `FilesystemConfig.php`
2. `RegisterFilesystem.php`

## Files

| File                     | Responsibility                                                          |
|--------------------------|-------------------------------------------------------------------------|
| `FilesystemConfig.php`   | Configuration object                                                    |
| `RegisterFilesystem.php` | Container registration for config, disk resolver, disk, and root facade |
