# TODO

Completed implementation archive. No active items remain.

## Entry Format

- `id`:
- `created_at`:
- `updated_at`:
- `status`: todo | in_progress | done | cancelled
- `outcome`:
- `acceptance`:
- `links`:

## Current Items

- `id`: AUTH-032
- `created_at`: 2026-04-16 11:20 CEST
- `updated_at`: 2026-04-16 11:20 CEST
- `completed_at`: 2026-04-16 11:20 CEST
- `status`: done
- `estimate`: 6h
- `actual`: 3h 10m
- `outcome`: Close the remaining REFAKTOR architecture plan by introducing explicit root-zone owner facades, canonical architecture/ADR/flow/security docs, migration mapping, and characterization coverage for the legacy Auth facade
- `acceptance`: `System/Capability/Access`, `System/Capability/Identity`,
  `System/Capability/ExternalIdentity`, `System/Capability/IdentitySync`, `System/Capability/Tenant`, and
  `System/Capability/Diagnostics` ship real owner units; `Auth` delegates through those zones instead of
  directly owning the full flow graph; `docs/architecture/`, `docs/decisions/`, `docs/flows/`, and
  `docs/security/` exist with truthful content; `tests/Characterization/` protects legacy facade behavior; PHPUnit and
  PHPStan lanes pass
- `links`: System/Auth.php, System/Capability/Access/AccessFacade.php,
  System/Capability/Identity/IdentityFacade.php, System/Capability/ExternalIdentity/ExternalIdentityFacade.php,
  System/Capability/IdentitySync/IdentitySyncFacade.php, System/Capability/Tenant/TenancyFacade.php,
  System/Capability/Diagnostics/DiagnosticsFacade.php, docs/architecture/,
  docs/decisions/, docs/flows/, docs/security/, tests/Characterization/, REFAKTOR.md

- `id`: AUTH-031
- `created_at`: 2026-04-15 10:05 CEST
- `updated_at`: 2026-04-15 10:05 CEST
- `completed_at`: 2026-04-15 10:05 CEST
- `status`: done
- `estimate`: 5h
- `actual`: 2h 10m
- `outcome`: Close the remaining package-owned OIDC completeness gap with asymmetric/public-client request-object
  verification, thin dynamic client registration HTTP lifecycle, and truthful canonical status updates
- `acceptance`: public and confidential clients can register verifier key material through package-owned flows and HTTP
  surfaces; RSA-signed request objects pass only with matching key + claims; OIDC registration supports
  create/update/disable and discovery publication; canonical docs stop claiming dynamic registration and public-client
  JAR verification are external; targeted PHPUnit slices pass
- `links`: System/Flow/Oidc/PushAuthorizationRequest/, System/Capability/OAuth/OAuthClient.php,
  integrations/http/Oidc/ServeOidcHttpSurface.php, integrations/http/TenantSecurity/ServeTenantSecurityHttpSurface.php,
  tests/Flows/Oidc/PushAuthorizationRequestTest.php, tests/Flows/OAuth/RegisterClientTest.php,
  tests/Flows/OAuth/UpdateClientTest.php, tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php,
  tests/Integrations/Http/TenantSecurity/ServeTenantSecurityHttpSurfaceTest.php, docs/STATUS.md,
  docs/oidc-conformance-matrix.md, REFAKTOR.md

- `id`: AUTH-030
- `created_at`: 2026-04-15 00:35 CEST
- `updated_at`: 2026-04-15 01:28 CEST
- `completed_at`: 2026-04-15 01:28 CEST
- `status`: done
- `estimate`: 4h
- `actual`: 2h 20m
- `outcome`: Close the remaining iteration-local source-truth, system-shape, release-gate, deployment-boundary, and
  explainability drift by adding package-owned verification checks, integration suite separation, and truthful canonical
  documentation
