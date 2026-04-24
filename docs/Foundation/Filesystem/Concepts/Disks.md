# Disks

`Disks/*` is the backend boundary for filesystem I/O.

- `Disk.php` defines the operations the rest of the component can depend on.
- `ResolveDisk.php` maps a configured disk name to a concrete implementation.
- `Local/LocalDisk.php` is the default backend and owns the PHP filesystem calls.

This keeps file and directory actions backend-agnostic without creating a second public facade.
