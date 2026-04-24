---
frontmatter:
  type: capability-folder
  name: Disks
  description: Disk backend abstraction and driver resolution
  triggers: [disk resolution, local disk, unsupported driver]
  owner: Avax\Filesystem\Disks
---

# Disks Capability

`Disks/*` owns backend resolution. The rest of the component should not guess storage drivers directly.

| File                        | Responsibility                                             |
|-----------------------------|------------------------------------------------------------|
| `Disk.php`                  | Backend contract used by action owners and the root facade |
| `DiskDefinition.php`        | Named disk definition value object                         |
| `ResolveDisk.php`           | Resolve configured disk by name or default                 |
| `UnsupportedDiskDriver.php` | Unsupported driver error                                   |

## Local Subfolder
| File | Responsibility |
|------|----------------|
| `LocalDisk.php` | Local filesystem backend |
