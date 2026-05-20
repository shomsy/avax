# Runtime Performance and Cache Skill — Evidence Summary

Date: 2026-05-20
Executor: Qoder
Branch: main
Type: governance/skill authoring

## Files Created

1. `.agents/skills/avax-runtime-performance-cache/SKILL.md`
   - Runtime performance, caching discipline, hot-path safety, long-lived worker safety, benchmark proof skill
   - Covers: runtime execution, boot lifecycle, container/DI, metadata compilation, routing, event dispatching, database/query compilation, filesystem access, HTTP/request handling, cache usage, config loading, reflection/class discovery, worker/server runtime, async/concurrent execution, preload/warmup, benchmark-sensitive code
   - Performance-First Questions: 16 pre-implementation questions about hot path, lifecycle phase, cost, precomputation, caching justification, invalidation, scope, concurrency, failure mode, proof
   - Runtime Hot-Path Forbidden Patterns: 19 patterns forbidden in hot paths unless explicitly justified (reflection, filesystem scan, glob, config parsing, env access, dynamic class discovery, service locator, container compilation, regex-heavy parsing, repeated metadata/route/attribute/closure work, unbounded array growth, per-request static mutable cache, hidden singleton, broad catch-and-ignore)
   - Cache Design Gate: 17 required fields (owner, purpose, key shape, namespace, scope, lifecycle, invalidation trigger, stale-data risk, memory growth bound, concurrency behavior, serialization format, observability, fallback, failure behavior)
   - Long-Lived Worker Safety Gate: 10 checks for Swoole/RoadRunner/FrankenPHP/worker runtimes; any worker-state leak is BLOCKER
   - Compilation and Warmup Preference: 6 preferred patterns over runtime work; requires same behavior with/without compiled mode, explicit fallback, invalidation on change, tests for both paths
   - Benchmark/Proof Requirement: 6 acceptable proof types; requires performance-proof.md with baseline, changed behavior, command, result, interpretation, limitations
   - Component Dogfooding Integration: 8 AvaX capabilities to check before adding local caching/performance logic
   - Evidence Requirements: performance-design.md, cache-design.md (if caching), runtime-safety-proof.md, performance-proof.md, validation-output.md, governance-review.md; cache-not-used.md if caching rejected
   - Classification: 12 categories; 4 block commit (WORKER_UNSAFE_BLOCKER, PERFORMANCE_REGRESSION_BLOCKER, CACHE_INVALIDATION_MISSING_BLOCKER, MEMORY_GROWTH_UNBOUNDED_BLOCKER)

## Files Updated

1. `.agents/skills/index.md`
   - Added routing entry for performance, cache, hot path, runtime, long-lived worker, compilation, warmup, benchmark, latency, throughput, memory, Swoole, RoadRunner, FrankenPHP, AvaX JIT, compiled runtime plans
   - Added entry in Skill Files table

2. `.agents/skills/avax-enterprise-codecraft/SKILL.md`
   - Added Runtime Performance and Cache Gate section
   - Added `avax-runtime-performance-cache` to required skill loading list
   - HLD/LLD must classify hot path impact
   - Caching requires cache-design.md

3. `.agents/skills/avax-autonomous-backlog-loop/SKILL.md`
   - Updated Per-Slice Flow step 2 to load `avax-runtime-performance-cache` when runtime/cache/performance is relevant
   - Updated step 4 to preserve performance/cache evidence per slice

4. `.agents/skills/avax-component-dogfooding/SKILL.md`
   - Added `avax-runtime-performance-cache` to Integration with Other Skills when caching or performance primitives are involved

5. `.agents/skills/avax-enterprise-remediation/SKILL.md`
   - Updated Bootloader Rule to route runtime/cache/performance tasks to `avax-runtime-performance-cache`
   - Added mandate: bootloader alone is insufficient for performance-sensitive work

6. `AGENTS.md`
   - Added section 24.4: Runtime Performance and Cache Discipline Rule
   - Forbids reflection, filesystem scanning, config parsing, env reads, dynamic discovery, runtime object graph assembly in hot paths unless justified
   - Requires cache owner, scope, key, invalidation, lifecycle, memory bound, worker-safety, proof
   - Declares long-lived worker state leaks as blocking findings
   - Requires performance/cache evidence and validation for performance-sensitive code

## New Routing Rules

- Skills index routes: performance, cache, hot path, runtime, long-lived worker, compilation, warmup, benchmark, latency, throughput, memory, Swoole, RoadRunner, FrankenPHP, AvaX JIT, compiled runtime plans to `avax-runtime-performance-cache`
- Codecraft skill requires runtime performance/cache gate for all production code
- Autonomous loop loads performance skill per slice when relevant
- Remediation bootloader routes runtime/cache/performance tasks to performance skill; bootloader alone insufficient
- Component dogfooding loads performance skill when caching/performance primitives involved

## New Cache/Performance Gates

- Performance-First Questions: 16 pre-implementation questions
- Hot-Path Forbidden Patterns: 19 patterns
- Cache Design Gate: 17 required fields; cache without invalidation/lifecycle forbidden unless immutable deployment-time compiled data
- Worker Safety Gate: 10 checks; any leak is BLOCKER
- Compilation/Warmup Preference: 6 preferred patterns with 4 requirements
- Benchmark Proof: 6 acceptable proof types with required performance-proof.md
- Classification: 12 categories, 4 blocking

## Validation

```
composer validate --no-check-publish: GREEN (./composer.json is valid)
php tooling/governance/check-governance-index-current.php: GREEN (Governance index is current)
php tooling/governance/check-root-evidence-hygiene.php: GREEN (Root Evidence Hygiene PASSED)
```

All validation GREEN.
