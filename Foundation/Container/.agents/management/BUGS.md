# BUGS

Canonical active defect and regression queue.

## Rules

- keep newest items first
- describe user-visible failure first
- include expected fixed behavior
- capture severity and risk
- use timestamp and estimate fields from `TIMELINE.md`
- keep `ACTIVE.md` in sync for non-closed items

## Entry Format

- `id`:
- `detected_at`:
- `updated_at`:
- `status`: open | in_progress | blocked | fixed | closed
- `severity`: low | medium | high | critical
- `estimate`:
- `actual`:
- `symptom`:
- `expected_behavior`:
- `risk`:
- `links`:

## Current Items

- `id`: `BUG-002`
  - `detected_at`: `2026-04-07 04:27 CEST`
  - `updated_at`: `2026-04-07 04:40 CEST`
  - `status`: `fixed`
  - `severity`: `high`
  - `estimate`: `4h`
  - `actual`: `1h`
  - `symptom`: Public `ContainerInterface` and concrete `Container` expose `DependencyInjection/*` types directly, making the system-root namespace part of the stable library API.
  - `expected_behavior`: The public surface should remain rooted in `Avax\Container\...`, or any namespace break must ship with an explicit compatibility path and release note.
  - `risk`: Hard BC break for consumers typed against the previous surface; it also locks internal runtime vocabulary into the public contract.
  - `links`: [ContainerInterface.php](/home/shomsy/projects/components/Foundation/Container/ContainerInterface.php), [Container.php](/home/shomsy/projects/components/Foundation/Container/Container.php), [docs/Container.md](/home/shomsy/projects/components/Foundation/Container/docs/Container.md)

- `id`: `BUG-001`
  - `detected_at`: `2026-04-07 04:27 CEST`
  - `updated_at`: `2026-04-07 04:40 CEST`
  - `status`: `fixed`
  - `severity`: `medium`
  - `estimate`: `2h`
  - `actual`: `45m`
  - `symptom`: `ServiceProviderInterface` hard-codes the concrete `Container` in its constructor, so external providers are forced onto the implementation type instead of a public contract.
  - `expected_behavior`: Provider lifecycle ports should depend on `ContainerInterface` or a narrower provider-facing contract, not on the concrete facade.
  - `risk`: SPI lock-in, weaker substitution in tests and integrations, and a stronger temptation to depend on concrete-only runtime helpers.
  - `links`: [ServiceProviderInterface.php](/home/shomsy/projects/components/Foundation/Container/DependencyInjection/Capabilities/Providers/Contracts/ServiceProviderInterface.php), [AppFactory.php](/home/shomsy/projects/components/Foundation/Container/DependencyInjection/Configuration/AppFactory.php), [Container.php](/home/shomsy/projects/components/Foundation/Container/Container.php)
