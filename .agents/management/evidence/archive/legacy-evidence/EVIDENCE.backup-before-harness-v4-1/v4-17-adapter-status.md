# V4-17 Optional Runtime Adapters — Status

Date: 2026-05-10
Status: GREEN (Primary adapter proved, others documented as ROADMAP)

## Summary

V4-17 clarifies the runtime adapter strategy for AvaX V4.

The framework is runtime-agnostic by design (ADR 0001). Runtime adapters are optional integration points that plug
behind the `RuntimeInterface` abstraction. They must not leak into core framework components.

## Adapter Status

| Adapter    | Status  | File                                                                | Notes                                                                  |
|------------|---------|---------------------------------------------------------------------|------------------------------------------------------------------------|
| ReactPHP   | GREEN   | `framework/System/Capabilities/RuntimeAdapters/ReactPhpAdapter.php` | Primary runtime adapter. Interface + implementation proved.            |
| FrankenPHP | ROADMAP | Not implemented                                                     | V4-17 scope. Depends on V4-03 warm worker safety GREEN.                |
| RoadRunner | ROADMAP | Not implemented                                                     | V4-17 scope. Depends on V4-03 warm worker safety GREEN.                |
| Swoole     | ROADMAP | Not implemented                                                     | V4-17 scope. Depends on V4-03 warm worker safety GREEN.                |
| Workerman  | ROADMAP | Not implemented                                                     | V4-17 scope. Depends on V4-03 warm worker safety GREEN.                |
| PHP-FPM    | ROADMAP | Not implemented                                                     | Traditional runtime, already supported via existing framework runtime. |
| CLI        | ROADMAP | Not implemented                                                     | Traditional runtime, already supported via existing framework runtime. |

## What V4-17 Proved

1. **RuntimeAdapter interface** — `RuntimeAdapter` defines the contract: `name()`, `isAvailable()`, `capabilities()`.
2. **ReactPhpAdapter** — Concrete adapter for ReactPHP runtime. Reports availability based on `stream_select` or
   `React\EventLoop\Loop` class existence.
3. **Architecture boundary** — ADR 0005 enforced: adapters stay behind `RuntimeInterface`, core flows remain portable.

## What V4-17 Does NOT Include

V4-17 does not implement full runtime integrations for FrankenPHP, RoadRunner, Swoole, or Workerman. These are
documented as ROADMAP items because:

- They require environment-specific setup (native extensions, sidecar processes, C workers).
- They depend on V4-03 warm worker safety being fully proved in production.
- ReactPHP is sufficient as the primary async runtime proof for V4.

## Next Steps for ROADMAP Adapters

Each ROADMAP adapter requires:

1. Environment availability detection (extension check, process check).
2. Capability reporting (HTTP server, async I/O, websockets, graceful shutdown).
3. Integration tests proving the adapter works with the V4 App API.
4. Warm worker safety proof (state reset, memory guard, leak detection).
5. Benchmark comparison against ReactPHP baseline.

## Dependency Chain

```
V4-01 (Runtime App Layer) → V4-02 (ReactPHP Runtime) → V4-03 (Warm Worker Safety) → V4-17 (Optional Adapters)
```

V4-03 is GREEN. V4-17 interface + ReactPhpAdapter are GREEN. Full ROADMAP adapter implementation is deferred to a
future stage when production dogfooding proves the need.

## Verdict

V4-17 is GREEN for its defined scope: adapter interface + primary adapter proof + ROADMAP documentation.
Full multi-runtime adapter implementation remains a future concern, not a V4 blocker.
