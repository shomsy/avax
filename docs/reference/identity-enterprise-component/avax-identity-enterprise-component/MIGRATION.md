# Migration Guidance

Do not overwrite the current Identity component blindly.

Recommended import path:

1. Import this package under a temporary reference path.
2. Compare current `components/Identity/**` against this package.
3. Build a migration map:
   - keep current
   - adapt reference idea
   - replace with reference idea
   - reject reference idea
   - needs fresh design
4. Implement one slice at a time.

Suggested slice order:

1. TokenGraph and JwtAuth DI conversion
2. AuthenticationGraph
3. AuthorizationGraph
4. SessionGraph
5. ExternalIdentityGraph and TenancyGraph
6. IdentityRuntimeGraph
7. PublicSurface cleanup

Do not merge unless validation, governance review, evidence and security review are clean.
