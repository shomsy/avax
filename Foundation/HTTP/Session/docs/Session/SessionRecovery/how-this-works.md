# SessionRecovery - How This Works

## Purpose

Backup and restore for session state.

## Features

- In-memory snapshots
- Transaction-like operations
- Integrity checking
- Import/export

## Usage

```php
$recovery = new SessionRecovery($store);
$recovery->backup('before_edit');

try {
    // atomic operation
    $recovery->transaction(fn() => /* ... */);
} catch (\Throwable $e) {
    $recovery->restore('before_edit');
}
```