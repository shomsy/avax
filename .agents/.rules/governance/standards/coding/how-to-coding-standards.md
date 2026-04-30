# How To Coding Standards

Version: 1.1.0
Status: Normative

## Core Rules

- prefer simple, explicit code over clever code
- preserve architecture boundaries unless a tracked migration changes them
- keep one responsibility in one place
- prefer explicit names over generic buckets
- avoid hidden side effects
- do not silently change public behavior without updating contracts
- add tests or machine checks for changed behavior
- do not keep dead compatibility layers without a tracked reason

## Structure And Naming Law

- folder should say the flow or real capability
- file should say the owned responsibility
- function should say the exact action
- prefer flow-first and slice-first structure over default technical drawers such
  as `controllers`, `services`, `helpers`, or `utils`
- each feature root should have one obvious flow owner, facade, pipeline, or
  other small composition entry
- add subfolders only when they represent a real step, sub-capability, or a
  meaningful reduction in mental noise

## Responsibility And Boundary Rules

- isolate side effects at the edges and keep pure decision logic closer to the
  center
- keep transport, domain, persistence, rendering, and runtime wiring clearly
  separable when the project has those concerns
- shared code is the last option, not the first; extract to shared only when it
  is stable, generic, and truly without product meaning
- root slice files must stay small enough that a reviewer can see where the
  feature begins and how it is composed
- generated output, caches, or build artifacts must not become the hidden source
  of truth when canonical authored input exists elsewhere

## State And Effect Rules

- durable truth must live in an owned store or contract, not in accidental local
  process memory
- request-local or interaction-local state should stay local and disposable
- background or async work must define ownership, acknowledgement or completion
  behavior, retry limits, and failure quarantine where relevant
- event listeners, timers, subscriptions, observers, and reactive effects must
  be owned by a lifecycle boundary and cleaned up deterministically
- do not hide important mutation behind generic event buses, ambient registries,
  or magical singleton state

## Editing Rules

- prefer small, reviewable diffs
- keep comments short and useful
- preserve existing project conventions unless a tracked migration lane says
  otherwise
- avoid creating fallback buckets such as `utils`, `helpers`, `common`,
  `manager`, or `service`
- do not leave placeholder or fake implementation paths behind
