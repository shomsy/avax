# Check Authentication Flow

Owner: `Access`

Primary implementation: `System/Flow/AuthenticateRequest/`, `System/Flow/CheckAuthentication/`, `System/Flow/ReadCurrentUser/`

Sequence:

1. inspect session or bearer request shape
2. resolve current authentication context
3. answer `check`, `current`, and `user`
4. expose access entry surface to policy gates
