# System Shape

Canonical production root: `System/`

```text
System/
  Auth.php
  AuthInterface.php
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

Reading model:

1. `Auth.php` is the package entry.
2. `Flows/` contains local stories such as `Login`, `Register`, `RecoverAccess`, `VerifyIdentity`, `Scim`,
   `TenantSecurity`, and `Oidc`.
3. `Capabilities/` contains owner zones and shared mechanisms such as `Access`, `Identity`, `ExternalIdentity`,
   `IdentitySync`, `Tenancy`, `Diagnostics`, `Session`, and `Passkey`.
5. `Configuration/` assembles the system.
6. `Foundation/` holds small neutral primitives.

Important implementation decision:

- the package ships plural filesystem roots: `System/Flows/` and `System/Capabilities/`
- owner zones live under `System/Capabilities/` and make system ownership obvious
- no new top-level slice may be added unless it owns a real story or shared boundary
