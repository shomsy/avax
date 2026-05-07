# AvaX Security Threat Model

**Date:** 2026-05-07
**Stage:** Stage 16 — Security Threat Model
**Status:** ACTIVE

---

## 0. Purpose

Security in AvaX is not bolted on; it is structurally guaranteed. This document outlines the core threat vectors and how
AvaX mitigates them through its architecture.

## 1. Core Threat Mitigations

- **SQL Injection:** Mitigated via forced usage of parameterized queries in the `DataStack` components. Raw queries are
  audited.
- **XSS:** Output escaping by default in Presentation components.
- **CSRF:** Stateful HTTP methods enforce CSRF token validation via `HTTP/Security` capabilities.
- **Mass Assignment:** Enforced DTO boundaries in `DataStack/Data` prevent mass assignment.
- **Path Traversal:** File access is restricted via `Application/Filesystem` sandbox normalization.
- **Memory Leaks in Workers:** The `MemoryLifecycle` engine ensures hard termination of contaminated requests in
  long-lived environments (RoadRunner/Swoole).
