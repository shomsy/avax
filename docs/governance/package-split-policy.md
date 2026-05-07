# AvaX Package Split Readiness Policy

**Date:** 2026-05-07
**Stage:** Stage 18 — Package Split Readiness
**Status:** ACTIVE

---

## 0. Purpose

The AvaX framework is built as a monorepo. This document dictates the rules for when and how components can be split
into individual packages.

## 1. Split Rules

- A component suite (`components/HTTP`, `components/DataStack`) can only be split into its own `avax/*` package if it
  has ZERO circular dependencies with other component suites.
- The `framework/System` root MUST remain the unifying kernel and cannot depend on `components/*` except via inverted
  interfaces (e.g., `ComponentProviderInterface`).
- Splitting is automated via `tooling/release/split-packages.sh`.

## 2. Monorepo First

AvaX follows the "Monorepo First" principle. Package splitting is purely a release-time artifact creation mechanism for
external consumers, not a development model.
