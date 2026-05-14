# SSO Cutover Runbook

Use this when moving a tenant from local login to tenant-owned federation.

## Before Cutover

1. register the federation connection
2. verify the tenant domain
3. sync metadata and record the initial metadata hash
4. validate group-to-role mappings against allowed roles
5. run a health check and confirm it is `healthy`
6. decide whether break-glass bypass is allowed for this tenant

## During Cutover

1. notify tenant admins of the exact cutover window
2. flip the tenant to `ssoOnly` only after health is green
3. test discovery from a verified tenant email
4. test full start and complete login with a tenant admin account
5. export and review the correlated audit events for the cutover

## Rollback

1. disable `ssoOnly`
2. keep the connection config but mark the cutover as rolled back
3. retain audit evidence, metadata hash, and health state
4. re-open cutover only after the cause is fixed and revalidated
