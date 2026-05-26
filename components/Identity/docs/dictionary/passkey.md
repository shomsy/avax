# Passkey

## What It Is

A passkey is a FIDO2/WebAuthn-based credential that uses public-key cryptography for passwordless authentication. The private key is stored on the user's device (phone, laptop, hardware token), and the public key is registered with the server.

Passkeys replace passwords by providing stronger, phishing-resistant authentication through cryptographic challenge-response.

## What It Is NOT

- A passkey is NOT a password. Passkeys use asymmetric cryptography and cannot be phished or brute-forced like passwords.
- A passkey is NOT a hardware token. Passkeys may be stored on hardware, but they are also supported by phones, laptops, and platform authenticators.
- A passkey is NOT OAuth. OAuth delegates authorization. Passkeys authenticate the user directly to the relying party.
- A passkey is NOT MFA by itself. A passkey is a single factor ("something you have" combined with device-level "something you know/are"). It is strong but may be combined with other factors for higher assurance.

## Common Confusion

People often confuse passkeys with generic "passwordless" solutions. Magic links and SMS codes are passwordless but are not passkeys. Passkeys specifically use FIDO2/WebAuthn cryptographic authentication.

Another confusion is assuming passkeys eliminate all phishing. Passkeys are phishing-resistant because they are bound to the origin, but implementation errors (incorrect origin validation, relay attacks) can weaken this guarantee.

## In AvaX

AvaX treats passkeys as:

- A credential type within the authentication capability
- Bound to specific origins and relying party identifiers
- Managed through a passkey registration and verification flow
- Observable: passkey registration and verification produce security events
- Subject to the same identity production as any credential type

Passkey support in AvaX integrates with the WebAuthn protocol through a dedicated capability that handles the cryptographic challenge-response while the authentication flow manages the overall login experience.
