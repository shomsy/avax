# React Governance Profile

Use this profile for React-based frontends.

Apply it together with the relevant `languages/typescript.md` or
`languages/javascript.md` profile and `languages/css.md`.
Apply the matching architecture overlay from
`../../architecture/profiles/frameworks/react.md` when repo shape matters.

## Component Rules

- keep components focused by capability
- separate render, state transitions, and side effects clearly
- avoid deeply coupled component trees without explicit boundaries

## State and Effects Rules

- keep derived state computed, not duplicated
- isolate asynchronous effects and cancellation behavior
- prevent stale closure bugs through explicit dependencies

## UX and Performance Rules

- treat accessibility as part of done criteria
- avoid unnecessary re-renders in hot interaction paths
- measure before introducing memoization complexity
