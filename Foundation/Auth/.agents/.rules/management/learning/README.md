# Learning Directory — Observations & Instincts Storage

This directory hosts the Continuous Learning pipeline artifacts.
See `.agents/governance/intelligence/learning/continuous-learning.md` for the governing contract
and `.agents/governance/intelligence/learning/instincts-policy.md` for the instinct format.

## Structure

```
learning/
├── README.md                    # This file
├── observations.jsonl           # Append-only tool usage log (Phase 1)
├── instincts/                   # Detected behavioral patterns (Phase 2)
│   └── (instinct files go here)
├── evolved/                     # Graduated artifacts (Phase 3)
│   ├── skills/                  # Promoted to formal skills
│   ├── rules/                   # Promoted to governance rules
│   └── agents/                  # Promoted to agent definitions
└── archived/                    # Decayed or superseded instincts
```

## Usage

- **Phase 1** (Observe): Tool invocations are appended to `observations.jsonl`
- **Phase 2** (Detect): Analysis produces instinct files in `instincts/`
- **Phase 3** (Evolve): Mature instincts graduate to `evolved/`
- `analyze-instincts.py` is the baseline detector for Phase 2

## Feature Flag

This system is controlled by the `continuous_learning` feature flag.
When disabled, no observations are captured and no analysis runs.
