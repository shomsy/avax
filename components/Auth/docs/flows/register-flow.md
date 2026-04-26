# Register Flow

Owner: `Identity`

Primary implementation: `System/Flows/Register/`

Sequence:

1. validate uniqueness and password policy
2. create user through `UserSource`
3. optionally mark email verification required
4. emit audit trail
