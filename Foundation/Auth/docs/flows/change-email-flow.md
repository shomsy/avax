# Change Email Flow

Owner: `Identity`

Primary implementation: `System/Flow/ChangeEmail/`

Sequence:

1. require authenticated context
2. verify password and freshness requirements
3. issue email change challenge
4. confirm challenge and update user source
5. reset verification posture as needed
