# Identity Target Architecture — Runtime Compilation Ready

## Design Decision

### Current State (AS-IS)

```
Identity Component
├── Static Facades (deprecated but still primary entry points)
│   ├── Identity::tenancy/credentials/externalIdentity() → new sub-surface
│   ├── Tenancy::resolve/getTenantId/run() → static TenantContext/TenantResolver
│   ├── Credentials::store/read/forget() → static InMemoryCredentialStore
│   ├── ExternalIdentity::link/resolve() → static InMemoryExternalIdentityLinkStore
│   ├── JwtAuth::configure/verify/issue/refresh/revoke() → all static, mutable state
│   └── Policy::define/authorize/allows/register/explain() → static, mutable state
│
├── ServiceProviders (incomplete registration)
│   ├── AuthServiceProvider → delegates to RegisterAuthDefaults (good)
│   ├── TenancyServiceProvider → registers Config + TenantStore, missing TenantContext/Tenancy PS
│   ├── CredentialsServiceProvider → registers Config + CredentialStore, missing Credentials PS
│   ├── ExternalIdentityServiceProvider → registers Config + LinkStore, missing ExternalIdentity PS
│   ├── TokensServiceProvider → registers flows + stores + Tokens PS (good pattern)
│   ├── AccessServiceProvider → registers AuthorizationEngine + Access PS (good pattern)
│   ├── SecurityServiceProvider → minimal
│   └── IdentityServiceProvider → only registers IdentityConfig
│
├── AuthBuilder (560 lines, massive assembly graph)
│   ├── Delegates to sub-graphs (good delegation)
│   ├── withContainer() pulls defaults from container (acceptable)
│   └── ready() validates + assembles (good fail-fast)
│
└── InMemory Stores (22+ implementations, no reset lifecycle)
    └── Suitable for dev/test but unsafe for long-lived workers
```

### Target State (TO-BE)

```
Identity Component
├── Instance-Based Services (DI-managed)
│   ├── Identity → instance, receives injected sub-surfaces
│   ├── Tenancy → instance, receives injected TenantContext + TenantResolver
│   ├── Credentials → instance, receives injected CredentialStoreInterface (deprecated but working)
│   ├── ExternalIdentity → instance, receives injected ExternalIdentityLinkStoreInterface (deprecated but working)
│   ├── JwtAuth → instance, receives injected JwtSigner + TokenVerifier + TokenBlacklist
│   └── Policy → instance, receives injected PolicyEvaluator (deprecated but working)
│
├── ServiceProviders (complete registration)
│   ├── Every active component registers its PublicSurface
│   ├── Every static facade gets setInstance() bridge during boot()
│   ├── Every in-memory store gets reset() registration for worker safety
│   └── AuthServiceProvider registers Auth + Identity as DI services
│
├── Worker Safety
│   ├── All static facades have reset() method
│   ├── All in-memory stores have reset() method
│   ├── ServiceProvider boot() calls reset() for worker safety
│   └── ResettableStateRegistry integration for centralized lifecycle
│
├── AuthBuilder (unchanged for backward compat, DI-first path documented)
│   └── Users may use AuthBuilder (assembly) OR ServiceProviders (DI)
│       Both paths produce the same runtime objects
│
└── Compiled-Readiness
    ├── All dependencies resolvable at compile/verify time
    ├── No runtime discovery (class_exists, container->has)
    ├── All bindings explicit in ServiceProviders
    └── AuthBootstrapValidator catches missing dependencies at boot
```

### Key Design Decisions

#### Decision 1: Static Facades → Deprecated but Working
- **Choice:** Keep deprecated static facades working, add reset()/setInstance()
- **Rationale:** Breaking backward compatibility is risky; deprecation path is safer
- **Trade-off:** Static state remains (mitigated by reset lifecycle)
- **Long-term:** Users migrate to DI injection of interfaces

#### Decision 2: AuthBuilder Remains (Assembly vs DI are Complementary)
- **Choice:** AuthBuilder stays as the assembly graph builder
- **Rationale:** Assembly graphs are valid; they belong in Configuration/Builders
- **Trade-off:** Two paths exist (builder vs ServiceProvider), but both are valid
- **Long-term:** ServiceProvider + DI becomes the recommended path

#### Decision 3: InMemory Stores Get Reset, Not Removed
- **Choice:** Add reset() to all InMemory stores
- **Rationale:** These are dev/test defaults; removing them breaks development
- **Trade-off:** Mutable state persists but with lifecycle management
- **Long-term:** Production should use persistent stores (DB, Redis, etc.)

#### Decision 4: shortcuts.php Deprecation
- **Choice:** Deprecate `auth()` helper, don't remove
- **Rationale:** Userland code depends on this helper
- **Trade-off:** Service locator persists but is documented as deprecated
- **Long-term:** Users inject AuthInterface directly

### Migration Order

1. **Worker Safety Pass** (bounded, non-breaking)
   - Add reset() to all static facades
   - Add reset() to all InMemory stores
   - Register reset hooks in ServiceProvider boot()

2. **Service Locator Pass** (bounded, non-breaking for DI users)
   - Remove `app()` from shortcuts.php, use DI-registered instance
   - Register all public surfaces in ServiceProviders

3. **JwtAuth Conversion** (breaking — new file, keep old)
   - Create instance-based JwtAuthRuntime service
   - Register in TokensServiceProvider
   - Keep old JwtAuth static facade as deprecated bridge

4. **Policy Conversion** (breaking — new file, keep old)
   - Create instance-based PolicyRuntime service
   - Register in AccessServiceProvider
   - Keep old Policy static facade as deprecated bridge

5. **Remaining Static Facades** (incremental)
   - Convert TenantResolver to instance-based
   - Convert Tenancy PublicSurface to instance-based
   - Bridge static facades to DI during boot

### Security Considerations

| Concern | Current | Target | Risk |
|---|---|---|---|
| Token secret in $_ENV | Direct read | Documented deployment requirement | LOW |
| Static blacklist unbounded growth | No limit | reset() + size limit | MEDIUM |
| Tenant context leakage between requests | clear() in boot | reset() + request-scoped context | HIGH |
| Policy definitions shared across requests | Static array | reset() + request-scoped | MEDIUM |
