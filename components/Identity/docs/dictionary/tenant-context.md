# Tenant Context

## What It Is

Tenant context is the scope that isolates data, identity, and operations within a multi-tenant system. Tenant context determines which tenant's data and resources are accessible during a request.

Tenant context is resolved per-request and enforced throughout the request lifecycle.

## What It Is NOT

- Tenant context is NOT identity. Tenant context scopes identity. A user may have identities in multiple tenants, but a request operates within one tenant.
- Tenant context is NOT organization. An organization is a business concept. Tenant context is the technical isolation boundary.
- Tenant context is NOT authorization. Tenant isolation is a prerequisite for authorization, but authorization decides permissions within the tenant.
- Tenant context is NOT a database. Tenant context determines which data is visible; the database stores the data.

## Common Confusion

The most dangerous confusion is assuming tenant context is always obvious. Tenant context must be explicitly resolved from the request (subdomain, header, path, token claim, or other signal) and validated before any data access. Assuming tenant context from user identity alone can lead to cross-tenant data leaks.

Another confusion is treating tenant context as static within a session. A session may span tenants if the user has multi-tenant access, but each request must resolve and validate its own tenant context.

## In AvaX

AvaX treats tenant context as:

- A per-request resolved value, not a session-level assumption
- Explicitly resolved before any data access occurs
- Validated against the authenticated identity (the user must have access to the tenant)
- Enforced at the data access boundary, not only at the API boundary
- Observable: tenant resolution and tenant mismatches produce security events
- Part of the identity claim set when the identity is tenant-scoped

Tenant resolution in AvaX is a distinct flow that runs before authorization and data access. It may use multiple signals (token claims, request headers, subdomain, path) but always produces a single validated tenant context per request.
