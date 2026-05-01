# Vanilla Web Components Profile

This profile defines the engineering standard for projects using native Web Components (Vanilla JS) without heavy external frameworks.

Apply it together with the relevant `languages/javascript.md` or
`languages/typescript.md` profile and `languages/css.md`.
Apply the matching architecture overlay from
`../../architecture/profiles/frameworks/v-web-components.md` when repo shape
matters.

## 1. Governance & Scope

- **Native First**: No UI libraries (React, Vue, etc.) or CSS frameworks (Tailwind) are permitted unless explicitly approved.
- **Zero Dependency**: Components should be self-contained and use standard Browser APIs.
- **ES Modules**: Standard ES modules are the only permissible module system.

## 2. Component Organization (Holy Trinity)

Every complex component or feature slice must follow the **State/Render/Actions** (Holy Trinity) separation:

- **`state/`**: The single source of truth. It must not know about the DOM or CSS.
- **`render/`**: Responsible for manual DOM updates based on state changes. It does not contain business logic.
- **`actions/`**: Contains logic that transforms state and manages side effects (API calls, storage).

## 3. Lifecycle And Ownership Rules

- components should have one obvious lifecycle path from setup to mount to
  teardown
- all listeners, timers, observers, subscriptions, and reactive effects must be
  owned by the component lifecycle
- `disconnectedCallback` or the equivalent teardown path must clean up every
  owned side effect deterministically
- do not let detached components keep mutating global state, DOM nodes, or
  shared stores accidentally
- when a reactive system is used, prefer fine-grained updates over whole-tree
  rebuilds if that keeps behavior simpler and more diagnosable

## 4. Directory Structure

A standard component slice looks like this:
```text
my-component/
  index.js      (Public facade)
  component.js  (Class definition)
  content/      (Internal sub-modules)
  styles/       (CSS modules)
  state/        (State logic)
  render/       (DOM update logic)
  actions/      (Side-effects)
```

## 5. The Facade Rule

The `index.js` (or a file named after the component) is a small public facade. It should only glue the internal parts together and export the class or initialization function. It must not become a large "command center".

## 6. DOM, Events, And Contract Rules

- attribute and property mapping must be explicit and stable
- reflected attributes should exist only when they are part of the public
  component contract
- event names must be stable, intention-revealing, and documented through real
  usage or tests
- `CustomEvent` options such as `bubbles` and `composed` must be intentional,
  not copied blindly
- components must not reach deep into unrelated outside DOM as a hidden control
  channel

## 7. Styling Standards

- Use standard CSS variables for theme and shared tokens.
- Keep styles isolated (Shadow DOM or strict naming).
- No inline styles for layout; use classes and state-driven attributes.

## 8. Security And Accessibility Rules

- do not inject untrusted HTML into Shadow DOM or light DOM without deliberate
  sanitization
- slot contracts must be explicit enough that consumers know what content and
  semantics are allowed
- keyboard behavior, focus behavior, and ARIA semantics are part of the
  component contract
- components should fail safely when required attributes, state, or child
  structure are missing

## 9. Testing And Packaging Rules

- test lifecycle teardown, event contract, attribute or property mapping, and
  Shadow DOM isolation for serious components
- test browser behavior, not only internal helper functions
- publish only canonical source, types, docs, and intended artifacts; do not
  ship internal noise by accident
