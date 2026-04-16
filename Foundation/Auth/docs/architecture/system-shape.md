# System Shape

Canonical production root: `System/`

```text
System/
  Auth.php
  AuthInterface.php
  Access/
  Diagnostics/
  ExternalIdentity/
  Identity/
  IdentitySync/
  Tenancy/
  Flow/
  Capability/
  Configuration/
  Foundation/
```

Reading model:

1. `Auth.php` is the package entry.
2. Root ownership zones provide explicit top-level ownership.
3. `Flow/` contains local stories such as `Login`, `Register`, `Scim`, `TenantSecurity`, `Oidc`.
4. `Capability/` contains shared mechanisms such as `Access`, `Identity`, `OAuth`, `Oidc`, `Tenant`, `Session`.
5. `Configuration/` assembles the system.
6. `Foundation/` holds small neutral primitives.

Important implementation decision:

- the package keeps `System/Flow/` as the concrete flow lane to avoid unnecessary namespace churn
- the root ownership zones sit above it and make system ownership obvious
- no new top-level slice may be added unless it owns a real story or shared boundary
