# Agent Bootstrap Contract

Version: 1.0.0
Status: Normative

## Purpose

Define the mandatory startup sequence every AI agent must follow when beginning
work in a repository that uses the Agent Harness operating system.

## Bootstrap Sequence

Every agent MUST execute these steps in order:

### Step 1: Read AGENTS.md

Read the root `AGENTS.md` file. This is the mandatory entry point.
It declares the local project contract, applied governance stack, and
project-specific overrides.

### Step 2: Resolve Applicable Governance Stack

Follow the profile resolution algorithm to determine:

- active SDLC lane for the current task
- applicable repository-kind profiles
- applicable language profiles
- applicable framework profiles
- applicable architecture overlays
- required security and operations rules

Source: `.agents/governance/core/resolution/profile-resolution-algorithm.md`

If the project provides `.agents/config/project.json`, use it to accelerate
profile resolution. The machine config is advisory — `AGENTS.md` remains the
human source of truth.

### Step 3: Load Selected Profiles

Load only the profiles that are declared or strongly inferred:

1. repository-kind profiles from `.agents/governance/profiles/repository-kinds/`
2. project-type profiles from `.agents/governance/profiles/project-types/`
3. language profiles from `.agents/governance/profiles/languages/`
4. framework profiles from `.agents/governance/profiles/frameworks/`
5. architecture overlays from `.agents/governance/architecture/profiles/`

Do not load profiles without evidence. Do not invent profiles that do not exist.

### Step 4: Load Local Overlays

Load project-specific overrides:

1. `.agents/business-logic/` for domain behavior
2. `.agents/language-specific/` for local stack exceptions
3. project-specific architecture in root `AGENTS.md`

Local overlays narrow or extend profiles. They must not silently contradict
safety floors.

### Step 5: Read Management State

Read the current management state:

1. `.agents/management/CURRENT.md` for operational truth
2. `.agents/management/ACTIVE.md` for active work
3. `.agents/management/STATUS.md` for GREEN/YELLOW/RED snapshot
4. `.agents/management/TODO.md` and `.agents/management/BUGS.md` for backlog

This context prevents duplicate work, contradictory changes, and drift.

### Step 6: Execute Work

Execute the task following:

1. execution policy (explore vs execute modes)
2. quality gates
3. applicable profile rules
4. recursive review contract

### Step 7: Update Machine Evidence

After completing work, update machine evidence:

1. record what changed in `.agents/management/evidence/`
2. record validation results
3. update backlog in TODO.md or BUGS.md
4. update ACTIVE.md if work status changed

### Step 8: Update Human Dashboard

If the change is significant enough to affect the human dashboard:

1. update `EVIDENCE/CURRENT.md` if operational state changed
2. update `EVIDENCE/CHANGELOG.md` with a summary
3. update `EVIDENCE/ACTIVE_PLAN.md` if the plan changed

### Step 9: Run Recursive Governance Review

Before committing, follow the recursive review contract:

Source: `.agents/governance/standards/review/recursive-review-contract.md`

## Fallback Behavior

If the project has no `.agents/config/project.json` and `AGENTS.md` is
incomplete:

- fall back to universal quality, security, execution, and architecture law
- apply only profiles with strong evidence from the repository tree
- record missing declarations as governance debt
- prefer safe under-application over confident hallucination

## Rule

The bootstrap sequence is not optional. An agent that skips bootstrap and
starts coding directly is operating outside the governance system.
