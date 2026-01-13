# GuardPolicyStep

## Quick Summary

- Enforces security and policy rules before resolving a service.
- Delegates policy checks to `GuardResolution` and blocks resolution on violations.
- Writes policy-check metadata to the context so diagnostics can confirm enforcement occurred.

### For Humans: What This Means (Summary)

It’s the bouncer at the door: before the container builds anything, it checks whether you’re allowed to resolve that
service.

## Terminology (MANDATORY, EXPANSIVE)- **Policy enforcement**: Rules that decide whether a service may be resolved

- **GuardResolution**: Component that evaluates policies for a given service identifier.
- **ErrorDTO**: Structured error result returned by guard checks.
- **ContainerException**: Exception thrown when policy violations should block resolution.

### For Humans: What This Means

Policies are the rules; GuardResolution applies them; ErrorDTO is a “not allowed” result; ContainerException is the hard
stop.

## Think of It

Like airport security: you don’t get to the gate (resolution) until you pass the checkpoint (guard). If you fail, you’re
stopped before you waste everyone’s time.

### For Humans: What This Means (Think)

The step prevents forbidden resolutions early and cheaply.

## Story Example

A production system forbids resolving certain internal services directly. When code tries to resolve one,
`GuardPolicyStep` calls the guard, gets an `ErrorDTO`, and throws `ContainerException` with a clear message. No instance
construction happens.

### For Humans: What This Means (Story)

You get a clear “not allowed” error before the container does any expensive work.

## For Dummies

1. Skip if this is an injection-target resolution (special case).
2. Ask the guard to check the service ID.
3. If the guard returns an error, throw a container exception.
4. Otherwise, mark in metadata that policy checks ran.

Common misconceptions:

- “This validates dependencies.” It validates *permission to resolve*, not correctness of dependency graphs.

### For Humans: What This Means (Dummies)

It’s about access control, not about building the object correctly.

## How It Works (Technical)

`__invoke` calls `GuardResolution::check`. If the result is an `ErrorDTO`, it throws `ContainerException` with the
service ID and guard message. On success, it writes `policy.checked` and `policy.check_time` metadata.

### For Humans: What This Means (How)

It asks the guard “is this allowed?” and either stops or records success.

## Architecture Role

Runs early in the kernel pipeline as a security gate. Depends on `GuardResolution` from the Guard subsystem and
communicates outcomes via exceptions and metadata.

### For Humans: What This Means (Role)

It’s the security checkpoint of the container’s resolution pipeline.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(GuardResolution $guard)

#### Technical Explanation (__construct)

Stores the guard policy evaluator used during enforcement.

##### For Humans: What This Means (__construct)

Keeps the policy checker this step will call.

##### Parameters (__construct)

- `GuardResolution $guard`: Guard policy evaluator.

##### Returns (__construct)

- `void`

##### Throws (__construct)

- None.

##### When to Use It (__construct)

Constructed by the container when assembling kernel steps.

##### Common Mistakes (__construct)

Injecting a guard implementation that doesn’t match your policy expectations.

### Method: __invoke(KernelContext $context)

#### Technical Explanation (__invoke)

Checks whether the requested service can be resolved. Throws `ContainerException` if guard reports an `ErrorDTO`.
Records policy metadata on success.

##### For Humans: What This Means (__invoke)

If the service isn’t allowed, it stops resolution immediately.

##### Parameters (__invoke)

- `KernelContext $context`: Contains the service ID and metadata.

##### Returns (__invoke)

- `void`

##### Throws (__invoke)

- `ContainerException` when policy validation fails.

##### When to Use It (__invoke)

Executed automatically during resolution.

##### Common Mistakes (__invoke)

Assuming it will run for injection-target operations (it explicitly skips those).

## Risks, Trade-offs & Recommended Practices

## Why This Design (And Why Not Others)

## Technical Explanation

Policy enforcement is implemented as an explicit kernel step so it can run **early** and stay **visible**:

- **Why a dedicated step**: the pipeline can fail fast before instantiation/injection, and telemetry can attribute
  denials to a specific stage.
- **Why not traits**: policy enforcement needs clear collaborators (policy evaluators, error DTOs, exceptions). A step
  keeps those dependencies explicit and replaceable.
- **Why not static/global policy checks**: policies often depend on runtime context (environment, scope, caller intent).
  Global static checks are hard to test and easy to bypass.

Trade-offs accepted intentionally:

- A stricter flow (some resolutions are denied) in exchange for safety and auditability

### For Humans: What This Means (Design)

This step is the checkpoint. It’s easier to secure a system when you have one obvious place where “permission” is
decided — and you can log it.

- **Risk: Overblocking**. Too strict policies can break legitimate resolutions; test policies in CI.
- **Risk: Underblocking**. Too permissive policies can expose internals; review attack surfaces.
- **Practice: Keep error messages safe**. Don’t leak sensitive service details in exceptions.
- **Practice: Record metadata**. Use `policy.*` metadata for audit and diagnostics.

### For Humans: What This Means (Risks)

Balance security with usability, don’t leak secrets in errors, and keep enforcement observable.

## Related Files & Folders

- `docs_md/Core/Kernel/Steps/index.md`: Steps overview.
- `docs_md/Guard/Enforce/GuardResolution.md`: Guard evaluator used here.
- `docs_md/Features/Core/DTO/ErrorDTO.md`: Error structure returned by the guard.
- `docs_md/Features/Core/Exceptions/ContainerException.md`: Exception thrown on policy violations.

### For Humans: What This Means (Related)

Read GuardResolution to understand the rules, and check the ErrorDTO/ContainerException docs to see how violations are
represented.
