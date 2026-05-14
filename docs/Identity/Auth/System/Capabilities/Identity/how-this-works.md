---
title: identity-how-this-works
owner: auth-kernel
last_reviewed: 2026-04-21
classification: internal
---

# Identity How This Works

## What this folder is

`System/Capabilities/Identity/` owns login/logout issuance, account changes, recovery, verification, MFA, passkeys,
session handling, token issuance, and the internal user model.

## Real commands or triggers that reach this folder

- `Auth::login()` and `Auth::logout()`
- Session and JWT issuance during login, passkey completion, federation completion, and MFA verification
- Account flows such as password change, registration, email change, reset, and verification
- MFA and passkey public methods on `Auth`

## Exact upstream handoffs

- `Auth` delegates into `Identity`.
- `Identity` owns cross-cutting issuance and clearing across session and JWT backends.
- Sub-capability owners in `IdentityOwners/`, `Mfa/`, `Passkey/`, and `Sessions/` execute the domain-specific flows.

## Why `Identity` keeps direct backend handles

The constructor still accepts session/JWT backends because issuance, logout clearing, and mode resolution are
cross-cutting identity operations, not local account or MFA concerns. That keeps the public kernel story stable while
the owner subtrees handle the use-case-specific work.

## Failure and refusal shape

- Missing sub-capability coordinators throw `IdentityCapabilityUnavailable`.
- Passkey runtime gaps throw `PasskeyOperationFailed::runtimeNotConfigured()`.
- Invalid users or missing backend contracts fail before auth state is issued.
