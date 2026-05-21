# Tenancy Assembly Naming Cleanup Validation

Date: 2026-05-21

## Static Gates

### `TenancyGraph` active-code grep

Command:

```text
rg -n -F "TenancyGraph" components/Identity tests/Unit/Components/Identity -g '*.php'
```

Result: GREEN. No active PHP matches.

### `git diff --check`

Result: GREEN.

## PHP Gates

Commands attempted:

```text
php -l components/Identity/Tenancy/System/Configuration/Assembly/Tenancy.php
vendor/bin/phpunit tests/Unit/Components/Identity/Tenancy --no-coverage
```

Result: ENVIRONMENT_YELLOW.

Output:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```
