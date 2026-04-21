# Identity Boundary

`Identity` owns the local account and authenticator lifecycle.

It owns:

- login and logout
- register
- change password
- email verification and change
- password recovery
- session lifecycle entry
- MFA enrollment, challenge, recovery, backup codes
- passkey registration and authentication

It does not own:

- permission checks
- tenant membership
- OAuth/OIDC protocol contracts
- SCIM directory synchronization

If a concern changes how a user proves identity, it belongs here.
If a concern changes what an authenticated user is allowed to do, it belongs in `Access` or `Tenancy`.
