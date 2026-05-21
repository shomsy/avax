# Auth Builder Detox Validation

Date: 2026-05-21

## Static Validation

```text
git diff --check
```

Result: PASS.

```text
rg -n "DefaultAuth::configuration" components tests framework -g "*.php"
```

Result: PASS, no call sites remain.

```text
rg -n "ContainerInterface" \
  components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php \
  components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php
```

Result: PASS, no matches.

```text
rg -n "^\s*public static function configuration" \
  components/Identity/Auth/System/Capabilities/Authentication/DefaultAuth.php
```

Result: PASS, no static container configuration helper remains.

```text
rg -n "function with(OAuthClientRegistry|AuthorizationCodeStore|OidcRequestObjectStore|FederationConnectionStore|FederatedIdentityLinkStore|LifecycleStore|ScimDirectoryStore|ScimProvisionedIdentityStore|AdminElevationStore|TenantStore|TenantSecurityConfigurationStore|TenantSecurityChangeRequestStore|RiskEngine|PasswordResetThrottle|MfaRecoveryThrottle|ScimThrottle|Clock|EmailVerificationState|EmailVerificationStore|EmailChangeStore|PasswordResetStore|MfaStore|MfaChallengeStore|MfaAttemptLimit|PasskeyCredentialStore|PasskeyChallengeStore)|function using(IdGenerator|Hasher|Totp)" \
  components/Identity/Auth/System/Configuration/Builders/AuthBuilder.php
```

Result: PASS. Named arguments used in `RegisterAuthDependencies::authBuilderFromContainer()` match current `AuthBuilder` parameter names.

## Environment-Yellow

PHP/composer/PHPUnit execution remains classified as ENVIRONMENT_YELLOW because this workspace's PHP tooling attempts to reach the Docker socket and fails with permission denied. Full runtime validation is not claimed.

## Final Validation Status

Focused static validation only.
No full GREEN claim.
