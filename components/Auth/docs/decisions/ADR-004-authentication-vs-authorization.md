# ADR-004: Authentication And Authorization Stay Separate

Status: accepted

Authentication proves identity.
Authorization decides access.

This package keeps them separate because:

- different risks apply
- different policies evolve independently
- mixing them produces unclear ownership and brittle condition chains

Consequence:

- MFA/passkey/session logic stays in `Identity`
- permission, role, ownership, and policy checks stay in `Access`
