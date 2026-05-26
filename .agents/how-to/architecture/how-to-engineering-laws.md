# Engineering Laws and Heuristics for Review Discipline

## 1. Purpose

This document collects engineering laws, heuristics, and cognitive biases that affect software design, review, and operation.

It is project-agnostic. It contains no project-specific details. It applies to any software engineering context.

It exists to make review heuristics explicit, consistent, and actionable.

## 2. What This Document Is

- A collection of observed engineering patterns and cognitive traps
- A review checklist for common failure modes
- A shared vocabulary for discussing design trade-offs
- A reminder that laws describe tendencies, not absolutes

## 3. What This Document Is Not

- A replacement for domain expertise or context-specific judgment
- A set of absolute rules that override security, correctness, or production safety
- A justification for bikeshedding or style debates
- A license to block merge for trivial observations

## 4. How to Apply Engineering Laws in Review

### 4.1 Laws Are Heuristics, Not Doctrine

Every law in this document describes an observed tendency in complex systems.

None of them override:

- Security requirements
- Data integrity guarantees
- Runtime safety constraints
- Public API compatibility contracts
- Production readiness gates

### 4.2 Severity Model

| Severity | Meaning |
|----------|---------|
| YELLOW | Observation noted. Owner assigned. Risk documented. Mitigation planned. Expiry set. Does not block merge. |
| BLOCKER | Law intersects with security, data integrity, runtime safety, public API break, or production readiness. Blocks merge until resolved. |

**Default severity for all engineering laws is YELLOW.**

Escalation to BLOCKER requires explicit justification tied to a concrete risk in one of the protected categories.

### 4.3 Trigger Table

| Review Context | Applicable Laws |
|----------------|----------------|
| Metrics, KPIs, SLAs, monitoring | Goodhart's Law |
| Public API, PublicSurface, facade, DSL | Hyrum's Law, Least Astonishment |
| Any abstraction, layer, wrapper, proxy | Leaky Abstractions |
| Refactoring, removal, replacement | Chesterton's Fence |
| Network IO, distributed systems, cache, queue | Distributed Computing Fallacies |
| Performance optimization | Amdahl's Law, Jevons' Paradox |
| Review discussions, style debates | Law of Triviality |
| API design, naming, defaults | Least Astonishment |

## 5. Required Rule

Engineering laws **MUST NOT** be used as blockers unless the law intersects with:

- Security boundary violation
- Data integrity risk
- Runtime safety failure
- Public API compatibility break without migration
- Production readiness gate failure

Engineering laws **MUST** default to YELLOW:

- Observation with owner, risk, mitigation, and expiry
- Actionable for the next slice
- Does not block merge unless escalated

Engineering laws **MUST** escalate to BLOCKER only when:

