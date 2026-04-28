# Foundation Rules

`System/Foundation/` is for small neutral primitives only.

Allowed:

- `Clock`
- `IdGenerator`
- narrow low-level interfaces that do not deserve a full capability slice

Not allowed:

- auth business logic
- policy engines
- random utilities
- mixed helpers
- feature orchestration

If a unit knows anything meaningful about login, OAuth, MFA, tenancy, or SCIM, it does not belong in `Foundation/`.
