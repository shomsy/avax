# TODO

Canonical active implementation queue.

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | done | cancelled
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: AUTH-020
- `created_at`: 2026-04-12 18:35 CEST
- `updated_at`: 2026-04-12 21:32 CEST
- `completed_at`: 2026-04-12 21:32 CEST
- `status`: done
- `estimate`: 3h
- `actual`: 2h 57m
- `outcome`: Deliver authorization-policy, passkey-lifecycle, maintenance-job, and audit-export seams that complete the
  remaining practical kernel scope from `REFAKTOR.md`
- `acceptance`: `Capability/Access/Policy/` exists; passkeys support rename in the public facade; cleanup/export flows
  exist inside their owning slices; README/docs/evidence reflect admin realm, federation, passkey, risk, provisioning,
  and maintenance ownership; PHPUnit and PHPStan lanes pass
- `links`: System/Capability/Access/, System/Flow/Passkey/, System/Flow/Session/CleanupExpiredSessions/,
  System/Flow/Recover/CleanupExpiredPasswordResets/, System/Flow/Mfa/Challenge/CleanupExpiredMfaChallenges/,
  System/Flow/OAuth/CleanupExpiredAuthorizationCodes/, System/Flow/Diagnostics/ExportAuditEvents/, README.md, docs/

- `id`: AUTH-019
- `created_at`: 2026-04-12 15:36 CEST
- `updated_at`: 2026-04-12 15:36 CEST
- `status`: done
- `outcome`: Deliver package-owned OAuth/API auth subsystem v1 with client registry, authorization-code + PKCE,
  refresh exchange, revoke, introspection, and public facade wiring
- `acceptance`: `Capability/OAuth/` and `Flow/OAuth/` exist; public clients require `S256` PKCE; authorization codes are
  single use; OAuth refresh reuse revokes the token family; README/docs/evidence reflect the new subsystem; PHPUnit and
  PHPStan lanes pass
- `links`: System/Capability/OAuth/, System/Flow/OAuth/, README.md, docs/

- `id`: AUTH-018
- `created_at`: 2026-04-12 15:04 CEST
- `updated_at`: 2026-04-12 15:04 CEST
- `status`: done
- `outcome`: Turn `REFAKTOR.md` into repo-owned scope artifacts and ship session subsystem v1 with tracked session
  listing, targeted revoke, logout-all, and reset-driven session/challenge revocation
- `acceptance`: ADR and threat model exist; `System/` remains the canonical system root; session registry capability and
  session flows exist; password reset and password change revoke tracked sessions and active MFA challenges; PHPUnit and
  PHPStan lanes pass
- `links`: REFAKTOR.md, docs/adr/001-auth-scope-and-trust-boundaries.md, docs/threat-model.md,
  docs/implementation-roadmap.md, System/Capability/Session/, System/Flow/Session/

- `id`: AUTH-017
- `created_at`: 2026-04-12 14:25 CEST
- `updated_at`: 2026-04-12 14:25 CEST
- `status`: done
- `outcome`: Harden the auth kernel with Argon2id-first password policy, recovery throttling, session lifetime
  enforcement, and regression coverage for the new security edges
- `acceptance`: password hashing defaults to Argon2id when available and rehashes stale hashes on login; password reset
  and MFA recovery starts throttle repeated requests without enumeration leaks; session identity expires idle and
  absolute lifetimes with audit evidence; PHPUnit and PHPStan lanes pass
- `links`: System/Capability/PasswordHashing/, System/Capability/Identity/Session/, System/Capability/Throttle/,
  System/Flow/Recover/, System/Flow/Mfa/Recover/, tests/

- `id`: AUTH-016
- `created_at`: 2026-04-09 21:44 CEST
- `updated_at`: 2026-04-09 21:44 CEST
- `status`: done
- `outcome`: Install a repo-local PHP coverage driver path and repair the mutation execution lane so coverage and
  Infection run without global root access
- `acceptance`: local `pcov` fallback is available through `tooling/run-with-coverage-driver`; `composer mutation`
  starts and completes real mutation analysis; Composer timeout no longer kills the run; release evidence distinguishes
  tooling unblock from actual mutation-quality gaps
- `links`: tooling/run-with-coverage-driver, composer.json, .gitignore, .agents/management/evidence/

- `id`: AUTH-015
- `created_at`: 2026-04-09 21:12 CEST
- `updated_at`: 2026-04-09 21:12 CEST
- `status`: done
- `outcome`: Restore the local Composer toolchain, execute the real verification suite, and harden kernel, adapter, and
  tooling edges found by strict review
- `acceptance`: `composer test`, `composer analyse`, and `composer analyse:strict` pass; optional adapter seams execute
  in tests; strict-review findings on autoloading, PHPUnit config, header parsing, session handling, and final-class
  test doubles are resolved; release evidence reflects the actual blocker state
- `links`: composer.json, phpunit.xml.dist, phpstan.strict.neon, tests/Integrations/, .agents/management/evidence/

