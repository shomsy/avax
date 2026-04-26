# Authorization Flow

Authorization is now a thin boundary over `CurrentAuthentication`.

## Boundaries

- `RequireAuthentication`
- `RequireRole`
- `RequirePermission`

## Behavior

1. A request enters through `authenticateRequest()`.
2. `AuthenticateRequest` stores the resolved immutable context.
3. `Access` boundaries read that context.
4. Missing auth yields `Unauthenticated`.
5. Missing role/permission yields `RoleDenied` or `PermissionDenied`.

## Why This Matters

- Authorization no longer knows whether the user came from session or bearer token.
- Protected routes, jobs, and events can all delegate to the same current-context boundary.
