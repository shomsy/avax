# JavaScript Governance Profile

Use this profile for JavaScript repositories.

This profile assumes modern module-based JavaScript and should be combined with
the relevant runtime, framework, or CSS profiles when applicable.

Apply it together with the relevant architecture overlay under
`../../architecture/profiles/**` when repo shape and slice ownership matter.

## Module And Naming Rules

- prefer small feature slices over large utility buckets
- folder names should communicate flow or capability, file names should state
  one responsibility, and function names should state the exact action
- use `index.js` only as a small public facade or composition entry, never as a
  hidden command center
- avoid generic buckets such as `helpers`, `utils`, `manager`, or `service`
  unless the responsibility is genuinely that broad and unavoidable
- keep import boundaries explicit so readers can predict ownership before
  opening the file

## State And Side-Effect Rules

- keep side effects explicit and near owned boundaries
- avoid hidden mutation in shared objects, ambient caches, or module-level
  mutable state
- separate state, render, and actions when a slice owns real interactive
  complexity
- durable state should not depend on one browser tab, one module instance, or
  one long-lived process unless that limitation is part of the contract
- listeners, timers, observers, and subscriptions must have clear ownership and
  deterministic cleanup

## Browser And UI Rules

- DOM updates should be local, intentional, and owned by the slice that renders
  them
- do not mix browser-only logic and server-only logic in the same module unless
  the boundary is explicit and tested
- no inline behavior or style mutation as the primary architecture when a clear
  module and stylesheet contract can own the job
- UI code should preserve predictable event flow and avoid hidden cross-slice
  DOM reach-in
- when the product is interaction-heavy, optimize for one obvious read path from
  user event to visible effect

## Runtime And Dependency Rules

- pin behavior by lockfile and reproducible scripts
- avoid unnecessary runtime dependencies, especially tiny convenience packages
  that hide simple built-in behavior
- choose the smallest module format and build complexity that honestly fits the
  runtime
- keep environment access explicit rather than scattering direct global reads
  through unrelated modules
- if async or background work exists, define completion, retry, and failure
  behavior explicitly instead of treating promises as self-managing

## Testing And Delivery Rules

- cover pure transformations with unit tests
- cover integration at feature entry boundaries
- add regression tests for previously fixed defects
- test lifecycle cleanup for listeners, timers, effects, or subscriptions when
  the slice owns them
- review packaging and published files so only canonical source, types, docs,
  and intended artifacts ship

## Security Rules

- validate and constrain all external input
- avoid unsafe HTML, URL, script, or shell construction patterns by default
- keep secrets out of code, fixtures, and browser-visible bundles
- treat browser storage, query params, postMessage payloads, and DOM-derived
  input as untrusted until validated
- redact sensitive values before logs, traces, or client-visible error output
