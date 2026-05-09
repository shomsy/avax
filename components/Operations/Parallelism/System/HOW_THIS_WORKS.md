# Parallelism — How This Works

## What This Component Does

The Parallelism component provides **process-based parallel execution** using Symfony Process for true OS-level parallelism.

It is NOT cooperative concurrency. It does NOT use Fibers. It creates separate OS processes for each work unit.

## Concurrency vs Parallelism

| Aspect | Concurrency | Parallelism |
|--------|------------|-------------|
| Model | Cooperative fibers | Separate OS processes |
| CPU | Single-threaded | Multiple processes |
| Isolation | Shared memory | Process isolation |
| Use case | I/O-bound, cooperative tasks | CPU-bound, blocking work |
| Runtime | `FiberTaskRuntime` | `SymfonyProcessParallelRuntime` |
| Fallback | `CurrentProcessTaskRuntime` | `CurrentProcessParallelRuntime` |

**Use Parallelism when:**
- Tasks are CPU-bound (computation, data processing)
- Tasks perform blocking operations
- You need true CPU-level parallelism
- Process-level fault tolerance matters

**Use Concurrency when:**
- Tasks are I/O-bound and can cooperatively yield
- You need many tasks in a single process
- Memory efficiency matters
- See `Concurrency/System/HOW_THIS_WORKS.md`

## Runtime Selection

`BuildParallelRuntime` detects available runtimes:

1. **Current process runtime** (`CurrentProcessParallelRuntime`) — default, runs closures in-process sequentially
2. **Symfony Process runtime** (`SymfonyProcessParallelRuntime`) — spawns separate PHP processes

Default: `current_process` (safe, no serialization required).

To use Symfony Process runtime, explicitly configure it:

```php
$config = new ParallelismConfig(runtime: 'symfony_process');
$runtime = (new BuildParallelRuntime())->build($config);
```

## Closure Serialization Limitation

**This is the most important fact about this component:**

> Native PHP `serialize()` cannot serialize closures.
> The Symfony Process runtime attempts to serialize closures to send them to worker processes.
> Without a closure serialization library, the Symfony Process runtime will throw on any closure-based work.

### Current state

- `CurrentProcessParallelRuntime` works correctly with ALL closure types (in-process, no serialization needed)
- `SymfonyProcessParallelRuntime` exists and is architecturally sound but **requires a closure serialization library** to function with closures

### Required dependency for process runtime

To enable Symfony Process parallelism with closures, install one of:

```bash
composer require laravel/serializable-closure
# or
composer require opis/closure
```

Then update `SerializeWorkPayload.php` to use the library instead of native `serialize()`.

## Worker Protocol (`bin/avax --payload`)

The `bin/avax --payload <base64>` command is an **internal worker protocol entrypoint**. It is NOT a public CLI API.

### How it works

1. Parent process serializes work (closure + metadata)
2. Base64-encodes the serialized payload
3. Spawns `php bin/avax --payload <base64>` as a child process
4. Child process decodes, deserializes, executes the closure
5. Child process outputs JSON result to stdout
6. Parent process reads stdout, parses JSON, captures result

### Worker output format

**Success:**
```json
{"name": "task_name", "value": <result>, "success": true}
```

**Failure:**
```json
{"name": "worker", "error": "<message>", "success": false, "code": <exit_code>}
```

### Security

The `--payload` command is an **internal worker protocol**:

- It is NOT a public CLI API
- It is NOT safe for untrusted input
- The payload contains serialized PHP closures
- Deserializing untrusted payloads is a remote code execution risk
- Only the parallelism component's internal code should generate payloads
- The worker script does NOT perform signature verification (remaining risk)

## Failure Handling

Worker failures are captured as `ParallelFailure` with:
- Worker name
- Error message from stderr or stdout
- Exit code
- Previous exception (if available)

`throwIfFailed()` throws `ParallelException` with the failure message.

## Timeouts

`StartWorkerProcess` sets a 300-second (5-minute) timeout per process via `$process->setTimeout(300.0)`. This is hardcoded. There is no per-task timeout configuration.

## Remaining Risks

- **Closure serialization**: Symfony Process runtime cannot serialize closures without a library. This is the primary blocker for true process-based parallelism.
- **No payload signing**: Worker payloads are not cryptographically signed. If the worker entrypoint were exposed to untrusted users, it could be exploited.
- **Hardcoded timeout**: 300-second timeout is not configurable per task.
- **Worker script path**: `StartWorkerProcess` defaults to `bin/avax` which must exist and be executable.
- **Output contamination**: If a worker writes non-JSON output before the result JSON, the parent may parse incorrectly.
