# Container Docs

This component ships one container story:

- public facade: [`Container.md`](./Container.md)
- structural map: [`architecture.md`](./architecture.md)
- concepts: [`concepts/index.md`](./concepts/index.md)
- reference terms: [`glossary.md`](./glossary.md)
- failure handling: [`troubleshooting.md`](./troubleshooting.md)

The system root is `DependencyInjection/`.

Root files under `DependencyInjection/` are public flow entries:

- `CreateContainer.php`
- `RegisterServices.php`
- `ResolveService.php`
- `CallFunction.php`
- `OpenScope.php`
- `CloseScope.php`
- `BootProviders.php`

Subfolders under `DependencyInjection/` are internal work areas:

- `Registrations/`
- `Resolution/`
- `Calls/`
- `Injection/`
- `Scopes/`
- `Providers/`
- `Configuration/`
- `Observability/`
- `Policies/`
- `Errors/`
- `Foundation/`

Local validation and smoke checks:

- Docker PHP lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- Docker smoke suite: [`tests/run-smoke-tests.sh`](../tests/run-smoke-tests.sh)
