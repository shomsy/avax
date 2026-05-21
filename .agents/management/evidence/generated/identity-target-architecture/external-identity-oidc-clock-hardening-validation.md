# External Identity OIDC Clock Hardening Validation

Date: 2026-05-21

## Static Validation

```text
git diff --check
```

Result: PASS.

```text
rg -n "\btime\s*\(" \
  components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol \
  components/Identity/ExternalIdentity/System/Foundation/Time \
  tests/Unit/Components/Identity/ExternalIdentity/OidcClockCharacterizationTest.php \
  components/Identity/Auth/System/Configuration/Providers/RegisterAuthDefaults.php
```

Result: PASS, no direct `time()` call remains in the touched OIDC slice.

```text
rg -n "new DateTimeImmutable\(" \
  components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol \
  components/Identity/ExternalIdentity/System/Foundation/Time \
  components/Identity/Auth/System/Configuration/Providers/RegisterAuthDefaults.php
```

Result: PASS_WITH_EXPECTED_MATCH. The only production match is `components/Identity/ExternalIdentity/System/Foundation/Time/SystemClock.php`.

```text
rg -n "new InMemoryOidcRequestObjectStore\(|new OpenSslOidcProvider\(" components/Identity tests -g "*.php"
```

Result: PASS. Store production assembly passes a clock; provider construction appears only in focused test source and passes a clock.

```text
rg -n "class\s+|interface\s+|trait\s+|enum\s+" \
  components/Identity/ExternalIdentity/System/Foundation/Time/Clock.php \
  components/Identity/ExternalIdentity/System/Foundation/Time/SystemClock.php \
  components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/InMemoryOidcRequestObjectStore.php \
  components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php \
  components/Identity/Auth/System/Configuration/Providers/RegisterAuthDefaults.php
```

Result: PASS. Touched production files contain one class/interface each.

## Blocked Runtime Validation

```text
php -l components/Identity/ExternalIdentity/System/Capabilities/OpenIDConnect/Protocol/OpenSslOidcProvider.php
php -l tests/Unit/Components/Identity/ExternalIdentity/OidcClockCharacterizationTest.php
composer validate --no-check-publish
```

Result: ENVIRONMENT_YELLOW.

Output:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```

## Environment Yellow

PHP/composer/PHPUnit remain blocked by Docker socket permission denied.

Focused static validation only. Full GREEN is not claimed.
