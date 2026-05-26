# AntiPattern: Golden Hammer

## What It Is

Using a familiar solution or pattern for every problem regardless of fit.

## Symptoms

- same pattern applied repeatedly
- problem statement missing
- simpler design rejected without reason
- pattern vocabulary replaces behavior vocabulary

## Why It Is Dangerous

It creates needless complexity and hides simpler ownership.

## Common AI Failure Mode

An agent overuses Strategy, Factory, Manager, or DSL shapes because they sound mature.

## How to Fix

State the problem, forces, rejected options, and the smallest fitting solution.

## Allowed Exceptions

Project-standard pattern with documented reason and local fit.

## Severity

MEDIUM by default. HIGH when it affects public API or core architecture.
