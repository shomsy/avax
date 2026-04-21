# External Identity Boundary

`ExternalIdentity` owns externally negotiated identity and token protocols.

It owns:

- federation connection lifecycle
- OAuth client and grant behavior
- OIDC metadata, PAR, JARM, logout, userinfo
- protocol-facing token issuance and introspection

It does not own:

- local password/MFA/passkey lifecycle
- SCIM directory sync
- tenant membership operations

Rule:

- protocol owner units stay separate from business flow owner units
- federation is about external trust relationships
- SCIM is not grouped here because SCIM is provisioning/sync, not login
