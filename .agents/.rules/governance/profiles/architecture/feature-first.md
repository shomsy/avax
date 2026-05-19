# Architecture Profile: Feature-First

Version: 1.0.0
Status: Normative / Optional
Applies when: project `AGENTS.md` declares `feature-first` architecture.

## What This Profile Adds

Feature-first is a variant of vertical slice with explicit product-facing
naming. Features are named after user-visible capabilities, not technical roles.

## Feature-First Rules

- top-level folders are named after product features, not technical concepts
- each feature folder contains all code needed to deliver that feature
- features may share a common `lib/` or `core/` but must not reach into each other
- feature boundaries are enforced at the module or package level

## Anti-Patterns

- technical folders at the root: `controllers/`, `models/`, `services/`
- feature code split across many unrelated technical folders
