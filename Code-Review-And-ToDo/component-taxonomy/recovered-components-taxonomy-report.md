# Recovered Components Taxonomy Report

- Date: 2026-05-01 23:51:46
- Mode: DRY-RUN
- Status: OK

## Operations

- MERGE `components/Security` -> `components/Security/System` | Security is now a top-level suite
- REWRITE `components/Identity/Auth/examples/session-login.php`
- REWRITE `components/Identity/Auth/tests/System/AuthFoundationSmokeTest.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/ProvisionUser/ProvisionScimUser.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/RegisterDirectory/RegisterScimDirectory.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Runtime/RotateToken/RotateScimToken.php`
- REWRITE `components/Identity/Auth/System/Capabilities/IdentitySync/SCIM/Directories/InMemoryScimDirectoryStore.php`
- REWRITE `components/Identity/Auth/System/Flows/Register/HashRegisteredPassword.php`
- REWRITE `components/Identity/Auth/System/Flows/ChangePassword/ChangePassword.php`
- REWRITE `components/Identity/Auth/System/Flows/RecoverAccess/PasswordReset/ResetPassword.php`
- REWRITE `components/Identity/Auth/System/Flows/ChangeEmail/BeginEmailChange.php`
- REWRITE `components/Identity/Auth/System/Flows/Login/VerifyPassword.php`
- REWRITE `components/Identity/Auth/System/Configuration/AuthBuilder.php`
- REWRITE `components/Identity/Auth/System/Configuration/RegisterAuthDependencies.php`
- REWRITE `components/Identity/ExternalIdentity/System/Capabilities/SingleSignOn/FederationRuntime/CompleteFederatedLogin/CompleteFederatedLogin.php`
- REWRITE `components/Identity/ExternalIdentity/System/Capabilities/OAuth/Elements/InMemoryOAuthClientRegistry.php`
- REWRITE `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/GenerateBackupCodes.php`
- REWRITE `components/Identity/Credentials/System/Capabilities/Mfa/Runtime/Backup/VerifyBackupCode.php`
- REWRITE `components/Security/Hashing/System/Capabilities/PasswordHashing/PasswordHasher.php`
- REWRITE `components/Security/System/Capabilities/Audit/SecurityAuditLog.php`
- REWRITE `components/Security/System/Capabilities/MassAssignment/MassAssignmentGuard.php`
- REWRITE `components/Security/System/Capabilities/Escape/OutputEscaper.php`
- REWRITE `components/Security/System/PublicSurface/Security.php`
- REWRITE `components/Security/Secrets/System/Capabilities/Stores/SecretStore.php`
- REWRITE `components/Security/Secrets/System/Capabilities/Stores/InMemorySecretStore.php`
- REWRITE `components/Security/Secrets/System/Capabilities/Stores/EncryptedSecretStore.php`
- REWRITE `components/Security/Secrets/System/Capabilities/Encryption/SecretEncrypter.php`
- REWRITE `components/Security/Secrets/System/Flows/RedactSecret/RedactSecret.php`
- REWRITE `components/Security/Secrets/System/Flows/ReadSecret/ReadSecret.php`
- REWRITE `components/Security/Secrets/System/PublicSurface/Secrets.php`
- REWRITE `components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlGenerator.php`
- REWRITE `components/HTTP/Security/System/Capabilities/SignedUrls/SignedUrlVerifier.php`
- REWRITE `components/HTTP/Security/System/Capabilities/Csrf/CsrfToken.php`
- REWRITE `components/HTTP/Security/System/Capabilities/Csrf/CsrfVerifier.php`
- REWRITE `components/HTTP/Security/System/Capabilities/Headers/SecurityHeaders.php`

## Conflicts

- none

## Notes

- Tests were not modified.
- Security is now a real suite in check-component-suite-structure.php.
- Runtime lifecycle moved to framework/System/Capabilities/.
- Run composer dump-autoload after apply.
- Run architecture checkers after apply.
