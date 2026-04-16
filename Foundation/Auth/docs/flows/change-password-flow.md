# Change Password Flow

Owner: `Identity`

Primary implementation: `System/Flow/ChangePassword/`

Sequence:

1. require authenticated context
2. verify current password
3. enforce fresh-auth / fresh-MFA policy when needed
4. write new password hash
5. revoke stale sessions, challenges, and refresh state
