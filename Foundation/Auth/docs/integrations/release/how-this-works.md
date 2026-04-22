---
title: integrations-release-how-this-works
owner: auth-integrations
last_reviewed: 2026-04-21
classification: internal
---

# Release Integrations How This Works

## What this folder is

`integrations/release/` owns release evidence, source-truth enforcement, quality-gate orchestration, provenance,
rollback artifacts, and other package release diagnostics.

## Real commands or triggers that reach this folder

- `composer conformance`
- `composer quality-gates`
- `composer release:gate`
- local or CI evidence generation commands

## Exact upstream handoffs

- Tooling scripts call these integration classes.
- The classes inspect repository files, build outputs, and docs.
- They emit machine-readable reports that gate release readiness.

## Why this matters

Production readiness in this repository is not a claim. It is an evidence trail, and this folder owns the code that
assembles that trail.
