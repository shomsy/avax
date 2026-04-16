# Recover Access Flow

Owner: `Identity`

Primary implementation: `System/Flow/Recover/`, `System/Flow/Mfa/Recover/`

Sequence:

1. begin password or MFA recovery with anti-enumeration behavior
2. throttle abusive attempts
3. issue one-time challenge
4. confirm challenge
5. revoke stale session, factor, and refresh material
