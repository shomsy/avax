# ARCHITECTURE NOTES

## Phase 0: Context and Scope Gate (Mandatory)

### 0.1 System Identity
- **System Type:** Framework component (HTTP router with middleware pipeline)
- **Primary Consumers:** Internal application layer, HTTP kernel
- **Runtime Context:** HTTP request/response lifecycle, per-request execution
- **Lifecycle:** Stable core (hardened routing, caching, pipelines)

### 0.2 Intended Use-Cases and Anti-Use-Cases
- **Intended Use-Cases:**
  - HTTP route registration via DSL
  - Domain-aware route matching
  - Prefix and group-based route organization
  - Middleware and authorization pipeline execution
  - Route caching for performance
  - Fallback route handling
- **Anti-Use-Cases (things the system explicitly should NOT do):**
  - Direct manipulation of global state outside DSL
  - Undefined middleware classes in pipelines
  - Bypassing router kernel for request dispatch
  - Multi-protocol routing (non-HTTP)
  - Advanced observability stack beyond basic tracing

### 0.3 Non-Goals
- Full web server implementation
- Multi-protocol support
- Comprehensive observability tooling (dashboards, agents, pipelines)
- Advanced middleware state machines

### 0.4 Compatibility Contract
- **Public API Stability Requirement:** Moderate (DSL and runtime interfaces stable)
- **Backwards Compatibility:** Required for RouterInterface and RouterRuntimeInterface; internals may change
- **Performance Budget:** Sub-millisecond route resolution; caching prevents regex recompilation on boot

## Phase 1: System and Architecture Review

### 1.1 Actual Execution Flow (As-Built)
Public API (Router::resolve) -> RouterKernel::handle -> HeadRequestFallback -> HttpRequestRouter::resolve -> RoutePipelineFactory::create -> RoutePipeline::dispatch -> ControllerDispatcher::dispatch

**This is how the system actually works.**
Incoming PSR-7 request enters Router::resolve, which delegates to RouterKernel::handle. HEAD requests may be converted to GET. HttpRequestRouter::resolve matches routes, extracts parameters, validates constraints. RouterKernel injects parameters into request. RoutePipelineFactory builds a StageChain (authorization -> middleware -> dispatch), executed via RoutePipeline::dispatch, ending at ControllerDispatcher.

### 1.2 Central Abstraction Identification

#### 1.2.1 Primary Axis Rule
This system is fundamentally organized around **validated RouteDefinition feeding a deterministic middleware/dispatch pipeline**.

#### 1.2.2 Secondary Axis
Secondary axis: **DSL-driven route registration** (adds grouping/prefix complexity but enables developer ergonomics).

**Assessment:** Necessary for DX, but global state in RouteGroupStack is a risk.

### 1.3 Central Abstraction Stress Test

- Does every feature flow through it? Yes, all routing goes through RouteDefinition -> pipeline.
- Does it accumulate responsibilities over time? No, focused on route matching and execution.
- Is it harder to change than surrounding components? No, RouteDefinition is central but not bloated.

**Assessment:** ✅ Pass: stable axis

### 1.4 Responsibility and Boundary Mapping

| Component | Orchestrates | Executes | Holds State | Notes |
|-----------|--------------|----------|-------------|-------|
| RouterDsl | DSL registration, group context | RouteBuilder application | - | Pure DSL, no runtime state |
| Router | Runtime entry | Exception handling, fallback | - | Thin facade |
| RouterKernel | Request flow | Parameter injection | - | Orchestrator |
| HttpRequestRouter | Route matching, resolution | Regex matching, validation | routes, namedRoutes | Core matcher |
| RoutePipelineFactory | Pipeline assembly | Stage ordering | - | Factory pattern |
| RoutePipeline | Chain execution | Middleware reduce, dispatch | per-request stages | Execution chain |
| RouteGroupStack | Group context | - | static stack | Global state concern |

**Responsibility boundaries are: clear / stressed / violated.** Stressed by global RouteGroupStack.

### 1.5 Pipeline and Control Flow Analysis

| Step | Mandatory | Conditional | Mutates State | Terminal |
|------|-----------|-------------|---------------|----------|
| HeadRequestFallback | No | HEAD requests | request method | No |
| HttpRequestRouter::resolve | Yes | - | parameters | Yes on 404/405 |
| Parameter injection | Yes | - | request attributes | No |
| RoutePipelineFactory::create | Yes | - | - | No |
| RoutePipeline::dispatch | Yes | auth/middleware present | - | Yes (response) |

**Yes** - Pipeline is a formal state machine with explicit stages and ordering validation.

### 1.6 Mutability Audit

| Object | Scope | Lifetime | Why Mutable? | Classification |
|--------|-------|----------|--------------|----------------------------------------|
| HttpRequestRouter routes/namedRoutes | App | Boot | Route storage | Necessary |
| FallbackManager handler | App | Boot | Fallback config | Controlled |
| RouteGroupStack stack | Global | Boot | Group context | Design Smell (global static) |
| RoutePipeline stages | Request | Per request | Chain build | Necessary |

