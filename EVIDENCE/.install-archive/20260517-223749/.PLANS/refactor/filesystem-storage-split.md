# Plan: Refactor Filesystem and Storage into Two Separate Components

**Date:** 2026-05-09
**Stage:** Proposed
**Owner:** Application/Filesystem + Application/Storage
**Area:** Application

## Goal

Refactor the current `Filesystem` component into two separate, focused components:

1. **Filesystem** — low-level local file/folder/path/permission operations
2. **Storage** — high-level named disk/object storage layer

## Core Decision

Filesystem and Storage are **separate components** with clear boundaries.

## Dependency Rule

```
Storage -> Filesystem  ✓ ALLOWED
Filesystem -> Storage  ✗ FORBIDDEN
```

## Target Filesystem Structure

```
components/Application/Filesystem/
  System/
    PublicSurface/
      Filesystem.php              # DI-friendly, takes FilesystemCore

    Flows/
      ReadFile/
        ReadFile.php
      WriteFile/
        WriteFile.php
      AppendToFile/
        AppendToFile.php
      CopyFile/
        CopyFile.php
      MoveFile/
        MoveFile.php
      DeleteFile/
        DeleteFile.php
      CreateDirectory/
        CreateDirectory.php
      DeleteDirectory/
        DeleteDirectory.php
      ClearDirectory/
        ClearDirectory.php
      ListDirectory/
        ListDirectory.php
      CheckPathExists/
        CheckPathExists.php

    Capabilities/
      LocalPaths/
        NormalizePath.php
        ResolvePath.php
        EnsurePathIsInsideRoot.php
        RejectPathTraversal.php

      LocalPermissions/
        CheckPathPermissions.php
        ChangePathPermissions.php
        CheckPathIsWritable.php
        CheckPathIsReadable.php

    Configuration/
      FilesystemConfiguration.php

    Foundation/
      Values/
        FilePath.php
        DirectoryPath.php
        FileContent.php
        FilePermissions.php
        DirectoryListing.php

      Failure/
        FileNotFound.php
        DirectoryNotFound.php
        DirectoryNotEmpty.php
        PermissionDenied.php
        PathTraversalAttempt.php
        FilesystemOperationFailed.php

    how-this-works.md
```

## Target Storage Structure

```
components/Application/Storage/
  System/
    PublicSurface/
      Storage.php                 # Disk factory + default ops

    Flows/
      ReadStoredObject/
        ReadStoredObject.php
      WriteStoredObject/
        WriteStoredObject.php
      DeleteStoredObject/
        DeleteStoredObject.php
      CheckStoredObject/
        CheckStoredObject.php
      CopyStoredObject/
        CopyStoredObject.php
      MoveStoredObject/
        MoveStoredObject.php
      GenerateStoredObjectUrl/
        GenerateStoredObjectUrl.php
      GenerateTemporaryStoredObjectUrl/
        GenerateTemporaryStoredObjectUrl.php

    Capabilities/
      Disks/
        Disk.php                  # Interface
        ResolveDisk.php
        RegisterDisk.php
        RegisteredDisks.php

        LocalDisk/
          LocalDisk.php           # Uses Filesystem through composition
          CreateLocalDisk.php

        S3Disk/
          S3Disk.php              # Only if already real and tested
          CreateS3Disk.php        # Only if already real and tested

      Visibility/
        ObjectVisibility.php
        ResolveObjectVisibility.php

      StoredPaths/
        NormalizeStoragePath.php
        RejectUnsafeStoragePath.php

    Configuration/
      StorageConfiguration.php
      RegisterStorageDisks.php

    Foundation/
      Values/
        DiskName.php
        StoragePath.php
        StoredObject.php
        StoredObjectMetadata.php
        TemporaryUrl.php

      Failure/
        DiskNotFound.php
        StoredObjectNotFound.php
        StorageOperationFailed.php
        TemporaryUrlNotSupported.php
        InvalidStoragePath.php

    how-this-works.md
```

## Naming Rules

**USE:**

- Disk, LocalDisk, S3Disk
- ResolveDisk, RegisterDisk, RegisteredDisks
- StoragePath, StoredObject

**AVOID:**

- Driver, Adapter, Manager, Service, Helper, Utils
- StorageDiskInterface, FilesystemEngine

## Public API

### Filesystem

