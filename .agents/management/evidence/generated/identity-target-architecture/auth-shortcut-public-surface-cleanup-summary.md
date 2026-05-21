# Auth Shortcut Public Surface Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Auth/System/PublicSurface/shortcuts.php`
- `tests/Unit/Components/Identity/Auth/AuthShortcutCharacterizationTest.php`

## Implementation

`auth()` now delegates to `Identity::auth()` instead of calling `app(Auth::class)`.

Focused test source proves the helper remains available and returns the Auth public surface.

## Boundary Result

The Auth shortcut no longer performs service-locator lookup inside PublicSurface code.
