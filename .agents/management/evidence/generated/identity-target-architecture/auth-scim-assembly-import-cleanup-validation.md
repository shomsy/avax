# Auth SCIM Assembly Import Cleanup Validation

Date: 2026-05-21

## Static Gates

### Fully-qualified `new \Avax` grep

Command:

```text
rg -n -F "new \Avax" components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php
```

Result: GREEN. No matches.

### Fully-qualified `\Avax\Components` grep

Command:

```text
rg -n -F "\Avax\Components" components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php
```

Result: GREEN. No matches.

### `git diff --check`

Result: GREEN.

## PHP Gates

Commands attempted:

```text
php -l components/Identity/Auth/System/Configuration/Assembly/AssembleAuthIdentityGraph.php
vendor/bin/phpunit tests/Unit/Components/Identity/Auth --no-coverage
vendor/bin/phpstan analyse components/Identity/Auth/System/Configuration/Assembly tests/Unit/Components/Identity/Auth --memory-limit=1G
```

Result: ENVIRONMENT_YELLOW.

Output:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```
