# Pass 2: Response Layer Worktree Baseline

## Branch

`main`

## Commit

`cd9bd8a38`

## Worktree Status

```
git status --short: (clean)
git diff --stat: (no changes)
```

## Pre-existing Dirty Files

None. Worktree is clean after Pass 1 commit.

## Files This Pass Will Touch

### Response Component (new/modified)

- `components/HTTP/Response/System/Capabilities/CreateHttpResponse/CreateHttpResponse.php` — NEW: internal capability
- `components/HTTP/Response/System/PublicSurface/Responses.php` — MODIFIED: thin facade delegating to CreateHttpResponse
- `components/HTTP/Response/System/PublicSurface/Response.php` — MODIFIED: remove static factories
- `components/HTTP/Response/System/PublicSurface/shortcuts.php` — MODIFIED: remove or update
- `components/HTTP/Response/System/Flows/BuildResponse/BuildJsonResponse.php` — REMOVED
- `components/HTTP/Response/System/Flows/BuildResponse/BuildTextResponse.php` — REMOVED
- `components/HTTP/Response/System/Flows/BuildResponse/BuildHtmlResponse.php` — REMOVED
- `components/HTTP/Response/System/Flows/BuildResponse/BuildRedirectResponse.php` — REMOVED
- `components/HTTP/Response/System/Flows/BuildResponse/BuildEmptyResponse.php` — REMOVED
- `components/HTTP/Response/System/Configuration/ResponseServiceProvider.php` — NEW

### HTTP/System (modified)

- `components/HTTP/System/Capabilities/ResponseBuilding/ResponseFactory.php` — REMOVED or BC shim

### Router Component (modified)

- `components/HTTP/Router/System/Capabilities/ResponseNormalization/NormalizeControllerResult.php` — MODIFIED: inject
  CreateHttpResponse
- `components/HTTP/Router/System/Capabilities/ErrorResponseBuilding/BuildErrorResponse.php` — MODIFIED: inject
  CreateHttpResponse
- `components/HTTP/Router/System/PublicSurface/shortcuts.php` — MODIFIED: remove new ResponseFactory()

### Framework (modified)

- `framework/System/Flows/HandleIncomingHttp/HandleIncomingHttp.php` — MODIFIED: inject CreateHttpResponse
- `framework/System/PublicSurface/App.php` — MODIFIED if ResponseFactory constructor param changes
- `framework/System/PublicSurface/Avax.php` — MODIFIED if assembly changes

### Tests (modified/added)

- Response component tests — NEW
- Updated existing tests referencing ResponseFactory/Response static factories

### Evidence (new)

- `EVIDENCE/hardening/15-response-layer-preflight.md`
- `EVIDENCE/hardening/16-response-layer-worktree-baseline.md`
- `EVIDENCE/hardening/17-response-layer-inventory.md`
- `EVIDENCE/hardening/18-response-factory-migration.md`
- `EVIDENCE/hardening/19-response-gate-proof.md`
- `EVIDENCE/hardening/20-response-recursive-review.md`
- `EVIDENCE/hardening/21-response-final-validation.md`
- `EVIDENCE/hardening/22-response-truth-reconciliation.md`

## Files This Pass Must NOT Touch

- Router dispatch logic (only response creation parts)
- GraphQL, Workflow, Auth, Session builders
- ServiceProvider coverage cleanup (except ResponseServiceProvider)
- Static facade reset proof (general)
- Boot DSL
- labs/SystemDesignKit

## Untracked Files

Only the EVIDENCE/hardening/ files from this pass.
