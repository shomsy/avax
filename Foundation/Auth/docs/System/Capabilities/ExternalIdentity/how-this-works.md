---
title: external-identity-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# External Identity How This Works

## What this folder is

`System/Capabilities/ExternalIdentity/` owns OAuth, OpenID Connect, and federation/SSO behavior that the kernel ships
directly.

## Real commands or triggers that reach this folder

- OAuth client registration and token flows through `Auth::*OAuth*`
- OIDC metadata, JWKS, userinfo, PAR, logout, and JARM reads/builds
- Federation connection registration, discovery, and federated login completion

## Exact upstream handoffs

- `Auth` delegates into `ExternalIdentity`.
- `ExternalIdentity` forwards into `OAuth`, `OpenIDConnect`, and `SingleSignOn`.
- Each owner delegates to its runtime flow classes.

## Optional capability semantics

- `OAuth::isConfigured()` reports whether the full shipped OAuth surface is wired.
- `OpenIDConnect::isConfigured()` covers base provider metadata/JWKS/userinfo readiness.
- `OpenIDConnect` also reports optional PAR/logout/JARM support individually.
- `SingleSignOn::isConfigured()` reports federation readiness.
- Unsupported operations now throw `ExternalIdentityCapabilityUnavailable` with the exact missing operation name.

## Where to debug first

- OAuth token exchange issues: `OAuth/Runtime/`
- OIDC provider/JWKS behavior: `OpenIDConnect/Runtime/`
- Federation discovery/login drift: `SingleSignOn/FederationRuntime/`
