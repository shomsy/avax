# `language-specific/` — Child Repo Placeholder

This directory stays intentionally empty in the reusable `agent-harness`
repo.

Child repos may add local language or framework rules here only after the
reusable `.agents` project has been mounted into `.agents/.rules/`, and only
when the reusable profiles under `.agents/.rules/governance/profiles/` are not
enough.

## Use This Folder Only For

1. repo-local stack constraints that are not reusable across unrelated projects
2. team idioms that must stay close to the adopting repository
3. temporary local overlays during a tracked migration

## Generalization Rule

If a rule becomes reusable across unrelated repositories that use the same
language or framework, move it into `.agents/.rules/governance/profiles/`.
