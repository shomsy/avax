# AvaX Performance Budget and Benchmark Policy

**Date:** 2026-05-07
**Stage:** Stage 14 — Benchmark and Performance Budget Suite
**Status:** ACTIVE

---

## 0. Purpose

This document outlines the performance constraints, latency budgets, and benchmarking rules for the AvaX framework. High
performance is a core design pillar; therefore, regressions must be caught mechanically through benchmarking rather than
subjective observation.

---

## 1. The Performance Budget

AvaX enforces a strict performance budget for its baseline framework boot and basic HTTP routing (The Golden Path).

### Core Baseline Budgets (Golden Path)

- **Boot Time:** < 5ms (Production Mode with all caching enabled)
- **Memory Consumption:** < 2MB (Baseline Boot)
- **Routing Latency:** < 1ms (1000 registered routes, finding 1 route)

### Component Specific Budgets

- **Dependency Injection Resolution:** < 0.1ms per deep object graph.
- **Event Dispatching:** < 0.5ms overhead for 100 listeners.

---

## 2. Benchmarking Strategy

Benchmarking in AvaX is a continuous process, not a one-off task.

### Allowed Benchmarking Tools

1. **PHPBench:** Used for micro-benchmarking specific capabilities (e.g., DI container resolution, route matching).
2. **FrankenPHP / RoadRunner metrics:** Used for macro-benchmarking full HTTP throughput (Requests Per Second).

### Benchmark Suite Location

All benchmarks must reside in the `benchmarks/` directory at the root of the repository.

```text
benchmarks/
  Micro/
    Container/
    Router/
    EventDispatcher/
  Macro/
    GoldenPath/
```

---

## 3. Dealing with Performance Regressions

If a PR or new feature causes a benchmark to fail the performance budget:

1. **The PR is automatically rejected.**
2. If the feature is essential and inherently expensive, the performance budget must be formally updated and justified
   via an Architecture Decision Record (ADR).
3. We do not accept "convenience" features that globally degrade the framework's baseline performance. Features must be
   strictly "pay-for-what-you-use".

---

## 4. Enforcement

- Benchmark pipelines run on every major architectural change.
- `tooling/performance/check-performance-naming.php` ensures performance constraints and naming conventions are adhered
  to.
- Memory leaks are detected via Long-Lived Worker tests (e.g., RoadRunner integration tests).
