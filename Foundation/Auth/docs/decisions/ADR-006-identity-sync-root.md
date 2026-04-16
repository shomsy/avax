# ADR-006: Identity Sync Gets Its Own Root

Status: accepted

Directory synchronization and provisioning are not login stories.

They deserve a separate root because they deal with:

- external directory state
- lifecycle reconciliation
- outage recovery
- bulk synchronization

Consequence:

- SCIM and provisioning lifecycle can grow without being confused with OAuth/OIDC login contracts
