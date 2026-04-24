# Facade

`Foundation/Filesystem/Filesystem.php` is the stable public entry point.

It owns the readable API:

- `get()`, `put()`, `append()`, `copy()`, `move()`, `delete()`
- `exists()`, `lastModified()`
- `ensureDirectory()`, `ensureDirectoryIsWritable()`
- `createDirectory()`, `deleteDirectory()`, `clearDirectory()`, `listFiles()`
- `isWritable()`, `setPermissions()`, `hasPermission()`

The facade does not implement raw filesystem calls directly. It delegates to action owners and the selected disk
backend.
