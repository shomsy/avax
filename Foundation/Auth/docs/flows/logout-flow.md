# Logout Flow

Owner: `Identity`

Primary implementation: `System/Flow/Logout/`, `System/Flow/Session/LogoutAllSessions/`

Sequence:

1. resolve current context
2. revoke current session or full session family
3. revoke refresh lineage where configured
4. emit audit trail
