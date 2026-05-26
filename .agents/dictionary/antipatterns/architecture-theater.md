# AntiPattern: Architecture Theater

## What It Is

Architecture text, folders, or diagrams that look rigorous but do not guide implementation, review, or validation.

## Symptoms

- diagrams with no source relationship
- rules with no checker or review path
- folder names copied from patterns without behavior
- GREEN claims without command evidence

## Why It Is Dangerous

It gives false confidence and lets drift pass as governance.

## Common AI Failure Mode

An agent writes impressive governance prose without making it executable.

## How to Fix

Add a checker, review question, evidence requirement, or explicit manual review decision.

## Allowed Exceptions

Exploratory notes clearly marked non-canonical.

## Severity

HIGH when used for governance decisions. BLOCKER when it supports fake GREEN.