- `acceptance`: `integrations/release/CheckSystemShape.php` and `tooling/check-system-shape.php` fail on junk-drawer
  architecture drift; `CheckSourceTruth` verifies capability evidence paths; `phpunit.xml.dist` exposes a clean
  `Integration` suite; `composer release:gate` runs system-shape, analysis, tests, source-truth, migration, and release
  evidence; STATUS/REFAKTOR/docs reflect executable deployment-trust and explainability runtime posture; PHPUnit,
  PHPStan, strict PHPStan, `test:integration`, `source-truth`, `system-shape`, and `release:gate` pass
- `links`: integrations/release/CheckSystemShape.php, integrations/release/CheckSourceTruth.php,
  tooling/check-system-shape.php, composer.json, phpunit.xml.dist, docs/STATUS.md, docs/deployment-trust-boundary.md,
  docs/support-explainability.md, REFAKTOR.md

- `id`: AUTH-029
- `created_at`: 2026-04-14 21:30 CEST
- `updated_at`: 2026-04-14 23:50 CEST
- `completed_at`: 2026-04-14 23:50 CEST
- `status`: done
- `estimate`: 6h
- `actual`: 2h 20m
- `outcome`: Rewrite release and source-truth tooling into package-owned integrations, archive historical merged-state
  noise,
  document the migration and deployment boundary canonically, and realign package status with executable evidence
- `acceptance`: `integrations/release/` ships conformance, evidence-bundle, migration-check, and source-truth checks;
  `tooling/*.php` are thin entrypoints without undeclared dependencies; `docs/STATUS.md`, `docs/product-boundary.md`,
  `docs/capability-matrix.md`, `docs/upgrade-migration-guide.md`, and `Auth.txt` agree on shipped scope; PHPUnit and
  PHPStan lanes pass
- `links`: integrations/release/, tooling/, docs/STATUS.md, docs/product-boundary.md, docs/capability-matrix.md,
  docs/upgrade-migration-guide.md, docs/supported-deployment-profiles.md, docs/choose-vs-external-idp.md, REFAKTOR.md

- `id`: AUTH-028
- `created_at`: 2026-04-14 09:40 CEST
- `updated_at`: 2026-04-14 09:40 CEST
- `completed_at`: 2026-04-14 09:40 CEST
- `status`: done
- `estimate`: 8h
- `actual`: 3h 50m
- `outcome`: Replace failed generated integration slices with package-owned lifecycle, tenant product, tenant-owned
  OAuth
  client management, and SCIM groups/bulk features wired through the canonical kernel and adapter surfaces
- `acceptance`: `Capability/Lifecycle/` exists and is used by provisioning, federation, and SCIM flows;
  `Capability/Tenant/`
  and `Flow/Tenant/` ship create/invite/accept/read/suspend/remove/transfer behavior; tenant-admin HTTP routes expose
  tenant membership and tenant-owned OAuth client management; SCIM publishes `/Groups` and `/Bulk` over real kernel
  flows; PHPUnit and PHPStan lanes pass
- `links`: System/Capability/Lifecycle/, System/Capability/Tenant/, System/Flow/Tenant/, System/Flow/OAuth/,
  System/Flow/Scim/ReadGroups/, System/Flow/Scim/Bulk/, integrations/http/TenantSecurity/,
  integrations/http/Scim/, REFAKTOR.md, docs/tenant-control-plane.md, docs/scim-runtime-boundary.md

- `id`: AUTH-027
- `created_at`: 2026-04-13 20:25 CEST
- `updated_at`: 2026-04-13 20:25 CEST
- `completed_at`: 2026-04-13 20:25 CEST
- `status`: done
- `estimate`: 4h
- `actual`: 1h 18m
- `outcome`: Close the remaining adapter and release-hardening gaps with OIDC, SCIM, and tenant-security HTTP surfaces,
  dependency review and rollback evidence tooling, and CI-enforced quality/release gates
- `acceptance`: Framework-neutral HTTP adapters publish OIDC discovery/JWKS/userinfo, SCIM metadata plus `/Users` CRUD,
  and tenant security admin routes; dependency review policy and rollback evidence are first-class release integrations;
  Composer scripts and GitHub workflows enforce dependency review, secret scan, provenance, SBOM, and rollback proof;
  README/docs/REFAKTOR reflect the new package boundary and remaining external gaps; PHPUnit and PHPStan lanes pass
