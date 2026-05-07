# AvaX Release, Upgrade and Migration Policy

**Date:** 2026-05-07
**Stage:** Stage 19 — Release, Upgrade and Migration Policy
**Status:** ACTIVE

---

## 0. Purpose

This document outlines how AvaX releases new versions and guarantees safe upgrades for end users.

## 1. Release Cadence

- **Major Versions (1.0, 2.0):** Released annually. Contains breaking changes.
- **Minor Versions (1.1, 1.2):** Released monthly. Backward compatible features.
- **Patch Versions (1.1.1):** Released as needed for bugs and security fixes.

## 2. Automated Upgrades

Every Major and Minor release MUST be accompanied by an automated Rector ruleset (`avax/rector-rules`) that
automatically rewrites user code to the new API shapes.

## 3. Migration Paths

- Data migrations for core framework features (e.g., Session tables, Job queues) must be backward compatible for at
  least one minor release before dropping old columns.
