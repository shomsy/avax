# Route Cache Plan

**Stage:** V4-04 Developer Experience
**Status:** Proof-of-concept / Foundation
**Owner:** AvaX Framework Team

## Purpose

Enable compiled route table caching for production performance.
Route cache stores a serialized compiled route table to avoid re-parsing on every request.

## Current Implementation

| Capability | File | Status |
|---|---|---|
| CacheRouteTable | `framework/System/Capabilities/Routing/CacheRouteTable.php` | Proof-of-concept |
| LoadCachedRoutes | `framework/System/Capabilities/Routing/LoadCachedRoutes.php` | Proof-of-concept |
| RegisterRouteCommands | `framework/System/Capabilities/Routing/RegisterRouteCommands.php` | Proof-of-concept |

## CLI Commands

- `route:cache` — Compiles and writes route table to cache
- `route:clear` — Removes cached route table

## Architecture

```
route:cache  → CacheRouteTable.write() → storage/cache/routes.php
route:clear  → filesystem delete cached route file
request      → LoadCachedRoutes.load() → cached route table OR fresh parse
```

## Limitations

1. Route cache is proof-of-concept only
2. Full route table compilation requires V4-05+ integration with ApplicationBuilder
3. No signed/verified cache payload yet (security gap)
4. Cache warmup must happen before first production request

## Next Steps

- V4-05: Integrate with ApplicationBuilder for real route table compilation
- V4-12: Add security policy for signed cache payloads
- V4-17: Validate cache behavior across runtime adapters

## Full Plan

See `.agents/management/evidence/generated/route-cache-plan.md` for the complete architecture plan including cache lifecycle, warm worker safety, security considerations, and implementation phases.
