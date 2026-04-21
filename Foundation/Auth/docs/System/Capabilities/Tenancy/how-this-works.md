---
title: tenancy-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Tenancy How This Works

## What this folder is

`System/Capabilities/Tenancy/` owns tenant lifecycle, membership, invites, ownership transfer, tenant security change
management, and admin elevation rules.

## Real commands or triggers that reach this folder

- `Auth::createTenant()`
- `Auth::readTenants()`
- `Auth::inviteTenantMember()`
- `Auth::acceptTenantInvite()`
- `Auth::readTenantMembers()`
- `Auth::beginTenantSecurityChange()` and related apply/rollback calls
- `Auth::beginAdminElevation()` and `Auth::requireAdminElevation()`

## Exact upstream handoffs

- `Auth` delegates into `Tenancy`.
- `Tenancy` splits into `Tenants` and `Security`.
- Admin elevation runtime stays in the same capability family because it is a tenant/admin assurance rule, not a
  transport concern.

## Where to debug first

- Membership or invite logic: `Runtime/Tenant/`
- Security change workflow: `Runtime/TenantSecurity/`
- Elevation refusal: `AdminRealmRuntime/`
