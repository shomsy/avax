# Async IO Decision Record

**Component:** `components/Application/Filesystem/System/Capabilities/AsyncIO/`
**Date:** 2026-04-30
**Status:** Interfaces defined — concrete async adapters pending

---

## Decision

Async filesystem I/O is implemented as a **capability boundary** — interfaces only,
no concrete async runtime dependencies. This allows the AvaX framework to remain
runtime-agnostic while providing a clean contract for async filesystem operations.

## Architecture

### Capability Boundary

The `AsyncFilesystemInterface` defines the contract for all async filesystem operations:

- `asyncRead(path, options)` — Read file contents asynchronously
- `asyncWrite(path, contents, options)` — Write file contents asynchronously
- `asyncExists(path)` — Check file/directory existence asynchronously
- `asyncDelete(path, options)` — Delete file/directory asynchronously
- `asyncListDirectory(path, options)` — List directory contents asynchronously

All methods return `AsyncOperationPromise`, a framework-level promise interface that
concrete async runtimes must implement.

### Promise Interface

`AsyncOperationPromise` provides:

- `then(callable)` — Chain on success
- `catch(callable)` — Handle errors
- `isResolved()` — Check resolution state
- `isRejected()` — Check rejection state
- `getResult()` — Block and retrieve result (discouraged, use only at boundaries)

### Value Objects

- `AsyncReadFile` — DTO describing an async read operation (path, options, promise)
- `AsyncWriteFile` — DTO describing an async write operation (path, contents, options, promise)

These are **descriptors only**, not implementations. They enable operation queuing,
batching, and inspection.

## Concrete Adapters (Future)

Concrete async adapters will be added later for specific runtimes:

| Runtime   | Adapter Class                | Notes                                       |
|-----------|------------------------------|---------------------------------------------|
| ReactPHP  | `ReactFilesystemAdapter`     | Uses ReactPHP event loop + react/filesystem |
| Amp       | `AmpFilesystemAdapter`       | Uses Amp concurrency + amphp/file           |
| Swoole    | `SwooleFilesystemAdapter`    | Uses Swoole async I/O primitives            |
| Workerman | `WorkermanFilesystemAdapter` | Uses Workerman async primitives             |

### Adapter Selection

The adapter should be selected based on the runtime environment:

- CLI with ReactPHP event loop → `ReactFilesystemAdapter`
- CLI with Amp runtime → `AmpFilesystemAdapter`
- Swoole HTTP server → `SwooleFilesystemAdapter`
- Workerman HTTP server → `WorkermanFilesystemAdapter`

Framework configuration should specify which adapter to use. If no async runtime is
detected, fall back to `SyncAsyncFilesystemAdapter`.

## SyncAsyncFilesystemAdapter — Stopgap

**WARNING:** `SyncAsyncFilesystemAdapter` is clearly labeled and documented as a
**sync-under-async-interface** implementation.

### What it does:

- Wraps blocking PHP filesystem calls (`file_get_contents`, `file_put_contents`, etc.)
- Uses PHP Fibers to structure the code as if it were async
- Returns `SyncOperationPromise` which implements `AsyncOperationPromise`
- From the caller's perspective, the interface looks async

### What it does NOT do:

- It does NOT provide non-blocking I/O
- It does NOT integrate with any event loop
- It does NOT yield control during I/O operations
- The calling thread IS blocked during filesystem operations

### Why it exists:

1. Unblocks development and testing before async runtime integration
2. Allows code to be written against the async interface from day one
3. Makes the eventual swap to a real async adapter transparent (same interface)
4. Provides a working implementation for environments where no async runtime is available

### When to replace:

Replace `SyncAsyncFilesystemAdapter` with a runtime-specific adapter as soon as
the target async runtime is available in the deployment environment.

## Rules

### No Silent Faking

- `SyncAsyncFilesystemAdapter` is **explicitly named** to indicate it's sync underneath
- The class docblock contains prominent warnings about its limitations
- No "silent" async-to-sync fallback — the adapter choice must be explicit
- Logging should indicate when the sync adapter is in use (recommended for production monitoring)

### Interface Purity

- `AsyncFilesystemInterface` contains NO implementation details
- `AsyncOperationPromise` is a clean interface with no runtime coupling
- Value objects (`AsyncReadFile`, `AsyncWriteFile`) are descriptors, not executors

### Fiber Usage

- PHP 8.1+ Fibers are used for structural consistency, not for true async
- When a real async adapter is implemented, Fibers will be used properly with
  `Fiber::suspend()` at I/O boundaries and event loop resumption

## File Structure

```
Capabilities/AsyncIO/
├── AsyncFilesystemInterface.php    # Main capability boundary interface
├── AsyncOperationPromise.php       # Promise interface for all async operations
├── AsyncReadFile.php               # Value object for read operation descriptors
├── AsyncWriteFile.php              # Value object for write operation descriptors
└── SyncAsyncFilesystemAdapter.php  # Stopgap: sync calls under async interface
```

## Future Work

1. Implement `ReactFilesystemAdapter` using `react/filesystem` package
2. Implement `AmpFilesystemAdapter` using `amphp/file` package
3. Implement `SwooleFilesystemAdapter` using Swoole async primitives
4. Implement `WorkermanFilesystemAdapter` using Workerman async primitives
5. Add adapter factory/resolver for automatic adapter selection
6. Add production logging when sync adapter is in use
7. Consider batch/bulk operations for high-throughput scenarios
8. Add file streaming support for large files (`asyncReadStream`, `asyncWriteStream`)
9. Add file metadata operations (`asyncStat`, `asyncSize`, `asyncPermissions`)
10. Add file watching/tailing capabilities (`asyncWatch`)
