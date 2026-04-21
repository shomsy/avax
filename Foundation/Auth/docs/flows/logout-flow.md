# Logout Flow

Owner: `Identity`

Primary implementation: `System/Flows/Logout/`, `System/Capabilities/Identity/Sessions/Runtime/LogoutAllSessions/`

Sequence:

1. resolve current context
2. revoke current session or full session family
3. revoke refresh lineage where configured
4. emit audit trail
