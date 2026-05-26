# AntiPattern: Stovepipe System

## What It Is

A local vertical slice solves its own problem while bypassing shared platform capabilities, creating a private mini-system.

## Symptoms

- Local logging, config, cache, filesystem, security, or serialization code duplicates platform capabilities.
- Integration boundaries are private and hard to reuse.
- Similar features cannot share operational behavior.
- Component code reaches around public surfaces or capability APIs.

## Why It Is Dangerous

Stovepipes fragment platform behavior and make dogfooding, observability, security, and runtime safety inconsistent.

## Common AI Failure Mode

The agent builds a self-contained local subsystem because it is faster than discovering existing AvaX capabilities.

## How to Fix

Review existing capabilities, route through approved public or capability APIs, and document any local exception with risk and expiry.

## Allowed Exceptions

Prototype-only exploration may be isolated if it is not promoted as production code and is clearly disposable.

## Severity

HIGH for production components bypassing first-party boundaries. BLOCKER if it bypasses security, DI, or runtime lifecycle.
