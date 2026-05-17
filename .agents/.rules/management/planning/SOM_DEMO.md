# Society of Mind — Orchestration Proof

**Task**: Implement a new security gate.
**Status**: DESIGN

## Orchestration Flow

1.  **Planner Agent**:
    - **Input**: User request.
    - **Action**: Generates `IMPLEMENTATION_PLAN.md` with detailed steps.
    - **Structured Artifact**: `plan-artifact.json`

2.  **Executor Agent**:
    - **Input**: `plan-artifact.json`.
    - **Action**: Modifies files, writes code.
    - **Structured Artifact**: `execution-summary.json`

3.  **Reviewer Agent**:
    - **Input**: `execution-summary.json` + code changes.
    - **Action**: Runs `recursive-review-engine.sh`.
    - **Structured Artifact**: `review-report.json`

4.  **Truth Agent**:
    - **Input**: `review-report.json`.
    - **Action**: Verifies evidence integrity, updates `CURRENT.md`.
    - **Structured Artifact**: `truth-certification.json`

## Rules
- **Supervisor**: Does not accept raw chat messages. Every step must be backed by a JSON artifact in `.agents/management/evidence/raw/`.
