# AntiPattern: Blob / God Object

## What It Is

A unit that owns too many unrelated responsibilities and becomes the center of unrelated changes.

## Symptoms

- many public methods
- unrelated dependencies
- mixed orchestration, persistence, validation, and formatting
- tests that must set up the whole world

## Why It Is Dangerous

It hides ownership, increases coupling, and makes safe change difficult.

## Common AI Failure Mode

An agent creates one large "do everything" class to finish a task quickly.

## How to Fix

Split by behavior owner, flow, capability, or assembly boundary.

## Allowed Exceptions

Temporary characterization wrappers with owner, expiry, and refactoring plan.

## Severity

HIGH for touched production code. BLOCKER when security or runtime safety is centralized unsafely.
