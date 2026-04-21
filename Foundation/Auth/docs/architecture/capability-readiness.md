---
title: capability-readiness
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Capability Readiness

## What this document is

This page records which optional capability contracts must be present before the kernel can safely advertise a feature.

## Readiness matrix

- Core identity bootstrap: requires at least one backend, session or JWT.
- OAuth: requires JWT issuance plus refresh-token storage.
- OpenID Connect base reads: requires the OIDC provider plus OAuth readiness.
- OIDC PAR/logout/JARM: optional within OIDC and reported separately.
- Passkeys: require passkey runtime plus challenge and credential stores.
- Federation/SSO: requires the federation runtime and companion stores.
- SCIM: requires a provisionable user source and SCIM directory stores.
- Provisioning lifecycle: requires the provisioning actions to be wired.

## Configuration request matrix

| Builder input                                                                                | What it enables                                  | What it requires                            | Invalid combination                                        |
|----------------------------------------------------------------------------------------------|--------------------------------------------------|---------------------------------------------|------------------------------------------------------------|
| `enterprise()`                                                                               | durable session posture                          | `withSessionRegistry()`                     | enterprise mode without registry fails at build time       |
| `withOAuthClientRegistry()` / `withAuthorizationCodeStore()`                                 | explicit OAuth/OIDC composition                  | JWT backend + `withRefreshTokenStore()`     | OAuth-specific config without token storage or JWT backend |
| `withOidcProvider()`                                                                         | OIDC metadata, JWKS, userinfo, logout, PAR, JARM | JWT backend + `withRefreshTokenStore()`     | OIDC provider without OAuth readiness                      |
| `withOidcRequestObjectStore()`                                                               | persisted PAR/request-object validation          | `withOidcProvider()` plus OAuth readiness   | request-object store without provider                      |
| `withPasskeyRuntime()`                                                                       | passkey registration/authentication              | passkey runtime only; stores can default    | no invalid state by itself                                 |
| `withPasskeyCredentialStore()` / `withPasskeyChallengeStore()` / `withPasskeyRelyingParty()` | passkey customization                            | `withPasskeyRuntime()`                      | passkey-specific config without runtime                    |
| `withFederationRuntime()`                                                                    | SSO/federation flows                             | federation runtime only; stores can default | no invalid state by itself                                 |
| `withFederationConnectionStore()` / `withFederatedIdentityLinkStore()`                       | federation customization                         | `withFederationRuntime()`                   | federation-specific config without runtime                 |
| `withScimDirectoryStore()` / `withScimProvisionedIdentityStore()`                            | SCIM customization                               | `forUser(ProvisionableUserSourceInterface)` | SCIM-specific storage with non-provisionable user source   |

## Failure model

- Bootstrap-time requirements fail before `AuthBuilder::ready()` returns a kernel.
- Optional late-bound surfaces throw explicit capability-unavailable exceptions with the missing operation name.
- Readiness helpers such as `isConfigured()` exist on optional owners so integrations can omit unsupported features
  cleanly.
