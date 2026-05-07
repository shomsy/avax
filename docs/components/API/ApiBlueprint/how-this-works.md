---
title: api-blueprint-how-this-works
owner: avax-core
last_reviewed: 2026-05-07
classification: internal
---

# API Blueprint How This Works

## What This Folder Is

This folder documents the V2 API Blueprint component. The component owns endpoint definitions, request payload schemas,
response payload schemas, authentication requirements, compatibility analysis, and API documentation source material.

## Real Commands Or Triggers

- Targeted unit tests under `tests/Unit/Components/API/ApiBlueprint/`
- Future V2 API compatibility validation commands

## Exact Upstream Handoffs

- `components/API/ApiBlueprint/System/PublicSurface/ApiBlueprint.php`
- function: `ApiBlueprint::registerEndpoint(...)`
- function: `ApiBlueprint::validate()`
- function: `ApiBlueprint::detectCompatibility(...)`

## The Simplest Story

- A caller registers endpoint definitions through `ApiBlueprint`.
- `DefineApiBlueprint` reads the documentation source and returns an `ApiBlueprintDefinition` snapshot.
- `VerifyApiBlueprint` checks required endpoint guarantees.
- `AnalyzeApiEvolution` compares an old surface with a new surface.

## The First Important Path

```mermaid
sequenceDiagram
    autonumber
    participant Test as ApiBlueprintTest
    participant Blueprint as ApiBlueprint
    participant Define as DefineApiBlueprint
    participant Verify as VerifyApiBlueprint
    participant Report as ApiBlueprintReport
    Test ->> Blueprint: registerEndpoint(endpoint)
    Test ->> Blueprint: validate()
    Blueprint ->> Define: build()
    Blueprint ->> Verify: validate(surface)
    Verify -->> Report: errors and warnings
```

## Failure Behavior

An endpoint without a success response is invalid. Removed endpoints are compatibility issues and are classified as
critical by `CompatibilityReport::criticalChanges()`.

## Where To Debug First

Start with `ApiBlueprint::validate()` for endpoint shape issues and `AnalyzeApiEvolution::analyze()` for
compatibility output.
