# ADR-007: SCIM Lives Under Identity Sync

Status: accepted

SCIM is placed under `IdentitySync`, not `ExternalIdentity`.

Reason:

- SCIM is a provisioning and directory protocol
- its main domain concern is state synchronization
- it is not a login or token negotiation protocol

Consequence:

- SCIM flows are evaluated with sync/lifecycle semantics first
- SSO and OAuth/OIDC remain isolated from provisioning drift logic
