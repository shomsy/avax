# Architecture

Filesystem is organized around explicit capabilities:

- `Files/*` for file lifecycle operations
- `Directories/*` for directory lifecycle operations
- `Paths/*` for low-level path checks and permissions
- `Disks/*` for backend resolution and concrete implementations
- `Configuration/*` for container wiring

The root facade stays intentionally thin and delegates into those slices.
