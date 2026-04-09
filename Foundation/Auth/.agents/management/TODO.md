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
- `updated_at`: 2026-04-06 16:50 CET
- `status`: todo
- `outcome`: Refactor repository structure - introduce src/ boundary
- `acceptance`: src/ contains all source slices (Flow, Capability, Foundation, Configuration), tests/ and docs/ stay at
  root
- `links`: src/

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
