# AntiPattern: Fake Abstraction

## What It Is

An abstraction that adds indirection without stabilizing a real variation or boundary.

## Symptoms

- one implementation
- no clear consumer need
- names like `Base`, `Generic`, `Common`
- tests mock the abstraction instead of proving behavior

## Why It Is Dangerous

It hides simple behavior and creates coupling theater.

## Common AI Failure Mode

An agent adds interfaces or factories because they look enterprise-grade.

## How to Fix

Inline the abstraction or document the real variation point.

## Allowed Exceptions

Public API compatibility boundaries with explicit evidence.

## Severity

MEDIUM by default. HIGH when it hides runtime dependency direction.