```php
final readonly class Filesystem
{
    public function __construct(
        private FilesystemCore $core,
    ) {}

    public function read(string $path): string;
    public function write(string $path, string $content): bool;
    public function append(string $path, string $content): bool;
    public function copy(string $source, string $dest): bool;
    public function move(string $source, string $dest): bool;
    public function delete(string $path): bool;
    public function exists(string $path): bool;
    public function createDirectory(string $path, int $permissions = 0o755): bool;
    public function deleteDirectory(string $path): bool;
    public function clearDirectory(string $path): bool;
    public function listDirectory(string $path): array;
    public function isReadable(string $path): bool;
    public function isWritable(string $path): bool;
    public function permissions(string $path): int|null;
    public function changePermissions(string $path, int $permissions): bool;
}
```

### Storage

```php
final class Storage
{
    public static function disk(string $name): Disk;
    public static function defaultDisk(): Disk;
    public static function setDefaultDisk(string $name): void;

    public static function get(string $path): string;
    public static function put(string $path, string $content): bool;
    public static function exists(string $path): bool;
    public static function delete(string $path): bool;
    public static function copy(string $source, string $dest): bool;
    public static function move(string $source, string $dest): bool;
    public static function url(string $path): string;
    public static function temporaryUrl(string $path, \DateTimeInterface $expires): string;
}
```

## Implementation Rules

1. **LocalDisk must use Filesystem through composition, not inheritance**
2. **Config:**
    - Each component owns its typed Configuration
    - May read from global config/filesystems.php if it already exists
    - Do not hardcode paths into LocalDisk
3. **S3Disk:**
    - Only implement if already real and tested
    - If S3 is not ready, document as future work
    - Do not create placeholder classes
4. **Migration:**
    - Inspect existing Filesystem component
    - Classify existing classes (FileOperations vs Disk operations)
    - Move behavior into the new split
    - Preserve useful behavior
    - Delete stale empty folders
    - Update imports and tests
    - No stale references

## Files To Create

### Filesystem (31 files)

**PublicSurface (1):**

- `System/PublicSurface/Filesystem.php`

**Flows (11):**

- `System/Flows/ReadFile/ReadFile.php`
- `System/Flows/WriteFile/WriteFile.php`
- `System/Flows/AppendToFile/AppendToFile.php`
- `System/Flows/CopyFile/CopyFile.php`
- `System/Flows/MoveFile/MoveFile.php`
- `System/Flows/DeleteFile/DeleteFile.php`
- `System/Flows/CreateDirectory/CreateDirectory.php`
- `System/Flows/DeleteDirectory/DeleteDirectory.php`
- `System/Flows/ClearDirectory/ClearDirectory.php`
- `System/Flows/ListDirectory/ListDirectory.php`
- `System/Flows/CheckPathExists/CheckPathExists.php`

**Capabilities (8):**

- `System/Capabilities/LocalPaths/NormalizePath.php`
- `System/Capabilities/LocalPaths/ResolvePath.php`
- `System/Capabilities/LocalPaths/EnsurePathIsInsideRoot.php`
- `System/Capabilities/LocalPaths/RejectPathTraversal.php`
- `System/Capabilities/LocalPermissions/CheckPathPermissions.php`
- `System/Capabilities/LocalPermissions/ChangePathPermissions.php`
- `System/Capabilities/LocalPermissions/CheckPathIsWritable.php`
- `System/Capabilities/LocalPermissions/CheckPathIsReadable.php`

**Configuration (1):**

- `System/Configuration/FilesystemConfiguration.php`

**Foundation (6):**

- `System/Foundation/Values/FilePath.php`
- `System/Foundation/Values/DirectoryPath.php`
- `System/Foundation/Values/FileContent.php`
- `System/Foundation/Values/FilePermissions.php`
- `System/Foundation/Values/DirectoryListing.php`
- `System/Foundation/Failure/FilesystemOperationFailed.php`

**Documentation (1):**

- `System/how-this-works.md`

### Storage (26 files)

**PublicSurface (1):**

- `System/PublicSurface/Storage.php`

**Flows (8):**

- `System/Flows/ReadStoredObject/ReadStoredObject.php`
- `System/Flows/WriteStoredObject/WriteStoredObject.php`
- `System/Flows/DeleteStoredObject/DeleteStoredObject.php`
- `System/Flows/CheckStoredObject/CheckStoredObject.php`
- `System/Flows/CopyStoredObject/CopyStoredObject.php`
- `System/Flows/MoveStoredObject/MoveStoredObject.php`
- `System/Flows/GenerateStoredObjectUrl/GenerateStoredObjectUrl.php`
- `System/Flows/GenerateTemporaryStoredObjectUrl/GenerateTemporaryStoredObjectUrl.php`