**Mutability is: justified / excessive / misplaced.** Excessive global state in RouteGroupStack.

### 1.7 System Invariants

| Invariant | Enforced Where | Evidence | Status |
|-----------|----------------|----------|-------------------------------------|
| HTTP methods valid | RouteDefinition | Routing/RouteDefinition.php | Enforced |
| Route paths valid | RoutePathValidator | Routing/RoutePathValidator.php | Enforced |
| Constraints valid regex | RouteDefinition | RouteDefinition.php | Enforced |
| Cache closure-free | RouteDefinition toArray/fromArray | Cache/ | Enforced |
| Stage ordering valid | StageChain validateOrder | Routing/StageChain.php | Enforced |
| Fallback deterministic | HttpRequestRouter resolve | Routing/HttpRequestRouter.php | Enforced |
| No duplicate routes | - | - | Not enforced (Finding 3) |

## Phase 2: Foundational and Critical Design Review

### 8.1 Framework-in-a-Framework Syndrome
**Not Present**

### 8.2 Abstractions Without Real Variance
- RouteMatcherInterface has single implementation (DomainAwareMatcher)
- RouterRuntimeInterface has single implementation (Router)

**Acceptable** - Interfaces enable testing/extension but not over-engineered.

### 8.3 "Too Clever" Design Test
**Clear** - DSL naming is readable, no internal knowledge required beyond basic routing concepts.

### 8.4 Configuration as Architectural Signal
**Healthy** - Configuration is additive, not proxy for architecture.

### 8.5 Performance-by-Design Sanity Check
- Is caching required for acceptable performance? No, but used for boot performance.
- Are many objects created per request? No, pipelines are built per route.
- Could parts be plain functions? No, object-oriented is appropriate.
- Is reflection on the hot path? No.

**Conclusion:** No performance risks.

### 8.6 Failure Modes and Diagnostic Surface
- Errors categorized? Yes (programmer vs runtime).
- Exceptions include context? Yes.
- Explain decisions? Via RouterTrace.
- Failure states explicit? Yes in pipeline.

**Yes/No matrix:** All Yes.

### 8.7 Rewrite Heuristics
| Heuristic | Weight | Checked |
|-----------|--------|---------|
| Central abstraction is wrong | 2 | ☐ |
| Pipeline relies on implicit ordering | 2 | ☐ |
| Configuration complexity mirrors design complexity | 1 | ☐ |
| Usage requires explanation to avoid misuse | 1 | ☐ |
| Performance depends on mitigation, not structure | 1 | ☐ |
| New features require touching multiple core classes | 2 | ☐ |

**Rewrite Score:** 0

## FINDINGS

### Finding: Global RouteGroupStack introduces state leakage risk
- **Symptom:** RouteGroupStack uses static stack shared across all route registrations and boots.
- **Root Cause:** Legacy grouping implementation retained as global static for compatibility.
- **Impact:** Potential test pollution, conflicts in multi-tenant or async contexts, violates DI principles.
- **Evidence:** Routing/RouteGroupStack.php static $stack; used in RouterDsl::group.
- **Risk Level:** High

### Finding: No deduplication guard for route registration
- **Symptom:** Routes can be registered multiple times without detection.
- **Root Cause:** HttpRequestRouter::add appends without checking duplicates.
- **Impact:** Non-deterministic matching, middleware duplication, performance degradation.
- **Evidence:** HttpRequestRouter::add overwrites by path but allows same path different methods; no name/path dedupe.
- **Risk Level:** High

### Finding: Complex dependency injection may lead to circular references
- **Symptom:** RouterKernel depends on HttpRequestRouter, which depends on RouteMatcher, etc.
- **Root Cause:** Tight coupling in constructor injection.
- **Impact:** Hard to test, refactor; potential runtime errors.
- **Evidence:** RouterKernel::__construct parameters; HttpRequestRouter dependencies.
- **Risk Level:** Medium

## DECISION
⚠️ **Redesign** — Critical state leakage and deduplication issues require targeted redesign of RouteGroupStack and registration guards.

## DECISIONS-LOG

## Decision:
- **Date:** 2026-01-13
- **Context:** Critical code review requested
- **Decision:** Redesign
- **Alternatives:** Keep and improve (rejected due to high-risk global state)
- **Consequences:** Improved isolation, testability; requires group context refactoring
- **Evidence:** Global static stack, missing dedupe guards

## NEXT STEPS

### Constraints (Mandatory)
- API stability requirement: Moderate (DSL surface stable)
- Performance budget: Sub-ms resolution
- Security boundaries: No global state leaks
- Time and risk tolerance: Low risk for isolation changes
- Migration expectations: Backward compatible

### Kill Criteria (Mandatory)
- After redesign, RouteGroupStack no longer static
- Dedupe guard prevents duplicate registrations
- All tests pass without global pollution

### If Redesign
- Which abstraction is being redesigned: RouteGroupStack global state
- What remains intact: Core routing pipeline
- First 3 concrete actions:
  1. Refactor RouteGroupStack to instance-based per registrar
  2. Add deduplication logic in HttpRequestRouter::add
  3. Update RouterDsl to use injected group context