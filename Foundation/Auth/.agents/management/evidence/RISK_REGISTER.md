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
- `updated_at`: 2026-04-09 18:10 CEST
- `severity`: medium
- `likelihood`: low
- `impact`: Global session revocation for all PHP session backends is still limited because the package only owns the
  current session store, not a session registry.
- `mitigation`: MFA recovery and disablement revoke refresh tokens and clear the current session; documentation now
  calls out that multi-session revocation for stateful session backends requires an application-owned store/registry.
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
