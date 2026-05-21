# JWT Token Value One-Class Cleanup Plan

Date: 2026-05-21

## Slice Scope

- Ensure `JwtTokens.php` contains only `JwtTokens`.
- Keep canonical `RefreshToken.php` and `TokenPair.php` as the value-object owners.
- Do not redesign JWT token payload classes.

## Out of Scope

- Removing unused `JwtTokens`.
- Clock injection or JWT expiry behavior.
- JwtAuth runtime behavior changes.
