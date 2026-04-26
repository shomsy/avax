---
frontmatter:
  type: sub-capability-folder
  name: Disks/Local
  description: Local filesystem backend
  owner: Avax\Filesystem\Disks\Local
---

# Local Disk

`LocalDisk.php` is the default backend implementation.

It owns the real PHP filesystem calls:

- `file_get_contents()` / `file_put_contents()`
- `copy()` / `rename()` / `unlink()`
- `mkdir()` / `rmdir()` / `chmod()`
- directory listing and recursive clearing

The rest of the component should delegate here through `Disks/Disk.php` instead of calling raw filesystem functions from
multiple places.

## Files

- `LocalDisk.php` - Full local filesystem implementation
