# Check Authentication Flow

Owner: `Access`

Primary implementation: `System/Flows/CheckAuthentication/`, `System/Flows/CheckAuthentication/AuthenticateRequest/`, `System/Flows/CheckAuthentication/ReadCurrentUser/`

Sequence:

1. inspect session or bearer request shape
2. resolve current authentication context
3. answer `check`, `current`, and `user`
4. expose access entry surface to policy gates
