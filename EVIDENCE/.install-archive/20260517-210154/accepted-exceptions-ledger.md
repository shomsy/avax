# Governance Accepted Exceptions Ledger

## Status

This is the canonical governance exception register for all AvaX rules.

## Rules

- Every governance exception MUST be recorded here.
- An exception without owner and expiry is not an exception. It is unresolved governance debt.
- Permanent exceptions require explicit architecture decision.
- Temporary exceptions require expiry.
- Exceptions without proof cannot be GREEN.
- Security exceptions cannot be GREEN unless mitigated and proven safe.
- This register is reviewed during every governance code review.

## Required Fields

| Field | Required | Description |
|---|---|---|
| Rule violated | yes | Exact rule being waived |
| File/path | yes | Where the exception applies |
| Reason | yes | Why the rule cannot be followed |
| Owner | yes | Who authorized the exception |
| Expiry/version | yes | When it will be reviewed |
| Risk | yes | Impact of waiving the rule |
| Mitigation | yes | How the risk is managed |
| Test/proof | yes | Evidence the exception is safe |
| Gate handling | yes | How gates treat this exception |
| Blocks current stage? | yes | Stage locking decision |

## Exceptions

| Rule | File/path | Reason | Owner | Expiry | Risk | Mitigation | Test/proof | Gate handling | Blocks stage? |
|---|---|---|---|---|---|---|---|---|---|
| | | | | | | | | | |
