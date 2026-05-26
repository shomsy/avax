# Credential

## What It Is

A credential is a piece of evidence presented by an entity to prove its identity. Credentials include passwords, cryptographic keys, biometric data, security tokens, passkeys, and other authentication factors.

Credentials are the input to the authentication process, not the output.

## What It Is NOT

- A credential is NOT identity. A credential proves identity when verified, but is not identity itself.
- A credential is NOT a token. Credentials are presented to obtain tokens. Tokens carry verified identity after credential verification.
- A credential is NOT a secret in the general sense. Credentials are specific to authentication. Secrets is a broader category including API keys, encryption keys, and configuration secrets.
- A credential is NOT a session. Credentials initiate sessions; they are not session state.

## Common Confusion

The most dangerous confusion is treating credentials as general secrets. Credentials have specific lifecycle requirements: secure storage, verification, rotation, and breach detection. Mixing credential storage with general secret management leads to inadequate security controls.

Another confusion is equating "strong password" with "strong authentication." A credential is only one factor. Strong authentication may require multiple credential types (MFA).

## In AvaX

AvaX treats credentials as:

- Inputs to the authentication capability
- Typed objects with specific verification rules per type
- Stored through a credential store capability with appropriate security controls
- Subject to rotation, expiry, and breach detection policies
- Never logged, echoed, or exposed in error messages
- Validated before storage (strength, format, breach checks where applicable)

Credential verification is a distinct capability. Each credential type (password, passkey, OAuth token, etc.) has its own verification logic but produces the same verified identity output.
