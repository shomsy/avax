# Root Identity Runtime Import Cleanup Validation

Date: 2026-05-21

## Static Gates

### Fully-qualified `new \Avax` grep

Command:

```text
rg -n -F "new \Avax" components/Identity/System/Configuration/Builders/IdentityRuntime.php
```

Result: GREEN. No matches.

### Fully-qualified `\Avax\Components` grep

Command:

```text
rg -n -F "\Avax\Components" components/Identity/System/Configuration/Builders/IdentityRuntime.php
```

Result: GREEN. No matches.

### `git diff --check`

Result: GREEN.

## PHP Gate

Command attempted:

```text
php -l components/Identity/System/Configuration/Builders/IdentityRuntime.php
```

Result: ENVIRONMENT_YELLOW.

Output:

```text
permission denied while trying to connect to the docker API at unix:///var/run/docker.sock
```
