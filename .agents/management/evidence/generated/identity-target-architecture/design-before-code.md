# Identity Target Architecture Design Before Code

## Scope

In scope:

- Move root `Identity` public DSL construction out of `System/PublicSurface/Identity.php`.
- Add a cohesive root runtime/delegation object under allowed `System/Capabilities`.
- Add a root assembly class under `System/Configuration/Builders`.
- Fix `IdentityServiceProvider` so it registers `IdentityConfiguration`.
- Update focused public DSL tests to prove root delegation and provider registration.

Out of scope:

- Full AuthBuilder detox.
- Replacing all static state across Credentials/Tenancy/ExternalIdentity/Tokens.
- Creating `System/Runtime/` until the folder conflict is explicitly accepted or resolved.
- Token cryptography redesign.
- Full Identity suite GREEN claim.

## HLD

The affected boundary is the Identity aggregate PublicSurface. Its purpose is to expose a fluent root DSL while delegating work to sub-surfaces. Object graph construction belongs to Configuration/Builders, not the PublicSurface.

The changed lifecycle is boot/configuration for provider registration and request/runtime for the static root DSL convenience methods. The root DSL remains backward compatible: static methods keep their names and return the same sub-surface types.

## LLD

- `Identity` receives delegation through a private static `runtime()` helper that asks a Configuration builder for the default runtime. The public methods only delegate.
- `Configuration/Builders/IdentityRuntime` assembles the current default sub-surfaces.
- `Capabilities/IdentityRuntime/IdentityRuntime` owns the cohesive set of root Identity sub-surfaces and returns them through exact methods.
- `Capabilities/GuestSession/GuestSessionIdentity` provides a named fail-closed guest session backend for default `Identity::auth()`.
- `IdentityServiceProvider` registers `IdentityConfiguration`, not the deleted `IdentityConfig`.

## Security

The default auth backend is guest-only and fail-closed: it never resolves a user, MFA state, phishing-resistant state, or session id. This preserves the existing characterization behavior while removing anonymous runtime construction from PublicSurface.

## Performance

No reflection, filesystem scanning, env reads, or container lookup is added to the root DSL. The default builder performs explicit construction only. Further optimization can register a container-provided runtime after the static DSL is migrated away from global defaults.

## Public API

- Public API changed: NO for existing static root DSL methods.
- Backward compatible: YES for `auth`, `access`, `credentials`, `tokens`, `tenancy`, `risk`, `externalIdentity`.
- Migration needed: NO for this slice.
- Contract tests updated: YES.
