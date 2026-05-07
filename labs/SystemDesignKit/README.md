# SystemDesignKit (labs/)

V3 Executable System Design Framework

## Status

**Experimental** — V3-00 foundation complete.
**V2 Platform Baseline:** GREEN (all 72 components complete).
**Promotion:** Not yet promoted to `components/SystemDesign/`.

## Purpose

Model, validate, simulate, test, and explain large application architectures.

V3 does not just build applications.
V3 tests whether the architecture makes sense.

## V3-00 Foundation

- `System/PublicSurface/SystemDesignKit.php` — experimental public surface
- `System/Capabilities/Capacity/CapacityModel.php` — capacity model (traffic, storage, cache, queue, latency,
  availability)
- `Capacity/CapacityYamlParser.php` — capacity.yaml parser spike

## MVP Scope

- Capacity modeling (traffic, storage, cache, queue, latency)
- Architecture validation
- Reference architectures
- Failure simulation
- Architecture tests
- Scenario runner

## Tree

```text
labs/SystemDesignKit/
  System/
    PublicSurface/
      SystemDesignKit.php
    Capabilities/
      Capacity/
        CapacityModel.php
    Flows/
    Configuration/
    Foundation/
  Capacity/
    CapacityYamlParser.php
  experiments/
  scenarios/
  spikes/
```

See: `EVIDENCE/avax-v3-executable-system-design-framework-plan.md`
