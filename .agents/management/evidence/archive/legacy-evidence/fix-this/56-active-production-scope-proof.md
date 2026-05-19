# Active Production Scope Proof

**Date:** 2026-05-16

## 1. Autoload Proof

| Check | Result | Decision |
|---|---|---|
| `Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersion` | YES — autoloaded | Canonical production class |
| `Avax\Components\Application\Pipeline\System\PublicSurface\Pipeline` | YES — autoloaded | Canonical production class |
| `Avax\Components\HTTP\ApiVersioning\System\PublicSurface\ApiVersionResolved` | YES — autoloaded | Canonical production class |
| `Avax\Components\HTTP\ApiVersioning\System\Capabilities\Lifecycle\VersionRegistry` | YES — autoloaded | Canonical production class |
| `Avax\Components\Application\Pipeline\System\Capabilities\PipelineHooks\HookRegistry` | YES — autoloaded | Canonical production class |

## 2. Composer Autoload Paths

Composer autoload uses `components/` and `framework/` paths. No top-level `HTTP/` path is configured.

## 3. Decision

Active production scope is clear and proven:
- All 5 canonical classes autoload from `components/` paths
- No duplicate class definitions exist
- No top-level HTTP/ApiVersioning code is autoloaded
- Single source of truth for API versioning and pipeline components
