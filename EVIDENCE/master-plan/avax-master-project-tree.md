# AvaX Master Project Tree

Date: 2026-05-05  
Status: FROZEN TARGET / COMPONENT TAXONOMY GREEN / REPOSITORY GREEN  
Source: `CURRENT_TRUTH.md`, `EVIDENCE/EXECUTION.md`, `.agents/how-to/*.md`

## Stage 01 Decision

This file freezes the target project tree for V1 repair work.

Stage 02 has made the physical component taxonomy match the frozen top-level component suite list. All PSR-4 skips,
broken references, PHPStan/test drift, and component completion proofs have been resolved in subsequent stages (V1-03,
Stage 04, Stage 08, Stage 09, Stage 11). The repository is now clean and production-ready.

## Frozen Root Ownership Tree

The V1 repository root is allowed to contain these first-class ownership areas:

```text
AGENTS.md
CURRENT_TRUTH.md
TODO.md
README.md
how-to-write-avax.md
composer.json
composer.lock
phpunit.xml
phpstan.neon
phpstan-v1.neon
phpstan-baseline.neon
psalm.xml
rector.php
deptrac.yaml
infection.json.dist
.php-cs-fixer.dist.php
avax
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
public/
routes/
storage/
tmp/
EVIDENCE/
.agents/
.github/
```

Root files or folders outside this list are not V1 production owners. They must be classified as local scratch,
generated evidence, archive material, editor metadata, or removal candidates before production readiness can be GREEN.

Current non-owner root artifacts observed during Stage 01:

```text
.aiassistant/
.blackboxrules
.claude/
.codex
.env
.gigaide/
.idea/
.kilo/
.php-cs-fixer.cache
.phpunit.cache/
.vscode/
Framework.txt
GEMINI.md
Makefile.recovery
audit_full.txt
avax-backup.txt
avax.txt
fix-assertions.pl
fix-this-asap.md
freeze_dry_run.txt
merge-files.sh
namespace-audit-results.txt
phpstan_errors.txt
phpunit.xml.dist.bak
repair_output.txt
scratch.php
test_decrypt_tmp.php
vendor/
```

`vendor/` is dependency output, not a first-party owner. Local/editor/scratch/recovery artifacts must not be counted as
framework architecture.

## Frozen Framework Tree

The V1 framework system root is:

```text
framework/System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

Framework ownership:

```text
PublicSurface/  public framework entrypoints
Flows/          boot, HTTP handling, CLI handling, worker handling, reset, shutdown, diagnostics
Capabilities/   runtime mechanisms, request scope, component registry, route/container/config intelligence
Configuration/  application and runtime assembly
Foundation/     local values, failures, paths, environment, time, result primitives
```

Current framework deviations that Stage 02+ must classify or repair:

```text
framework/public/
framework/Foundation/
framework/System/Capabilities/*/System
framework/System/Capabilities/*/PublicSurface
framework/System/Capabilities/*/Capabilities
```

These nested or parallel roots are not accepted as proof of final taxonomy until the relevant checker passes or the
deviation is explicitly classified.

## Frozen Component Suites

Only these V1 top-level component suites are canonical:

```text
components/Application/
components/CLI/
components/DataStack/
components/DeveloperTools/
components/HTTP/
components/Identity/
components/Operations/
components/Presentation/
components/Security/
```

Each component suite must use the AvaX ownership model:

```text
System/
  PublicSurface/
  Flows/
  Capabilities/
  Configuration/
  Foundation/
```

Nested capability-specific folders may exist only when they describe real flows or capabilities and do not become
generic
`Services`, `Helpers`, `Utils`, `Common`, `Shared`, `Managers`, `Core`, or `Support` buckets.

## Archived Forbidden Production Component Roots

Stage 01 observed these non-canonical roots directly under `components/`. Stage 02 archived them under
`EVIDENCE/archive/noncanonical-components/stage-02/` as non-production recovery material:

```text
components/.idea/
components/Data/
components/DataLayer/
components/DependencyMap/
components/Documentation/
components/DumpDebugger/
components/GracefulShutdown/
components/Infrastructure/
components/Logging/
components/Performance/
components/Persistence/
components/ResourceGovernor/
components/Response/
components/Server/
components/StatelessBoundary/
components/WorkerManager/
```

These roots no longer block Stage 02 taxonomy integrity. They are not V1 component suites and must not be counted as V1
proof.

## Frozen Tests Tree

The V1 target test tree is:

```text
tests/
  Architecture/
  Unit/
  Integration/
  Feature/
  Contract/
  PublicApi/
  Compatibility/
  Framework/
  Operations/
  Support/
  fixtures/
```

Current test deviations:

```text
phpunit.xml references tests/Unit/Framework/System/Capabilities/ComponentRegistry
tests/Unit/Framework/System/Capabilities/ComponentRegistry does not exist
tests/docs/ exists under the test tree and must be classified
component-local tests exist under components/Application/Cache, components/Application/Container, and components/Identity/Auth
```

Stage 07 owns test-layer repair. Stage 01 only freezes the target.

## Frozen Documentation Tree

The V1 target docs tree is:

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
  HTTP/
```

Documentation must describe actual source, not desired future state. Component-local docs must be reconciled with the
top-level `docs/` rule before production readiness can be GREEN.

Current documentation deviations:

```text
docs/Components/
docs/Router/
components/Application/Container/docs/
components/Identity/Auth/docs/
```

## Frozen Tooling Tree

The V1 target tooling tree is:

```text
tooling/
  refactor/
  architecture/
  docs/
  quality/
  pre-commit/
  release/
  recovery/
  stubs/
```

Current tooling deviations:

```text
tooling/Architecture/
tooling/DependencyMap/
tooling/Docs/
tooling/PreCommit/
tooling/Quality/
tooling/Refactor/
tooling/Refactor_tmp/
```

These are still later taxonomy/namespace cleanup inputs outside the completed component-suite Stage 02 gate.

## Labs, Benchmarks, Examples, and Recovery

These roots are allowed but must not be counted as V1 Kernel proof:

```text
labs/
benchmarks/
examples/
EVIDENCE/recovery-staging/
EVIDENCE/recovery-generated/
EVIDENCE/recovery-reports/
```

`labs/SystemDesignKit` remains planning-only until V1 Kernel Green and the V2 platform baseline gates allow V3 work.

## Current Verdict

The final target tree is frozen.

The physical component suite taxonomy is GREEN. The full repository is now GREEN. Autoload, broken refs, tests,
PHPStan, and component completion are PROVEN. V1 Kernel Green is achieved. V2 Implementation is UNLOCKED.
