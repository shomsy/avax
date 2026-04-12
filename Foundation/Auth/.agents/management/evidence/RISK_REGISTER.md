# Risk Register

Tracks active and accepted risks.

## Entry Format

- `id`:
- `identified_at`:
- `updated_at`:
- `severity`: low | medium | high | critical
- `likelihood`: low | medium | high
- `impact`:
- `mitigation`:
- `owner`:
- `status`: open | accepted | mitigated | closed

## Active Risks

- `id`: AUTH-RISK-008
- `identified_at`: 2026-04-12 23:10 CEST
- `updated_at`: 2026-04-12 23:10 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The kernel now owns sender-constrained token binding metadata and refresh enforcement, but actual HTTP DPoP
  proof verification and mTLS certificate validation remain integration-owned; a deployment could still pass untrusted
  thumbprints into the kernel if its transport layer is weak.
- `mitigation`: Keep sender-constraint verification at the transport or adapter boundary, document that trust boundary
  in `docs/assurance-policy.md` and `docs/boundary.md`, and add concrete HTTP or mTLS adapters before claiming
  full replay-resistant enforcement in a specific deployment.
- `owner`: OAuth integrations
- `status`: accepted

- `id`: AUTH-RISK-007
- `identified_at`: 2026-04-12 21:32 CEST
- `updated_at`: 2026-04-12 23:10 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The package previously lacked an explicit package-owned assurance policy for phishing-resistant privileged
  posture.
- `mitigation`: `Capability/Access/Policy/IdentityPolicyCatalog`, `AccessPolicy::forIdentityPolicy()`, the renamed
  phishing-resistant policy requirement, and OAuth client high-assurance posture now provide first-class package-owned
  enforcement seams.
- `owner`: passkey + admin realm slices
- `status`: mitigated

- `id`: AUTH-RISK-006
- `identified_at`: 2026-04-12 15:36 CEST
- `updated_at`: 2026-04-12 21:32 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The package now owns OAuth, passkey, federation, provisioning, risk, and admin-realm kernel slices, but it
  still does not ship OIDC provider behavior, client-credentials, SCIM runtime, or a full tenant/control-plane model;
  teams could mistake the kernel for a full identity platform.
- `mitigation`: Boundary docs, ADR-001, threat model, and the roadmap now state the exact delivered kernel scope and
  keep OIDC provider, SCIM runtime, deeper tenant ownership, and control-plane workflows as explicit non-goals or next
  tracks.
- `owner`: boundary docs
- `status`: accepted

- `id`: AUTH-RISK-005
- `identified_at`: 2026-04-09 19:21 CEST
- `updated_at`: 2026-04-09 19:21 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The Avax container adapter moved from `System/Configuration/AuthServiceProvider` to
  `Integrations/AvaxContainer/AuthServiceProvider`, which is a compatibility break for consumers importing the old
  namespace directly.
- `mitigation`: The new boundary is documented in `docs/boundary.md` and `docs/architecture.md`; the break is
  intentional to keep the kernel clean and is called out in release notes and the final API freeze proposal.
- `owner`: integration surface
- `status`: accepted

- `id`: AUTH-RISK-003
- `identified_at`: 2026-04-09 18:10 CEST
- `updated_at`: 2026-04-09 18:10 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The package does not yet ship trusted-device support; MFA UX always requires a second factor or a backup
  code on each new login.
- `mitigation`: This is an explicit product choice for now; the MFA slice keeps trusted-device support optional until a
  package-owned device-token and revocation contract is designed safely.
- `owner`: MFA slice
- `status`: accepted

- `id`: AUTH-RISK-004
- `identified_at`: 2026-04-09 18:10 CEST
- `updated_at`: 2026-04-12 15:04 CEST
- `severity`: medium
- `likelihood`: low
- `impact`: Global session revocation for all PHP session backends is still limited because the package only owns the
  current session store, not a session registry.
- `mitigation`: The kernel now ships `Capability/Session/SessionRegistryInterface`, active-session flows, and
  revocation-aware `SessionIdentity`. Full multi-session revocation is covered when applications provide a durable
  registry implementation; deployments without that registry still only control the current session store.
- `owner`: session strategy
- `status`: open

- `id`: AUTH-RISK-001
- `identified_at`: 2026-04-09 15:31 CEST
- `updated_at`: 2026-04-09 21:44 CEST
- `severity`: high
- `likelihood`: high
- `impact`: Mutation tooling now executes, but the current critical auth slices are not yet mutation-hard enough for a
  production-ready claim; covered-only diagnostics report `MSI 61%`, `236` escaped mutants, and `70` timeouts.
- `mitigation`: Add targeted tests for `Identity`, `JwtIdentity`, `ChangePassword`, `RefreshAuthentication`,
  `HmacTokenCodec`, and token-store invariants; stabilize mutation parallelism and eliminate timeout-prone cases before
  release.
- `owner`: local environment
- `status`: open

- `id`: AUTH-RISK-002
- `identified_at`: 2026-04-09 15:31 CEST
- `updated_at`: 2026-04-09 21:12 CEST
- `severity`: low
- `likelihood`: medium
- `impact`: `Integrations/AvaxContainer/AuthServiceProvider` is an optional adapter and was not exercised end-to-end
  because the real Avax container package is not installed locally.
- `mitigation`: The adapter is now physically extracted from the kernel and executed in PHPUnit through a test seam
  that mirrors the minimal container contract. Full runtime verification against the external package is still desirable
  before publish, but kernel correctness no longer depends on that package being present.
- `owner`: optional adapter
- `status`: accepted
