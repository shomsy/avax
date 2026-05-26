# MFA (Multi-Factor Authentication)

## What It Is

Multi-Factor Authentication (MFA) is an authentication approach that requires two or more independent factors to verify identity. Factors fall into categories:

- Something you know (password, PIN)
- Something you have (phone, hardware token, passkey)
- Something you are (biometric: fingerprint, face recognition)

MFA increases confidence in identity by requiring compromise of multiple independent factors.

## What It Is NOT

- MFA is NOT two-factor authentication specifically. MFA requires two or more factors; 2FA is a subset of MFA.
- MFA is NOT a single factor with multiple steps. Entering a password and then answering a security question is still single-factor (both are "something you know").
- MFA is NOT session management. MFA strengthens the authentication step; session management handles post-authentication state.
- MFA is NOT a replacement for strong primary credentials. MFA adds defense in depth but does not excuse weak primary authentication.

## Common Confusion

A common confusion is treating any additional step as an additional factor. MFA requires factors from different categories. A password plus a password hint is not MFA.

Another confusion is assuming MFA is only for login. MFA may be required for sensitive operations (password change, privileged access, financial transactions) within an already-authenticated session.

## In AvaX

AvaX treats MFA as:

- A capability that coordinates multiple factor verification strategies
- Integrated into the login flow as a conditional step
- Triggered by policy (user setting, risk assessment, sensitive operation)
- Observable: MFA challenges and failures produce security events
- Failing closed: if MFA is required and cannot be completed, access is denied
- Independent of the primary authentication mechanism

MFA in AvaX is not a separate authentication path. It is an extension of the authentication flow that inserts additional factor verification steps based on policy.
