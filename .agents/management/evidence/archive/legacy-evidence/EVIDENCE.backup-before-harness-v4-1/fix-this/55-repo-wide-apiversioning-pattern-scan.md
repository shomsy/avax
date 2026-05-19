# Repo-Wide ApiVersioning Pattern Scan

**Date:** 2026-05-16

## 1. Pattern Results

| Pattern                   |                                                                         Findings |                                   Valid? | Action |
|---------------------------|---------------------------------------------------------------------------------:|-----------------------------------------:|--------|
| `new VersionRegistry`     | 1 in ApiVersioningServiceProvider (production), 4 in tests, 1 in fixture comment | YES — provider registration + test setup | None   |
| `VersionRegistry\|null`   |                                                                                0 |                                      N/A | None   |
| `ApiVersionResolved`      |                                All in canonical `components/HTTP/ApiVersioning/` |            YES — single class definition | None   |
| `??= new VersionRegistry` |               0 (regex matched `new VersionRegistry` in tests, not actual `??=`) |            YES — no lazy fallback exists | None   |
| `?? new VersionRegistry`  |                        0 (regex matched `new VersionRegistry` in valid contexts) |        YES — no null-coalescing fallback | None   |

## 2. Analysis

- `new VersionRegistry` appears only in:
    - `ApiVersioningServiceProvider::register()` — correct, registers singleton
    - Test files — correct, test setup
    - Fixture test comment — correct, documentation of bad pattern

- `VersionRegistry|null` — zero occurrences. No lazy nullable fallback exists anywhere.

- `ApiVersionResolved` — single class definition in
  `components/HTTP/ApiVersioning/System/PublicSurface/ApiVersionResolved.php`. No duplicate class definition exists.

- No lazy `??= new` or `?? new` patterns exist in any ApiVersioning production code.

## 3. Decision

All ApiVersioning patterns are clean. No stale lazy registry fallback. No duplicate class definitions. No
null-coalescing fallback patterns.
