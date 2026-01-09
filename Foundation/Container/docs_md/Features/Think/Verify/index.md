# Features/Think/Verify

## What This Folder Represents
This folder validates prototypes before you trust them.

Technically, `Features/Think/Verify` contains services that check whether a `ServicePrototype` is safe and usable for dependency injection. Validation is where you turn “we found injection points” into “we’re confident this won’t explode at runtime”.

### For Humans: What This Means
It’s the quality-control department: it inspects the blueprint before you start building.

## What Belongs Here
- Prototype validators that throw clear, actionable errors when prototypes are invalid.

### For Humans: What This Means
If a class makes sure prototypes aren’t nonsense, it belongs here.

## What Does NOT Belong Here
- Prototype analysis (that’s `Think/Analyze`).
- Prototype caching (that’s `Think/Cache`).

### For Humans: What This Means
Validation doesn’t discover facts and it doesn’t store facts—it checks facts.

## How Files Collaborate
Analyzers produce prototypes, verifiers validate them, caches store them. Validation should happen before caching so you don’t persist invalid prototypes.

### For Humans: What This Means
You don’t want to save a broken blueprint and keep reusing it.

