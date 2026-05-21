# Tenancy Runtime Safety Threat Analysis

Date: 2026-05-21

## Asset

Tenant context and tenant isolation decisions.

## Threats

- Stale tenant context leaking between requests in long-lived workers.
- Static tenant state being reused across tests, requests, workers, or fibers.
- Fail-open tenant access when no tenant is present.

## Mitigation

- `TenancyRuntime` owns context through `TenantContextInterface`.
- `TenancyServiceProvider` registers tenant context as scoped.
- Root Identity assembly creates an explicit `DefaultTenantContext`.
- `requireTenant()` fails closed with `TenantNotFoundException` and safe message `Tenant context is required.`
- Unit characterization now proves two `Tenancy` runtimes do not share tenant context.

## Negative Proof

`TenancyCharacterizationTest::requireTenantFailsClosedWhenNoTenantIsSet()` documents the negative behavior: no tenant context raises `TenantNotFoundException`.

Runtime execution is ENVIRONMENT_YELLOW until PHP/PHPUnit can run outside the Docker socket permission issue.
