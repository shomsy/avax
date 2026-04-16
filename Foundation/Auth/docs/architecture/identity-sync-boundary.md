# Identity Sync Boundary

`IdentitySync` owns identity movement between this system and external directories.

It owns:

- SCIM directory registration
- SCIM token rotation
- SCIM outage handling
- user and group sync
- bulk sync execution
- provisioning lifecycle hooks that change externalized identity state

It does not own:

- SSO login
- OAuth/OIDC authorization
- tenant membership policy

Why SCIM lives here:

- SCIM is about directory synchronization and lifecycle reconciliation
- it is not an authentication protocol
- its core risk is provisioning drift, not login proof
