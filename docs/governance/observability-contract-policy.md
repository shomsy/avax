# AvaX Observability Contract Policy

**Date:** 2026-05-07
**Stage:** Stage 15 — Observability Contract
**Status:** ACTIVE

---

## 0. Purpose

This document outlines the observability requirements for the AvaX framework. Observability is not an afterthought; it
is a first-class citizen built into the core capability contract. AvaX aims for a "Glass-Box" architecture where
internal state mutations and flows emit structured insights by default.

---

## 1. The Observability Triad

AvaX supports the standard observability triad natively:

1. **Logs (Structured Logging):** JSON-formatted structured logs out of the box. String interpolation logging is
   forbidden for production engines.
2. **Metrics:** Core components (like the Router, Container, Database) must emit exact metrics (counters, gauges,
   histograms) for their critical paths.
3. **Traces (Distributed Tracing):** Native integration capabilities for OpenTelemetry. The framework propagates
   Correlation IDs and Trace Context automatically across request scopes.

---

## 2. Capability Observability Rules

Every `Capability` in the framework that interacts with I/O (Database, Cache, External API) MUST emit:

- A start trace span.
- A success or failure trace span.
- A duration metric.

Every `Flow` MUST emit:

- A structured log indicating the flow has started, including the `Correlation-ID`.
- A structured log indicating the outcome of the flow (Success/Failure) and its duration.

---

## 3. The `RuntimeDoctor`

The `RuntimeDoctor` (`avax runtime:doctor`) acts as the observability baseline checker.
It ensures that:

- Logging sinks are reachable.
- Metric exporters are configured properly.
- No silent failures are occurring in background workers.

---

## 4. Sensitive Data Redaction

Observability MUST NEVER compromise security.

- The logging and tracing engines must explicitly redact sensitive keys (passwords, tokens, PII) before they leave the
  application boundary.
- Redaction logic is centrally managed in `components/Operations/Observability/System/Capabilities/Redaction/`.
