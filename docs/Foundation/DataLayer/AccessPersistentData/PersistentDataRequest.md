---
title: PersistentDataRequest
owner: foundation
last_reviewed: 2026-04-24
classification: internal
---

# PersistentDataRequest

## Why this exists

`Avax\DataLayer\AccessPersistentData\PersistentDataRequest` exists to own one responsibility: owns the responsibility
named by the source file and keeps that responsibility inside its capability folder.

## Why it lives here

It belongs in `Foundation/DataLayer/AccessPersistentData` because that folder names the capability that needs this
responsibility. Moving it into a generic bucket would hide the ownership rule that the source tree is trying to
preserve.

## Public behavior

- The file exposes only the behavior needed by its capability folder.
- Failure is represented by a named exception when this file owns an invalid state or boundary violation.
- Value files carry named data instead of unstructured arrays where the boundary needs a contract.

## What gets written or changed

This file writes nothing unless its class name says `Record`, `Save`, `Append`, `Write`, `Publish`, or `Commit`.

## Failure path

Open this file first when a caller reaches the surrounding capability and the failure names `PersistentDataRequest` or
the concept it owns.