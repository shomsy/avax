# Identity Sync Flow

Owner: `IdentitySync`

Primary implementation: `System/Capabilities/IdentitySync/SCIM/Runtime/`, `System/Capabilities/IdentitySync/SCIM/ProvisioningRuntime/`

Sequence:

1. register or resolve directory
2. authenticate sync request
3. apply user/group lifecycle mutation
4. detect drift and outage state
5. emit audit and sync evidence
