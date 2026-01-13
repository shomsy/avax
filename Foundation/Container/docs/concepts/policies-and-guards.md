# Policies and Guards

## Technical Explanation (Summary)

Policies and guards are the container’s **safety layer**. They exist because “can be resolved” is not the same as
“should be resolved”. A guard system lets you express rules like:

- “Only allow these classes to be resolved”
- “Reject services that depend on forbidden types”
- “Require stricter validation in production”

In this component, you typically see policies enforced during the kernel flow (as a step) and implemented in dedicated
guard types under `Guard/`.

### For Humans: What This Means (Summary)

Guards are your container’s bouncer. Just because something is on the guest list (registered) doesn’t mean it gets into
every room (every context).

## How Policy Enforcement Fits the Flow

### Technical Explanation (Flow)

Policy checks are designed to happen *before expensive work* (like instantiation) so that unsafe resolutions fail fast:

- Kernel enforcement point: `Core/Kernel/Steps/GuardPolicyStep.php`
- Guard concepts and orchestration: `Guard/Enforce/*`
- Rules and validation: `Guard/Rules/*`

This split exists so you can:

- Keep the kernel generic (it just runs a step)
- Implement policy logic independently (rules can evolve without changing the kernel)

### For Humans: What This Means (Flow)

The kernel doesn’t want to “know security”. It just asks, “Are we allowed to do this?” and lets the guard subsystem
answer.

## Typical Policy Shapes in This Codebase

### Technical Explanation (Shapes)

You’ll commonly find:

- **Resolution policies**: decide whether a service may be resolved in the current context  
  Examples: `Guard/Enforce/ResolutionPolicy.php`, `Guard/Enforce/StrictResolutionPolicy.php`,
  `Guard/Enforce/CompositeResolutionPolicy.php`

- **Validation rules**: inspect service definitions and dependency graphs  
  Examples: `Guard/Rules/ContainerPolicy.php`, `Guard/Rules/DependencyValidationRule.php`,
  `Guard/Rules/ServiceValidationRule.php`, `Guard/Rules/ServiceValidator.php`

- **Security-oriented resolution**: wrappers that enforce safety boundaries  
  Example: `Guard/Enforce/SecureServiceResolver.php`

### For Humans: What This Means (Shapes)

Think of “policies” as rules of permission, and “rules/validators” as rules of correctness.

## Risks, Trade-offs & Recommended Practices

### Technical Explanation (Risks)

- **Risk: policies become accidental Service Locator gates**  
  *Why it matters*: you start encoding app logic into the container.  
  *Mitigation*: keep policies about safety/architecture constraints, not business decisions.

- **Trade-off: stricter rules reduce flexibility**  
  *Why it matters*: you may block dynamic patterns used in tests or plugins.  
  *Mitigation*: use profiles/environments; allow relaxed policies in dev/testing when appropriate.

### For Humans: What This Means (Risks)

Guards should protect you from foot-guns, not micromanage your app. If the container starts feeling like “the boss”, the
policy layer is probably too strict.

## Related Files & Jump Links

- Kernel policy step: `../Core/Kernel/Steps/GuardPolicyStep.md`
- Guard enforcement: `../Guard/Enforce/index.md`
- Guard rules: `../Guard/Rules/index.md`

### For Humans: What This Means (Links)

If resolution is being rejected, start at the kernel policy step doc, then jump to the specific policy/rule docs to see
what’s being enforced.
