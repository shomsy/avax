# Execution Profiles

This document defines canonical operating profiles for systems that vary timing,
verbosity, or non-functional behavior.

## Profiles

### Normal

- default end-user or operator behavior
- standard timing, logging, and recovery posture

### Slow

- increased wait or visibility for teaching, demos, or careful verification
- preserves canonical meaning and state outcomes
- should not introduce extra branches unavailable in normal mode

### Fast

- reduced waiting where correctness is unaffected
- intended for automation, verification, and restore flows

### Instant

- duration is effectively zero where timing is not part of the contract
- intended for smoke checks, replay, restore, and other fast proof paths
- must not change canonical outputs or safety rules

### Diagnostic

- increased visibility into state transitions and failures
- may reduce performance in exchange for observability

## Rule

Profiles may change timing, logging, or presentation detail.
Profiles must not change canonical outputs, contracts, or safety boundaries.
