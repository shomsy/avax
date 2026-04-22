---
title: Contracts-how-this-works
owner: foundation-database-connections
last_reviewed: 2026-04-22
classification: internal
---

# Contracts How This Works

## What this folder is

This folder owns supporting types for the connections capability.

## Real commands or triggers that reach this folder

- Connections runtime types resolve these support contracts and value objects during connection setup

## Exact upstream handoffs

- Connections/* delegates small focused responsibilities into this folder

## Main decision point

- These types define boundaries, payloads, and exception shapes for connection flows

## Writes and side effects

- No direct side effects; these are support primitives

## Debug first

- Start with the specific contract, value object, or exception being referenced in the failing path
