# AntiPattern: Shallow Tests

## What It Is

Tests that create confidence without proving behavior.

## Symptoms

- `assertTrue(true)`
- constructor-only tests
- not-null assertions without behavior
- getter/setter-only tests
- mocking the subject under test

## Why It Is Dangerous

It produces fake GREEN and hides missing failure coverage.

## Common AI Failure Mode

An agent writes tests to increase count or satisfy a request quickly.

## How to Fix

Test public behavior, failure paths, security denial, and regression cases.

## Allowed Exceptions

Temporary legacy baseline entries with owner, reason, and review date.

## Severity

HIGH when used for readiness claims. BLOCKER for security-sensitive behavior without negative tests.
