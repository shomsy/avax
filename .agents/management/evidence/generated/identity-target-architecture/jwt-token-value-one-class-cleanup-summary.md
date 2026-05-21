# JWT Token Value One-Class Cleanup Summary

Date: 2026-05-21

## Changed Files

- `components/Identity/Tokens/System/Capabilities/JwtAuth/Tokens/JwtTokens.php`

## Implementation

Removed duplicate `RefreshToken` and `TokenPair` definitions from `JwtTokens.php`.

Canonical value-object owners remain:

- `RefreshToken.php`
- `TokenPair.php`

## Boundary Result

`JwtTokens.php` now contains only `JwtTokens`.
