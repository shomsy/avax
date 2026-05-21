# Auth Shortcut Public Surface Cleanup Plan

Date: 2026-05-21

## Scope

- Remove `app(Auth::class)` service-locator usage from the Auth public shortcut.
- Preserve the global `auth()` helper contract.
- Add focused characterization source for helper delegation.

## Non-Scope

- Do not add the helper to composer autoload.
- Do not redesign global helper strategy across AvaX.
- Do not change Auth public surface methods.

## Design Decision

The helper delegates to the root Identity DSL (`Identity::auth()`), keeping PublicSurface behavior as delegation without direct container access.
