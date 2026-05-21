# External Identity Assembly Naming Cleanup Validation

Date: 2026-05-21

## Static Gates

### Exact old class grep

Command:

```text
rg -n "\bExternalIdentityGraph\b" components/Identity tests/Unit/Components/Identity -g '*.php'
```

Result: GREEN for the renamed component assembly class. Remaining matches are
`AssembleAuthExternalIdentityGraph`, a separate Auth assembly class intentionally out of
scope for this slice.

### `git diff --check`

Result: GREEN.

## PHP Gates

Commands attempted:

```text
php -l components/Identity/ExternalIdentity/System/Configuration/Assembly/ExternalIdentity.php
vendor/bin/phpunit tests/Unit/Components/Identity/ExternalIdentity --no-coverage
```

Result: ENVIRONMENT_YELLOW.

Output:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```
