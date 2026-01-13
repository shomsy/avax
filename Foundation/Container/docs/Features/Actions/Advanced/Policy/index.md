# Features/Actions/Advanced/Policy

## What This Folder Represents

This folder contains “policy-driven” resolution behaviors.

Technically, policy actions are runtime behaviors that shape how the container resolves services under certain
constraints (security rules, allow/deny lists, strictness, diagnostics policies). Instead of hardcoding those decisions
inside the resolver, they’re expressed as dedicated actions/flows so you can reason about them, test them, and swap
strategies.

### For Humans: What This Means (Represent)

It’s where the container learns to say “yes, no, or maybe” based on rules—like a bouncer at a club checking who’s
allowed in.

## What Belongs Here

- Policy “flows” or action classes that enforce resolution constraints.
- Security or governance-related decision logic that affects resolution outcomes.

### For Humans: What This Means (Belongs)

If it changes container behavior based on rules rather than types, it likely belongs here.

## What Does NOT Belong Here

- Core resolution pipeline mechanics (those live in `Core/Kernel`).
- Definition/registration rules (those live in `Features/Define`).

### For Humans: What This Means (Not Belongs)

This folder decides “should we resolve?”, not “how do we build?”.

## How Files Collaborate

Policy actions plug into the resolution flow (often as a pipeline step or a guard). They typically read contextual
information (what’s being resolved, who is asking, what environment you’re in) and then either allow resolution to
proceed or stop it with a clear error.

### For Humans: What This Means (Collaboration)

Policies are checkpoints: if you pass, resolution continues; if you fail, you get a clear reason.