**Capabilities (10):**

- `System/Capabilities/Disks/Disk.php`
- `System/Capabilities/Disks/ResolveDisk.php`
- `System/Capabilities/Disks/RegisterDisk.php`
- `System/Capabilities/Disks/RegisteredDisks.php`
- `System/Capabilities/Disks/LocalDisk/LocalDisk.php`
- `System/Capabilities/Disks/LocalDisk/CreateLocalDisk.php`
- `System/Capabilities/Disks/S3Disk/S3Disk.php` (if real)
- `System/Capabilities/Disks/S3Disk/CreateS3Disk.php` (if real)
- `System/Capabilities/Visibility/ObjectVisibility.php`
- `System/Capabilities/Visibility/ResolveObjectVisibility.php`

**StoredPaths (2):**

- `System/Capabilities/StoredPaths/NormalizeStoragePath.php`
- `System/Capabilities/StoredPaths/RejectUnsafeStoragePath.php`

**Configuration (2):**

- `System/Configuration/StorageConfiguration.php`
- `System/Configuration/RegisterStorageDisks.php`

**Foundation (5):**

- `System/Foundation/Values/DiskName.php`
- `System/Foundation/Values/StoragePath.php`
- `System/Foundation/Values/StoredObject.php`
- `System/Foundation/Values/StoredObjectMetadata.php`
- `System/Foundation/Failure/StorageOperationFailed.php`

**Documentation (1):**

- `System/how-this-works.md`

## Files To Modify

- `components/Application/Filesystem/System/PublicSurface/Filesystem.php` — refactor to new API
- `components/Application/Filesystem/System/Configuration/` — review/merge with new structure
- `components/Application/Filesystem/System/Capabilities/` — review/merge with new structure

## Files To Delete (after migration)

- Stale empty folders in Filesystem component
- Duplicate classes (LocalStorage, FilesystemStorage if present)
- Old Disk.php interface (replaced with new Disks/Disk.php)

## Tests Required

### Filesystem Tests

- read file (success + not found)
- write file (create + overwrite)
- append file
- copy file (success + source not found)
- move file
- delete file
- create directory
- delete directory (empty + non-empty)
- clear directory
- list directory (empty + with files + with subdirs)
- path traversal is rejected
- permissions checked/changed (OS dependent)

### Storage Tests

- resolve local disk
- put/get object through local disk
- exists/delete object through local disk
- local disk uses Filesystem (composition proof)
- Storage does not bypass Filesystem for local disk
- unknown disk throws DiskNotFound
- temporaryUrl on unsupported disk throws TemporaryUrlNotSupported
- dependency direction proof (Filesystem -> Storage = 0)

## Validation Commands

```bash
composer validate --no-check-publish
composer dump-autoload -o
vendor/bin/phpunit --no-coverage
vendor/bin/phpstan analyse framework components tests labs/SystemDesignKit --memory-limit=1G --error-format=raw --no-progress
php tooling/refactor/check-component-suite-structure.php
php tooling/refactor/check-duplicate-owners.php
php tooling/refactor/check-namespace-drift.php
php tooling/refactor/check-public-surface.php
php tooling/refactor/check-runtime-leaks.php
```

## Manual Checks

1. `grep -r "Storage" components/Application/Filesystem/System/` — must return 0 matches
2. Forbidden folder check in new components
3. Empty folder check
4. One-class-per-file check
5. Stale reference check

## Risks

1. Breaking existing code that uses old Filesystem API
2. Circular dependency if Storage accidentally imports Filesystem in wrong layer
3. S3 implementation status unknown

## Mitigation

1. Document migration path in CHANGELOG
2. Strict dependency direction checks during implementation
3. Document S3 as future work if not ready

## Final Report Requirements

1. Files changed
2. Before tree
3. After tree
4. Moved class table
5. Deleted path table
6. Filesystem public API proof
7. Storage public API proof
8. LocalDisk composition proof
9. Dependency proof
10. Tests added/updated
11. Validation output
12. Remaining risks
13. Final status

## GREEN Criteria

- Filesystem and Storage are separate components
- Filesystem has zero dependency on Storage
- LocalDisk uses Filesystem through composition
- Local disk behavior is real and tested
- No placeholders
- No empty folders
- PHPStan is clean
- PHPUnit passes
- Governance checks pass