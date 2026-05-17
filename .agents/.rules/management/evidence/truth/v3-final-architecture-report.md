# V3 Final Architecture Report

**Status**: HARDENED
**Date**: 2026-05-16

## 1. Core Structure
The Agent Harness V3 follows a strictly project-agnostic "rules-engine" architecture. The core logic resides in `.agents/.rules/` (hidden from standard workflows) while the local project interface is managed through root `AGENTS.md` and `.agents/config/`.

## 2. Profile Resolution Engine
The resolution engine is now deterministic, supporting language, framework, and project-type profiles with explicit override precedence. Inferred profiles are flagged as YELLOW debt to ensure transparency.

## 3. Trust Boundaries
Execution is gated by a graduated trust model (T0-T3). Agents are sandboxed by default, and dangerous operations require explicit human escalation.

## 4. Continuity & Memory
The architecture supports long-running project continuity through evidence-derived memory, ensuring agents do not lose context across sessions while preventing context poisoning from untrusted inputs.
