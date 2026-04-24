---
frontmatter:
  type: capability-folder
  name: Paths
  description: Path-level checks and permissions
  triggers: [exists, is writable, is directory, permissions]
  owner: Avax\Filesystem\Paths
---

# Paths Capability

| File | Responsibility |
|------|----------------|
| `PathExists.php` | Check if path exists |
| `PathIsWritable.php` | Check if path is writable |
| `PathIsDirectory.php` | Check if path is directory |
| `ChangePathPermissions.php` | Change path permissions |
| `PathHasPermissions.php` | Check path permissions |