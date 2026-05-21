# Tokens PublicSurface Assembly Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tokens/System/PublicSurface/Tokens.php`
- `tests/Unit/Components/Identity/Tokens/TokensCharacterizationTest.php`

## Implementation

Removed deprecated static assembly helpers `Tokens::hmac()` and `Tokens::fromRuntime()` from the PublicSurface.

Updated token characterization tests to assemble through `TokensGraph::hmac()` and `TokensGraph::fromRuntime()`.

## Boundary Result

`Tokens` now exposes runtime token behavior only. Token object-graph assembly remains in `Configuration/Assembly/TokensGraph`.