- `links`: integrations/http/Oidc/, integrations/http/Scim/, integrations/http/TenantSecurity/, integrations/release/,
  tooling/review-composer-dependencies.php, tooling/create-rollback-evidence.php, .github/workflows/, REFAKTOR.md

- `id`: AUTH-026
- `created_at`: 2026-04-13 19:20 CEST
- `updated_at`: 2026-04-13 19:20 CEST
- `completed_at`: 2026-04-13 19:20 CEST
- `status`: done
- `estimate`: 6h
- `actual`: 2h 05m
- `outcome`: Close the remaining kernel-local identity-platform gaps with OIDC provider behavior, SCIM runtime,
  tenant-security control-plane workflow, rollover-aware OIDC signing overlap, and truthful repo documentation/status
- `acceptance`: `Capability/Oidc/`, `Capability/Scim/`, and `Capability/TenantSecurity/` ship validated runtime
  behavior;
  `Auth` exposes OIDC, SCIM, workload, and tenant-security public flows; OIDC JWKS overlap is supported through
  `RotatingOidcProvider`; README/docs/REFAKTOR reflect delivered kernel scope and remaining external surfaces; PHPUnit,
  PHPStan, and strict PHPStan lanes pass
- `links`: System/Capability/Oidc/, System/Capability/Scim/, System/Capability/TenantSecurity/, System/Flow/Oidc/,
  System/Flow/Scim/, System/Flow/TenantSecurity/, docs/oidc-conformance-matrix.md,
  docs/oidc-key-rollover-runbook.md, REFAKTOR.md

- `id`: AUTH-025
- `created_at`: 2026-04-13 15:12 CEST
- `updated_at`: 2026-04-13 15:12 CEST
- `completed_at`: 2026-04-13 15:12 CEST
- `status`: done
- `estimate`: 5h
- `actual`: 1h 12m
- `outcome`: Close the remaining kernel-local machine-identity and release-rigor gaps with workload inventory,
  audience-bound workload policies, reloadable key-ring runtime, release tooling, and safer optional Foundation HTTP
  CSRF adapters
- `acceptance`: OAuth workload clients expose inventory and per-audience scope boundaries; runtime HMAC key rings can
  rotate without redeploy; automated rollover and compromise drills plus crypto-agility tests pass; local SBOM,
  provenance, artifact-signing, and secret-scan tooling exists; optional `Foundation/HTTP/Security` CSRF utilities no
  longer log raw tokens and keep bounded token windows; PHPUnit and PHPStan lanes pass
- `links`: System/Flow/OAuth/ReadWorkloadIdentities/, System/Flow/Token/FileBackedHmacKeyRingCodec.php,
  integrations/release/, tooling/generate-sbom.php, tooling/create-release-provenance.php,
  tooling/scan-committed-secrets.php, tests/Integrations/Release/ReleaseToolingTest.php,
  ../HTTP/Security/CsrfTokenManager.php, ../HTTP/Security/VerifyCsrfToken.php

- `id`: AUTH-024
- `created_at`: 2026-04-13 13:35 CEST
- `updated_at`: 2026-04-13 14:39 CEST
- `completed_at`: 2026-04-13 14:39 CEST
- `status`: done
- `estimate`: 4h
- `actual`: 1h 04m
- `outcome`: Close the remaining kernel-local perfection gaps with workload identity runtime, legal-hold aware audit
  export, explicit replay/fixation/CSRF regressions, and adapter-ready persistence/job examples
- `acceptance`: OAuth supports `client_credentials` for workload clients with audience and sender-constraint posture;
  audit export can preserve evidence under legal hold; explicit tests cover DPoP URI mismatch, TOTP replay, passkey
  replay, CSRF example middleware, session fixation, break-glass auditability, and full deprovision revocation; example
  migrations and maintenance-job entrypoints exist; PHPUnit and PHPStan lanes pass
