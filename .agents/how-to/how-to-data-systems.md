# Data Systems Correctness Governance

## Status

**MANDATORY** — This document defines non-negotiable rules for data system correctness, transaction handling, and consistency boundaries in AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, **BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.
- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **BLOCKER**: violation prevents GREEN status.

---

## 1. Purpose

This standard translates the lessons of *Designing Data-Intensive Applications* (Kleppmann) into concrete, reviewable rules. It ensures data systems remain correct, consistent, and resilient under concurrency and failures.

---

## 2. Data System Correctness Rule

### 2.1 System of Record vs. Derived State

Subsystems **MUST** explicitly document which data stores act as the **System of Record (SoR)** and which represent **Derived or Cached State**.
- **System of Record:** The source of truth that owns the authoritative state of a domain. Mutations **MUST** apply first to the SoR.
- **Derived State:** Read models, search indexes, caches, and reporting databases. Derived state **MUST** be updateable asynchronously or eventually without blocking the primary transactional flow.

### 2.2 Transactional Boundaries

Database transactions **MUST** be managed at the **Flow layer** or via explicit **Unit of Work** objects assembled at configuration time.
- Capabilities **MUST NOT** manage their own transactions (e.g., calling `beginTransaction()`, `commit()`, `rollback()`) unless the capability is completely self-contained and does not share connection state.
- Nested transactions or "savepoint" patterns **MUST NOT** be used unless explicitly approved in an ADR with documented rollback risk.

### 2.3 Mutation Boundaries

All data mutations **MUST** pass through designated Aggregate roots or Repository interfaces.
- Inline raw SQL mutations or direct Active Record updates outside of Repository boundaries are **FORBIDDEN**.
- Bulk updates **MAY** be executed via specialized capabilities but **MUST** validate invariants before execution.

### 2.4 Idempotency, Retries, and Cache Invalidation

Data systems **MUST** be designed to handle retries safely:
- **Idempotency Keys:** Every data mutation flow initiated by external actors or queues **MUST** use an idempotency key to prevent duplicate writes on network/connection retries.
- **Cache Invalidation:** Any flow mutating the System of Record **MUST** invalidate or refresh corresponding derived/cached states in the same transaction or immediately after commit. Hard-coded cache TTLs without proactive invalidation triggers are **FORBIDDEN**.

---

## 3. Classifications

### 3.1 BLOCKER

- Performing raw database mutations bypassing aggregate repository interfaces.
- Caching user or transactional data without explicit invalidation triggers, namespace cache keys, and defined TTLs.
- Executing write operations on derived states before the System of Record transaction has successfully committed.

### 3.2 RED

- Missing transactional boundaries in write flows (e.g., executing multiple database statements without a wrapping transaction block).
- Missing retry or circuit breaker policies on external database connections or external data stores.
- Direct database writes inside capability layers bypassing the Flow orchestrator.

### 3.3 YELLOW

- Lacking explicit documentation separating the System of Record from derived/cached read models.
- Failing to prove idempotency behavior under simulated network disconnects.
