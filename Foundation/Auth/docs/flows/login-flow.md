# Login Flow

Owner: `Identity`

Primary implementation: `System/Flow/Login/`

Sequence:

1. receive credentials
2. enforce login throttling
3. verify local password or factor preconditions
4. issue authenticated context or MFA challenge
5. project sessions and tokens through identity runtime
6. emit audit events

Security notes:

- session fixation is mitigated by identity/session runtime rules
- phishing-resistant factor can be required by policy