- `id`: AUTH-014
- `created_at`: 2026-04-09 19:21 CEST
- `updated_at`: 2026-04-09 19:21 CEST
- `status`: done
- `outcome`: Separate the package into an explicit auth kernel and optional integration surface, extract container glue,
  add HTTP transport adapters, and freeze the public boundary
- `acceptance`: `System/` stays kernel-only, `integrations/` owns adapters, `AuthServiceProvider` is extracted, HTTP
  request/failure mapping is optional, docs/evidence record the new boundary and compatibility risks
- `links`: docs/boundary.md, integrations/, tests/Integrations/

- `id`: AUTH-013
- `created_at`: 2026-04-09 18:10 CEST
- `updated_at`: 2026-04-09 18:10 CEST
- `status`: done
- `outcome`: Implement production-grade MFA with TOTP enrollment, challenge lifecycle, backup codes, recovery, fresh-MFA
  guards, docs, and verification evidence
- `acceptance`: MFA enrollment requires first proof; login stops at `mfa_required`; backup codes are hashed and
  one-time; recovery resets MFA safely; challenge lockout and fresh-MFA protections exist; docs/evidence updated
- `links`: System/Flow/Mfa/, tests/Flows/Mfa/, docs/mfa-flow.md

- `id`: AUTH-012
- `created_at`: 2026-04-09 15:31 CEST
- `updated_at`: 2026-04-09 15:31 CEST
- `status`: done
- `outcome`: Evolve Auth into a public auth kernel with immutable context, ingress ownership, token/session strategy
  parity, and secondary security flows
- `acceptance`: `Auth` exposes stable request/result/context contracts; request auth is centralized;
  refresh/reset/verify/MFA flows exist; diagnostics and release evidence updated
- `links`: README.md, System/Auth.php, System/Flow/AuthenticateRequest/

- `id`: AUTH-011
- `created_at`: 2026-04-07 01:11 CEST
- `updated_at`: 2026-04-07 01:11 CEST
- `status`: done
- `outcome`: Unify authentication identity behind a single façade
- `acceptance`: AuthBuilder wires flows through `System/Capability/Identity/IdentityInterface` with session and JWT
  adapters hidden behind `Identity`
- `links`: System/Capability/Identity/

- `id`: AUTH-010
- `created_at`: 2026-04-06 16:50 CET
- `updated_at`: 2026-04-12 15:04 CEST
- `status`: cancelled
- `outcome`: Refactor repository structure - introduce src/ boundary
- `acceptance`: src/ contains all source slices (Flow, Capability, Foundation, Configuration), tests/ and docs/ stay at
  root
- `links`: System/, AGENTS.md
- `reason`: Superseded by the local repository contract: `System/` is the canonical system root and a `src/` hallway
  would reduce clarity instead of improving it.

- `id`: AUTH-009
- `created_at`: 2026-04-06 16:30 CET
- `updated_at`: 2026-04-07 01:51 CEST
- `status`: done
- `outcome`: Implement In-Memory Rate Limit Storage
- `acceptance`: Add `InMemoryLoginRateLimitStorage` class and unit test for LoginRateLimit gatekeeping
- `links`: System/Flow/Login/RateLimit/

- `id`: AUTH-008
- `created_at`: 2026-04-06 16:30 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: todo
- `outcome`: Fix outdated Examples
- `acceptance`: `examples/session-login.php` using new `AuthBuilder` and `Capability` namespaces
- `links`: examples/

- `id`: AUTH-007
- `created_at`: 2026-04-06 16:30 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: todo
- `outcome`: Audit and fix documentation
- `acceptance`: All `.md` files in `docs/` reflect new taxonomy and `Verb-Noun` flow names
- `links`: docs/

- `id`: AUTH-006
- `created_at`: 2026-04-06 16:30 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: in_progress
- `outcome`: Implement missing tests for Flow, Capability, and Foundation
- `acceptance`: 100% logic coverage for all files in src/
- `links`: tests/

- `id`: AUTH-005
- `created_at`: 2026-04-06 14:00 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: done
- `outcome`: Add @throws docblocks to Logout and Check actions
- `acceptance`: All public methods documented with throws tags

- `id`: AUTH-004
- `created_at`: 2026-04-06 14:00 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: done
- `outcome`: Decouple JwtIdentity from external dependencies
- `acceptance`: JWT adapter uses interfaces, not concrete Avax framework classes

- `id`: AUTH-003
- `created_at`: 2026-04-06 14:00 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: done
- `outcome`: Refactor to feature-sliced architecture
- `acceptance`: Code organized by business flow (Login/, Register/, Session/) not technical type

- `id`: AUTH-002
- `created_at`: 2026-04-06 14:00 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: done
- `outcome`: Fix UserInterface mutability - decide on immutable vs mutable design
- `acceptance`: Remove or properly validate setPassword(), addRole(), removeRole() methods

- `id`: AUTH-001
- `created_at`: 2026-04-06 14:00 CET
- `updated_at`: 2026-04-06 16:30 CET
- `status`: cancelled
- `outcome`: Implement AuthMiddleware authentication check
- `acceptance`: All protected routes require authentication
- `reason`: Superseded by Capability: Access/RequireAuthentication boundary enforcement.
