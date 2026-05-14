# Federation Operations

Federation is now treated as a tenant-owned operating surface instead of only a
login connector.

## Connection Lifecycle

1. register the tenant connection with domain, provider, optional metadata URL,
   and group-to-role mapping
2. verify the domain with the issued verification token
3. sync metadata from the provider
4. run health checks before or during cutover
5. allow discovery only for verified domains

## Strong Tenant Policy

- one domain maps to one tenant connection
- invalid group-to-role mappings are rejected at registration time
- break-glass bypass is allowed only for `ssoOnly` connections
- unavailable federation health blocks SSO start and forces explicit fallback
  handling

## Break-Glass

- break-glass is a policy decision, not an implicit fallback
- use `evaluateFederationBreakGlassBypass()` only after a health check records
  `degraded` or `unavailable`
- every allow or deny decision leaves an audit event

## Metadata Sync

- store the provider issuer and a metadata hash
- resync on schedule and before large tenant cutovers
- review hash changes before enabling new provider posture in production
