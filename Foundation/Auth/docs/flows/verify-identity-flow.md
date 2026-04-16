# Verify Identity Flow

Owner: `Identity`

Primary implementation: `System/Flow/Verify/`

Sequence:

1. begin verification challenge
2. deliver challenge through surrounding product adapter
3. confirm challenge token
4. persist verified state
