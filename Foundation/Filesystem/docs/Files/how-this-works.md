---
frontmatter:
  type: capability-folder
  name: Files
  description: File-level operations and failures
  triggers: [read, write, append, delete, copy, move]
  owner: Avax\Filesystem\Files
---

# Files Capability

## Overview
This folder owns all file-level operations.

## Files

| File | Responsibility |
|------|----------------|
| `ReadFile.php` | Read file contents |
| `WriteFile.php` | Write content (overwrite mode) |
| `AppendToFile.php` | Append content to file |
| `DeleteFile.php` | Delete a file |
| `CopyFile.php` | Copy a file |
| `MoveFile.php` | Move a file |
| `ReadFileLastModifiedAt.php` | Get file modification time |
| `FileNotFound.php` | File not found failure |
| `FileWriteFailed.php` | Write failure |
| `FileDeleteFailed.php` | Delete failure |
| `FileCopyFailed.php` | Copy failure |
| `FileMoveFailed.php` | Move failure |

## Usage
```php
$disk = Filesystem::disk();
$content = (new ReadFile(disk: $disk))->execute(path: '/path/to/file');
```
