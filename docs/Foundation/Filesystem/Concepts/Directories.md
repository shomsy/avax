# Directories

`Directories/*` owns directory lifecycle behavior:

- creating directories
- ensuring existence
- ensuring writability
- listing entries
- clearing contents
- deleting directories

Directory-specific failures stay here as well, which keeps error ownership aligned with behavior ownership.