- Metric gaming creates security or data integrity risk (Goodhart's)
- Observable public behavior change without migration path (Hyrum's)
- Abstraction hides failure modes that violate fail-closed guarantees (Leaky Abstractions)
- Fence removal eliminates a safety boundary (Chesterton's Fence)
- Network failure handling violates data integrity or security (Distributed Fallacies)
- Optimization targets wrong bottleneck and creates production risk (Amdahl's)
- Efficiency gain creates unbounded resource consumption (Jevons')
- Behavior violates explicit security or safety contract (Least Astonishment)

## 6. Required Laws

### 6.1 Goodhart's Law

> When a measure becomes a target, it ceases to be a good measure.

**Meaning:** Optimizing for a metric changes the system behavior around the metric, not necessarily the underlying goal.

**Governance Translation:** Review must distinguish between measuring health and targeting the measure. Counter-metrics are required when a metric becomes a target.

**Severity Criteria:**

- **YELLOW:** Single metric used as target without counter-metric. Metric definition unclear.
- **BLOCKER:** Metric gaming creates security risk, data integrity loss, or production instability.

**Review Questions:**

1. What behavior does this metric incentivize?
2. What perverse behavior does it NOT penalize?
3. Is there a counter-metric?
4. Can this metric be gamed without improving the underlying goal?

**Example:** Measuring "lines of test coverage" drives developers to write trivial tests that exercise code without asserting behavior. Counter-metric: mutation testing score or assertion density.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference

---

### 6.2 Hyrum's Law

> With a sufficient number of users of an API, it does not matter what you promise in the contract: all observable behaviors of your system will be depended on by somebody.

**Meaning:** Every observable behavior of a public interface becomes a de facto part of the contract, regardless of whether it was documented.

**Governance Translation:** PublicSurface and API boundaries must document all observable behaviors. Undocumented behaviors are still depended upon.

**Severity Criteria:**

- **YELLOW:** Observable behavior change documented but migration is optional. Implicit behavior noted.
- **BLOCKER:** Observable public behavior change without migration path, breaking dependent systems.

**Review Questions:**

1. What behaviors are observable but undocumented?
2. What order, timing, formatting, or error details could callers depend on?
3. Is the migration path clear for every observable behavior change?
4. Are null, empty, and edge-case behaviors explicit?

**Example:** An API returns results in insertion order but does not document ordering. Callers depend on it. Changing to alphabetical order breaks callers who expected insertion order, even though the contract never promised ordering.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference
- `how-to-data-systems.md` — Section 4: Engineering Laws Cross-Reference
- `how-to-architecture-extension-with-ddd.md` — Engineering Laws Cross-Reference

---

### 6.3 Leaky Abstractions Law

> All non-trivial abstractions, to some degree, are leaky.

**Meaning:** Abstractions hide complexity, but when the hidden complexity matters, the abstraction fails to protect the caller from understanding it.

**Governance Translation:** Every abstraction boundary must document what it hides and when the hidden details matter. Callers must know when they need to understand the internals.

**Severity Criteria:**

- **YELLOW:** Abstraction leaks internals but callers are aware and bounded. Hidden details documented in capability docs.
- **BLOCKER:** Abstraction hides failure modes that violate fail-closed guarantees, security boundaries, or data integrity.

**Review Questions:**

1. What complexity does this abstraction hide?
2. When does the hidden complexity matter to the caller?
3. Does the abstraction hide failure modes the caller must handle?
4. Can the caller detect when the abstraction is leaking?

**Example:** An ORM abstracts SQL queries but leaks N+1 query problems. The caller must understand SQL execution patterns to use the ORM correctly, defeating the purpose of the abstraction.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference
- `how-to-system-performance.md` — Section 47: Engineering Laws Cross-Reference
- `how-to-data-systems.md` — Section 4: Engineering Laws Cross-Reference
- `how-to-architecture-extension-with-ddd.md` — Engineering Laws Cross-Reference

---

### 6.4 Chesterton's Fence

> Do not remove a fence until you know why it was put there in the first place.

**Meaning:** Before removing or replacing a constraint, understand the original problem it solved.

**Governance Translation:** Refactoring and removal must include analysis of the original constraint's purpose. Deleting without understanding is a risk.

**Severity Criteria:**

- **YELLOW:** Fence purpose understood but removal is safe with monitoring. Original constraint documented.
- **BLOCKER:** Fence removal eliminates a safety boundary, security control, or data integrity protection.

**Review Questions:**

1. Why was this constraint added?
2. What problem did it solve?
3. Has the original problem been solved another way?
4. What happens if the constraint is removed and the original problem returns?

**Example:** A validation check seems redundant after a refactor. Removing it exposes the system to invalid data from a different input path that the original author anticipated.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-architecture-extension-with-ddd.md` — Engineering Laws Cross-Reference

---

### 6.5 Distributed Computing Fallacies

1. The network is reliable
2. Latency is zero
3. Bandwidth is infinite
4. The network is secure
5. Topology doesn't change
6. There is one administrator
7. Transport cost is zero
8. The network is homogeneous

**Meaning:** Distributed systems assume perfect network conditions. Real networks fail, have latency, limited bandwidth, and change topology.

**Governance Translation:** Any system boundary that crosses a network must handle failure, latency, partial responses, retries, and timeouts.

**Severity Criteria:**

- **YELLOW:** Network assumptions documented but not fully handled. Retry logic present but not bounded.
- **BLOCKER:** Network failure handling violates data integrity, security, or fail-closed guarantees.

**Review Questions:**

1. What happens when this call times out?
2. What happens when the response is partial?
3. Is the retry bounded and idempotent?
4. What is the fallback behavior?
5. Is latency measured or assumed?

**Example:** A service calls an external API without timeout. The external service hangs, consuming all available worker threads, cascading failure to the entire system.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference
- `how-to-data-systems.md` — Section 4: Engineering Laws Cross-Reference

---

### 6.6 Law of Triviality (Bike-Shedding)

> Organizations give disproportionate weight to trivial issues.

**Meaning:** People spend more time discussing things they can easily understand (bike shed color) than complex things they cannot (nuclear reactor design).

**Governance Translation:** Review must weight feedback by impact, not by how easy it is to discuss. Style debates must not block architecture decisions.

**Severity Criteria:**

- **YELLOW:** Review discussion disproportionate to impact. Noted and redirected.
- **BLOCKER:** Not applicable. This law is a meta-review observation, not a code finding.

**Review Questions:**

1. Is this comment proportional to its impact?
2. Am I discussing this because I understand it or because it matters?
3. Is this blocking more important decisions?

**Example:** A review spends 20 comments on variable naming and zero on a missing idempotency key in a payment flow.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference

---

### 6.7 Amdahl's Law

> The speedup of a program using multiple processors is limited by the sequential portion of the program.

**Meaning:** Optimization impact is bounded by the fraction of time the optimized path actually runs. Optimizing a non-bottleneck has minimal total effect.

**Governance Translation:** Performance optimization must target the actual bottleneck. Proof of bottleneck is required before optimization.

**Severity Criteria:**

- **YELLOW:** Optimization target not proven to be the bottleneck. Profiling data absent but optimization is safe.
- **BLOCKER:** Optimization targets wrong bottleneck, creates production risk, or violates resource bounds.

**Review Questions:**

1. What fraction of total time does this path consume?
2. Is this the actual bottleneck or a convenient one?
3. What is the theoretical maximum speedup?
4. Has profiling data confirmed the bottleneck?

**Example:** Optimizing a function that runs 2% of total request time by 50% improves total time by 1%. Meanwhile, a database query running 60% of the time is unoptimized.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference
- `how-to-system-performance.md` — Section 47: Engineering Laws Cross-Reference
- `how-to-data-systems.md` — Section 4: Engineering Laws Cross-Reference

---

### 6.8 Jevons' Paradox

> As technology increases the efficiency with which a resource is used, the total consumption of that resource increases rather than decreases.

**Meaning:** Making something cheaper to use encourages more use, potentially consuming more total resources.

**Governance Translation:** Efficiency improvements must include resource bounds, quotas, or cost controls. Efficiency without bounds is a production risk.

**Severity Criteria:**

- **YELLOW:** Efficiency improvement without explicit resource bounds. Monitoring in place.
- **BLOCKER:** Efficiency gain creates unbounded resource consumption, cost explosion, or production instability.

**Review Questions:**

1. Does this efficiency improvement encourage more usage?
2. Are there resource bounds or quotas?
3. What is the worst-case total consumption?
4. Is there a circuit breaker or rate limit?

**Example:** Making an API call 10x faster causes callers to make 100x more calls, increasing total server load by 10x despite the efficiency improvement.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-production-readiness.md` — Section 29: Engineering Laws Cross-Reference
- `how-to-system-performance.md` — Section 47: Engineering Laws Cross-Reference

---

### 6.9 Law of Least Astonishment (Least Surprise)

> A component of a system should behave in a way that most users will expect it to behave.

**Meaning:** Systems should match user and developer expectations. Unexpected behavior creates cognitive load, bugs, and security risks.

**Governance Translation:** Public APIs, naming, defaults, and error messages must match domain expectations. Surprise is a design defect.

**Severity Criteria:**

- **YELLOW:** Behavior surprising but documented. Migration from expected pattern noted.
- **BLOCKER:** Behavior violates explicit security contract, safety guarantee, or domain safety invariant.

**Review Questions:**

1. Would a skilled developer expect this behavior?
2. Does the name match what the function does?
3. Are the defaults safe and expected?
4. Would the error message help someone debug a production issue?

**Example:** A method called `findUser` returns null when the user doesn't exist, but a method called `getUser` throws an exception. The naming convention is inconsistent and surprising.

**Cross References:**

- `how-to-code-review.md` — Section 30: Engineering Laws Review Cross-Reference
- `how-to-architecture-extension-with-ddd.md` — Engineering Laws Cross-Reference

---

## 7. Review Checklist

Before marking a review as complete, consider:

- [ ] Goodhart's: Are metrics becoming targets? Are counter-metrics defined?
- [ ] Hyrum's: Are all observable behaviors documented? Are implicit contracts acknowledged?
- [ ] Leaky Abstractions: Are failure boundaries clear? Do callers know when internals matter?
- [ ] Chesterton's Fence: Is the original constraint understood before removal?
- [ ] Distributed Fallacies: Are timeouts, retries, and partial failures handled?
- [ ] Triviality: Is review focus proportional to impact?
- [ ] Amdahl's: Is the optimized path the actual bottleneck?
- [ ] Jevons': Does efficiency have resource bounds?
- [ ] Least Astonishment: Would a skilled developer expect this behavior?

## 8. Cross-Reference Map

| Law | Code Review | Production Readiness | Performance | Data Systems | DDD Architecture |
|-----|-------------|---------------------|-------------|--------------|-----------------|
| Goodhart's | YES | YES | - | - | - |
| Hyrum's | YES | YES | - | YES | YES |
| Leaky Abstractions | YES | YES | YES | YES | YES |
| Chesterton's Fence | YES | - | - | - | YES |
| Distributed Fallacies | YES | YES | - | YES | - |
| Triviality | YES | - | - | - | - |
| Amdahl's | YES | YES | YES | YES | - |
| Jevons' | YES | YES | YES | - | - |
| Least Astonishment | YES | - | - | - | YES |

## 9. Examples of Law Application

### Example 1: Cache Implementation

**Context:** Review of a new cache layer for user profile data.

**Laws applied:**

- **Leaky Abstractions:** Cache must document eviction policy, staleness risk, and invalidation trigger.
- **Distributed Fallacies:** Cache backend must handle network failure, timeout, and fallback to source.
- **Jevons' Paradox:** Cache hit rate improvement must not encourage unbounded cache growth. Memory bounds required.
- **Hyrum's Law:** Cache return format (null vs exception on miss) must be explicit and stable.

**Decision:** YELLOW — Cache design sound but lacks explicit memory bounds and invalidation documentation. Owner assigned. Mitigation: add memory bound config and invalidation trigger docs. Expiry: next sprint.

### Example 2: API Response Ordering Change

**Context:** Changing API response from insertion order to alphabetical order.

**Laws applied:**

- **Hyrum's Law:** Callers may depend on ordering even if undocumented.
- **Least Astonishment:** Callers expect stable ordering. Change is surprising.

**Decision:** BLOCKER — Observable behavior change without migration path. Required: deprecation period, versioned endpoint, or explicit ordering parameter with default matching current behavior.

### Example 3: Performance Optimization of Minor Hot Path

**Context:** Optimizing a serialization function that accounts for 3% of request time.

**Laws applied:**

- **Amdahl's Law:** Maximum total improvement is 3%, even with infinite serialization speedup.
- **Jevons' Paradox:** Faster serialization may encourage more serialization calls.

**Decision:** YELLOW — Optimization acceptable if cost is low, but profiler should identify actual bottleneck. Required: note in review that this is not the bottleneck and larger gains exist elsewhere.

## 10. Final Law

Engineering laws do not replace judgment.

They sharpen judgment.

They make review observations explicit, consistent, and actionable.

They prevent repeated mistakes from being discovered individually.

They remind reviewers that:

- Metrics can be gamed
- Observable behavior is always depended upon
- Abstractions leak
- Fences have purposes
- Networks fail
- Focus drifts to trivia
- Bottlenecks limit optimization
- Efficiency increases consumption
- Surprise is a design defect

Laws are YELLOW by default.

BLOCKER requires intersection with security, data integrity, runtime safety, public API, or production readiness.

No law overrides correctness.

No law replaces context.

No law excuses missing evidence.
