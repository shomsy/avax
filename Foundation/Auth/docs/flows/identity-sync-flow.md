# Identity Sync Flow

Owner: `IdentitySync`

Primary implementation: `System/Flow/Scim/`, `System/Flow/Provisioning/`

Sequence:

1. register or resolve directory
2. authenticate sync request
3. apply user/group lifecycle mutation
4. detect drift and outage state
5. emit audit and sync evidence
