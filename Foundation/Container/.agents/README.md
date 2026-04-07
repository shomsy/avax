# `.agents/` Workspace — Foundation/Container

This folder is the visible project-local workspace for the `Foundation/Container`
component.

The reusable ruleset is mounted in `.agents/.rules/`. Everything else in this
folder is project-specific state, guidance, or runtime memory.

## This Repository

- `Container.php` is the public facade.
- `DependencyInjection/` is the system root.
- `DependencyInjection/Flows/` holds public system flows.
- `DependencyInjection/Capabilities/` holds shared runtime capabilities.
- `DependencyInjection/Configuration/` holds assembly and wiring.
- `DependencyInjection/Foundation/` is reserved for tiny neutral primitives.
- `docs/` is the canonical documentation tree for this component.

## Use This Folder For

- business meaning and domain notes about dependency resolution
- local queueing, decisions, review notes, and evidence
- session memory and harness-specific runtime artifacts
- repo-local hooks or adapters that should not live in the reusable ruleset

Keep reusable policy in `.agents/.rules/`.
Keep project-specific truth here.
