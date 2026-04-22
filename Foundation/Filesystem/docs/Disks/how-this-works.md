---
frontmatter:
  type: capability-folder
  name: Disks
  description: Disk backend abstraction and driver resolution
  triggers: [disk resolution, local disk, unsupported driver]
  owner: Avax\Filesystem\Disks
---

# Disks Capability

| File | Responsibility |
|------|----------------|
| `Disk.php` | Disk interface |
| `DiskDefinition.php` | Disk configuration |
| `ResolveDisk.php` | Resolve disk by name |
| `UnsupportedDiskDriver.php` | Unsupported driver error |

## Local Subfolder
| File | Responsibility |
|------|----------------|
| `LocalDisk.php` | Local filesystem backend |