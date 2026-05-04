# AvaX Canonical Class Map

Date: 2026-05-03  
Status: PARTIAL / RED  
Machine-readable map: `build/canonical-class-map.json`

## Evidence

`build/canonical-class-map.json` exists and starts with framework classes, but Stage 05 is not GREEN because:

```text
[ ] composer dump-autoload -o still reports skipped production classes.
[ ] composer dump-autoload -o still reports skipped test classes.
[ ] class map entries include lane: unknown.
[ ] component completion status is not synchronized with this class map.
[ ] required markdown evidence was previously missing.
```

## Required Fields

Each class-map entry must include:

```text
FQCN
file path
suite
component
lane
status
```

Allowed statuses:

```text
canonical
public-api
internal
experimental
deprecated-bridge
test-fixture
dead
```

## Verdict

Stage 05 is PARTIAL. It can become GREEN only after autoload and component completion evidence agree with the map.
