# AntiPattern: Cut-and-Paste Programming

## What It Is

Repeated logic is copied instead of represented once as a named behavior, policy, test utility, or documented variant.

## Symptoms

- Same validation, mapping, authorization, or retry logic appears in multiple places.
- One copy changes while another stays stale.
- Tests duplicate setup without clarifying behavior.
- Fixes require searching and patching many similar snippets.

## Why It Is Dangerous

Copying creates drift between rules that should remain consistent, especially security and data correctness rules.

## Common AI Failure Mode

The agent copies a nearby pattern because it compiles, then misses hidden differences in lifecycle, boundary, or failure semantics.

## How to Fix

Decide whether the repetition is shared knowledge or honest variation. Extract shared behavior only when ownership is clear; otherwise document the variant.

## Allowed Exceptions

Small duplication is allowed when abstraction would hide important local intent and the duplication is reviewed as deliberate.

## Severity

HIGH for copied security, authorization, data mutation, or runtime lifecycle behavior. MEDIUM for ordinary maintainability drift.
