# Filesystem Refactor

## Status

- Root public surface normalized around `Filesystem.php`, `FilesystemInterface.php`, and `AsyncFilesystemInterface.php`
- File, directory, path, disk, and configuration ownership folders are now the live source of truth
- `ensureDirectoryIsWritable` is exposed through the root facade instead of staying hidden in removed legacy services
- Disk resolution and default configuration wiring are normalized through `Configuration/*` and `Disks/ResolveDisk.php`
- Legacy characterization tests for removed `FilesystemService`, `LocalFileService`, `DirectoryInitializer`, and
  `Storage/*` layers are retired from the active test matrix
- Ownership docs completed for the live Filesystem tree
- Repo-level docs mirror introduced under `docs/Foundation/Filesystem`
- Review and completion artifacts added under `Foundation/Filesystem/Code-Review-And-ToDo`

## Goals

- keep the root API small and readable
- keep behavior capability-sliced by responsibility
- avoid parallel owners for the same filesystem concept
- keep tests and docs aligned with the live source tree

## Notes

- `RegisterFilesystem.php` still targets the container package boundary, so full wiring execution depends on that
  package being present in the runtime environment.
- PHPUnit execution remains an external validation step until the tool is available in this workspace.
