# ExternalIdentity Runtime Safety Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/ExternalIdentity/System/Capabilities/ExternalIdentityRuntime/ExternalIdentityRuntime.php`
- `components/Identity/ExternalIdentity/System/PublicSurface/ExternalIdentity.php`
- `components/Identity/ExternalIdentity/System/Configuration/Assembly/ExternalIdentityGraph.php`
- `components/Identity/ExternalIdentity/System/Configuration/ExternalIdentityServiceProvider.php`
- `components/Identity/System/Configuration/Builders/IdentityRuntime.php`
- `tests/Unit/Components/Identity/ExternalIdentity/ExternalIdentityCharacterizationTest.php`

## Implementation

`ExternalIdentity` PublicSurface now receives an `ExternalIdentityRuntime` and delegates `link()` / `resolve()` behavior.

`ExternalIdentityRuntime` owns the external identity link store dependency for one assembled runtime.

Root Identity default assembly now creates `ExternalIdentity` through `ExternalIdentityGraph::fromStore()` with an explicit in-memory link store.

The characterization test no longer resets private static state by reflection. It now proves separate runtime instances do not share external identity links.

## Boundary Result

- PublicSurface static mutable link-store state removed.
- PublicSurface direct fallback construction removed.
- Configuration assembly owns the default runtime graph.
