---
title: access-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Access How This Works

## What this folder is

`System/Capabilities/Access/` owns current-authentication reads, authorization boundaries, composed access policy
evaluation, phishing-resistant checks, admin elevation gates, and risk reads over the current actor.

## Real commands or triggers that reach this folder

- `Auth::authenticateRequest()`
- `Auth::current()`
- `Auth::check()`
- `Auth::user()`
- `Auth::access()` and authorization enforcement through `AccessInterface`
- `Auth::requireAdminElevation()`
- `Auth::assessCurrentRisk()` and `Auth::readRiskSignals()`

## Exact upstream handoffs

- `Auth` delegates access-facing methods into `Capabilities/Access/Access.php`.
- `Access` forwards authorization checks into `Facades/Authorization.php`.
- Runtime enforcement lands in boundary folders such as `RequireAuthentication/`, `RequirePermission/`,
  `RequireAccessPolicy/`, and `RiskBasedAccess/`.

## One concrete decision path

1. A caller asks `Auth::access()->requirePolicy($policy)`.
2. `Authorization` delegates to `RequireAccessPolicy`.
3. `RequireAccessPolicy` runs only the requested checks: auth, role, permission, ownership, phishing resistance, fresh
   MFA, and admin elevation.
4. The first violated rule throws the corresponding domain exception.

## Failure and refusal shape

- Missing authentication throws `Unauthenticated`.
- Role and permission failures stay separate.
- Fresh-MFA and phishing-resistant requirements remain explicit instead of collapsing into a generic deny.
