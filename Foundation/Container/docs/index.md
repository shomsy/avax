# Container Docs

This component has one canonical story:

- public facade: [`Container.md`](./Container.md)
- architecture map: [`architecture.md`](./architecture.md)
- concepts: [`concepts/index.md`](./concepts/index.md)
- glossary: [`glossary.md`](./glossary.md)
- failure handling: [`troubleshooting.md`](./troubleshooting.md)

Canonical source tree:

- `Container.php`
- `ContainerInterface.php`
- `DependencyInjection/Flows/`
- `DependencyInjection/Dependencies/`
- `DependencyInjection/Injection/`
- `DependencyInjection/Scopes/`
- `Configuration/`
- `Observability/`
- `Errors/`
- `Foundation/`
- `docs/`

Local validation:

- lint: `docker run --rm -v "$PWD:/app" -w /app php:8.3-cli sh -lc "find . -name '*.php' -not -path './.agents/*' -print0 | xargs -0 -n1 php -l"`
- smoke suite: [`tests/run-smoke-tests.sh`](../tests/run-smoke-tests.sh)