- `links`: System/Flow/OAuth/ExchangeClientCredentials/, System/Flow/Token/ResolvedWorkloadToken.php,
  integrations/diagnostics/ContextFlagAuditLegalHoldPolicy.php, tests/Flows/OAuth/OAuthFlowTest.php,
  tests/Integrations/Http/RequireCsrfProtectionTest.php, examples/persistence/migrations/, examples/jobs/

- `id`: AUTH-023
- `created_at`: 2026-04-13 11:35 CEST
- `updated_at`: 2026-04-13 13:20 CEST
- `completed_at`: 2026-04-13 13:20 CEST
- `status`: done
- `estimate`: 5h
- `actual`: 1h 45m
- `outcome`: Close the new `REFAKTOR.md` P0/P1 operational gaps with browser-session guidance, sender-constrained HTTP
  adapters, SIEM-grade audit exporters, federation operations, policy examples, multi-key token verification, and the
  missing runbook/control-plane docs
- `acceptance`: Browser/session docs and examples exist; DPoP and mTLS verification adapters plus tests exist; audit
  exporters, security notifications, correlation propagation, and PII masking tests exist; federation domain
  verification, metadata sync, health checks, and break-glass policy flows exist with tests; high-assurance examples,
  workload/OIDC/SCIM/control-plane docs, and multi-key token verification are committed; PHPUnit and PHPStan lanes pass
- `links`: docs/browser-session-deployment.md, docs/audit-export-operations.md, docs/federation-operations.md,
  docs/sso-cutover-runbook.md, docs/workload-identity.md, docs/oidc-provider-boundary.md,
  docs/scim-runtime-boundary.md, docs/tenant-control-plane.md, System/Flow/Federation/,
  integrations/http/VerifyOAuthSenderConstraint.php, integrations/diagnostics/,
  System/Flow/Token/MultiKeyHmacTokenCodec.php

- `id`: AUTH-022
- `created_at`: 2026-04-12 22:39 CEST
- `updated_at`: 2026-04-12 23:10 CEST
- `completed_at`: 2026-04-12 23:10 CEST
- `status`: done
- `estimate`: 3h
- `actual`: 31m
- `outcome`: Add explicit assurance policy tiers, sender-constrained OAuth posture, key-versioned token artifacts, and
  first-class privacy, authorization, and crypto governance docs for the enterprise-to-ideal auth pass
- `acceptance`: `Capability/Access/Policy/IdentityPolicyCatalog` exists; `AccessPolicy` can express actor-tier
  assurance; OAuth clients can require phishing-resistant auth and sender-constrained tokens; HMAC JWTs support `kid`;
  README/docs/evidence reflect assurance, retention, authz, crypto, and SCIM conditional posture; PHPUnit and PHPStan
  lanes pass
- `links`: System/Capability/Access/Policy/, System/Capability/OAuth/, System/Flow/OAuth/, System/Flow/Token/,
  docs/assurance-policy.md, docs/privacy-retention-policy.md, docs/authorization-hardening.md,
  docs/crypto-key-lifecycle.md

- `id`: AUTH-021
- `created_at`: 2026-04-12 22:39 CEST
- `updated_at`: 2026-04-12 22:39 CEST
- `completed_at`: 2026-04-12 22:39 CEST
- `status`: done
- `estimate`: 2h
- `actual`: 2h 03m
- `outcome`: Normalize phishing-resistant naming, unblock `ChangeEmail` as a first-class flow, and close the new
  regression gaps around admin elevation and email change
- `acceptance`: `Capability/Access/RequirePhishingResistantAuthentication/` exists with clean naming; `BeginEmailChange`
  resolves accounts by `UserId`; admin elevation uses phishing-resistant terminology; new `ChangeEmail` and guard tests
  exist; PHPUnit and PHPStan lanes pass
- `links`: System/Capability/Access/RequirePhishingResistantAuthentication/, System/Flow/ChangeEmail/,
  System/Flow/AdminRealm/, tests/Flows/ChangeEmail/, tests/Capabilities/Access/RequirePhishingResistantAuthentication/

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
