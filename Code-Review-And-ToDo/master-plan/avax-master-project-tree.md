# AvaX Master Project Tree

Date: 2026-05-03  
Status: PARTIAL / Stage 02 taxonomy resolved, Stage 06+ still RED  
Source: `Code-Review-And-ToDo/avax-master-development-plan-v1.md`

## Canonical Root Tree

The V1 plan allows these root ownership areas:

```text
AGENTS.md
CURRENT_TRUTH.md
README.md
composer.json
composer.lock
phpunit.xml
phpstan.neon
psalm.xml
rector.php
bin/
framework/
components/
tests/
docs/
examples/
reference-architectures/
labs/
benchmarks/
tooling/
build/
Code-Review-And-ToDo/
```

## Canonical Framework Tree

```text
framework/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

Framework code owns boot, request handling, console handling, worker lifecycle, request scope, reset safety, runtime
adapters, diagnostics, and shutdown.

## Canonical Component Suites

V1 component suites are:

```text
components/Application/
components/HTTP/
components/CLI/
components/DataStack/
components/Identity/
components/Security/
components/Operations/
components/Presentation/
components/DeveloperTools/
```

## Current Non-Canonical Production Roots

These roots have been MOVED out of `components/` (2026-05-03):

```text
[RESOLVED] components/API/             -> labs/API (V2 draft)
[RESOLVED] components/Integration/    -> labs/Integration (V2 locked)
[RESOLVED] components/DependencyMap/ -> tooling/dependency-map
[RESOLVED] components/Documentation/ -> docs/components/System
[RESOLVED] components/Performance/   -> benchmarks/performance
[RESOLVED] components/Server/         -> framework/System/Runtime/Adapters
[RESOLVED] components/.idea/          -> REMOVED (exists at root)
[N/A]      components/DataLayer/     -> did not exist in physical tree
```

## Current Components Tree (2026-05-03)

```text
components/
  Application/
  CLI/
  DataStack/
  DeveloperTools/
  HTTP/
  Identity/
  Operations/
  Presentation/
  Security/
```

This matches the canonical V1 component suites defined above.

## Canonical Test Tree

```text
tests/
  Architecture/
  Unit/
  Integration/
  Feature/
  PublicApi/
  Compatibility/
  Support/
```

Current test status is RED because `vendor/bin/phpunit tests --no-coverage` fails during suite loading.

## Canonical Documentation Tree

```text
docs/
  architecture/
  decisions/
  framework/
  components/
  examples/
  governance/
  security/
  system-design/
```

Documentation must describe actual source, not desired future state.

## Canonical Tooling Tree

```text
tooling/
  refactor/
  quality/
  architecture/
  pre-commit/
  release/
```

## Stage 02 Verdict

Stage 02 (Taxonomy) is RESOLVED as of 2026-05-03. Non-canonical production roots have been moved out of `components/`:

- API, Integration -> labs/
- DependencyMap -> tooling/
- Documentation -> docs/
- Performance -> benchmarks/
- Server -> framework/System/Runtime/Adapters
- .idea -> removed

Stage 06 (Autoload) remains RED - PSR-4 namespace drift exists in moved files and throughout codebase.
