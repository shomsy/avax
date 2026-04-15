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
- `updated_at`: 2026-04-13 13:20 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: Sender-constrained posture is now enforced by concrete HTTP DPoP and mTLS adapters, but certificate-chain
  trust and reverse-proxy correctness still remain deployment-owned.
- `mitigation`: Use `integrations/http/VerifyOAuthSenderConstraint.php`, keep mTLS certificate validation at the trusted
  edge, and document the remaining transport trust boundary in `docs/assurance-policy.md`,
  `docs/audit-export-operations.md`, and `docs/boundary.md`.
- `owner`: OAuth integrations
- `status`: mitigated

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
- `updated_at`: 2026-04-14 18:05 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The package now ships a practical OIDC lane, workload `client_credentials`, SCIM runtime core, and tenant
  control-plane core. The real remaining risk is scope inflation: teams could still mistake the shipped identity kernel
  for a full hosted identity platform.
- `mitigation`: `docs/STATUS.md`, `docs/product-boundary.md`, `docs/capability-matrix.md`, `docs/choose-vs-external-idp.md`,
  and `docs/upgrade-migration-guide.md` define the shipped kernel scope, non-goals, and migration posture explicitly.
- `owner`: boundary docs
- `status`: accepted

- `id`: AUTH-RISK-005
- `identified_at`: 2026-04-09 19:21 CEST
- `updated_at`: 2026-04-14 18:05 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The Avax container adapter moved from `System/Configuration/AuthServiceProvider` to
  `Integrations/AvaxContainer/AuthServiceProvider`, which is a compatibility break for consumers importing the old
  namespace directly.
- `mitigation`: The boundary change is now documented in `docs/upgrade-migration-guide.md`, enforced through
  `tooling/check-migration-path.php`, and covered by product-boundary tests. The package keeps the kernel clean instead
  of reintroducing the adapter through a BC shim.
- `owner`: integration surface
- `status`: mitigated

- `id`: AUTH-RISK-003
- `identified_at`: 2026-04-09 18:10 CEST
- `updated_at`: 2026-04-13 13:20 CEST
- `severity`: medium
- `likelihood`: medium
- `impact`: The package does not yet ship trusted-device support; MFA UX always requires a second factor or a backup
  code on each new login.
- `mitigation`: This is now an explicit documented product choice in `docs/trusted-device-policy.md`; the MFA slice
  does not imply remembered-device bypass until a package-owned device-token and revocation contract is designed
  safely.
- `owner`: MFA slice
- `status`: accepted

- `id`: AUTH-RISK-004
- `identified_at`: 2026-04-09 18:10 CEST
- `updated_at`: 2026-04-14 18:05 CEST
- `severity`: medium
- `likelihood`: low
- `impact`: Multi-session revocation is now package-owned in enterprise mode through durable registries, but external
  deployments that bypass enterprise mode can still choose weaker session topologies.
- `mitigation`: The package ships SQL and Redis registries, enterprise-mode build-time fail-fast, revocation flows, and
  conformance tests for the durable registry contract. Remaining weaker deployments are a documented product choice, not
  a missing kernel seam.
- `owner`: session strategy
- `status`: mitigated

- `id`: AUTH-RISK-001
- `identified_at`: 2026-04-09 15:31 CEST
- `updated_at`: 2026-04-15 10:52 CEST
- `severity`: high
- `likelihood`: high
- `impact`: Mutation tooling now executes, but the current critical auth slices are not yet mutation-hard enough for a
  production-ready claim; the latest covered mutation run reports `MSI 59%`, `394` escaped mutants, and `63` timeouts
  across the configured high-value source set.
- `mitigation`: Add targeted tests for `Identity`, `JwtIdentity`, `ChangePassword`, `RefreshAuthentication`,
  `HmacTokenCodec`, OIDC request-object validation, and token-store invariants; stabilize mutation parallelism and
  eliminate timeout-prone cases before treating mutation quality as release-hard evidence.
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
