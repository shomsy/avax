# Recover Access Flow

Owner: `Identity`

Primary implementation: `System/Flows/RecoverAccess/`, `System/Flows/RecoverAccess/PasswordReset/`, `System/Capabilities/Identity/Mfa/Runtime/Recover/`

Sequence:

1. begin password or MFA recovery with anti-enumeration behavior
2. throttle abusive attempts
3. issue one-time challenge
4. confirm challenge
5. revoke stale session, factor, and refresh material
