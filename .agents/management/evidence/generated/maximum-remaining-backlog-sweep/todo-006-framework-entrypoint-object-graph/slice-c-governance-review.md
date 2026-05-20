# TODO-006 Slice C Governance Review

## Review Scope

- Branch: `architecture/todo-006-framework-entrypoint-object-graph-slice-c`
- Slice: App public entrypoint runtime/route construction
- Mode: HARNESS-FULL

## Compliance Matrix

| Rule | Status | Evidence |
|---|---|---|
| PublicSurface receives/delegates | PASS | `App` delegates to injected route registrar, scope flows, and request converter. |
| Configuration/composition assembles | PASS | `CreateApplication` and `BootDslEngine` provide App collaborators. |
| Runtime flow focused | PASS | request conversion has a dedicated flow; `RunApplication` remains execution-only. |
| API compatibility | PASS | public `App` methods unchanged. |
| Test quality | PASS | focused PHPUnit and changed-file PHPStan are GREEN. |
| Failure semantics | PASS | scope close remains in `finally`; exception handling unchanged. |
| Runtime performance | PASS_WITH_YELLOW | per-request scope helper construction removed; no benchmark claim. |
| Security | PASS | request data is still sourced from canonical request component; no auth/session behavior changed. |
| Component dogfooding | PASS | uses existing `FrameworkRouteRegistrar`, scope flows, and HTTP request component. |
| No fake OOP | PASS | collaborators own real route/scope/request responsibilities, not wrappers around a single constructor. |
| Broad cleanup | PASS | slice limited to `App` direct construction findings. |

## Findings

No merge-blocking finding.

Accepted YELLOW:

- broader direct-instantiation backlog remains outside this slice
- runtime-composition four HIGH findings remain outside this slice
- `Avax.php` still has public entrypoint assembly that the current direct-instantiation gate explicitly allows as a composition root

## Decision

MERGE_READY_WITH_YELLOW.

TODO-006 must not be closed until `Avax.php` assembly is either remediated or explicitly accepted with source-of-truth evidence.
