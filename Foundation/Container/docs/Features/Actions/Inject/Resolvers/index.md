# Resolvers

## What This Folder Represents
Small resolver/result helpers used by injection actions to express outcomes in a structured way. It exists to keep injection decisions explicit rather than relying on magic values or exceptions for normal control flow.

### For Humans: What This Means (Represent)
These files are the little “result objects” and helpers that make injection decisions easy to read and reason about.

## What Belongs Here
Objects like `PropertyResolution` that represent “resolved/unresolved” outcomes for injection decisions.

### For Humans: What This Means (Belongs)
If it represents an injection decision or outcome, it belongs here.

## What Does NOT Belong Here
Heavy orchestration (`InjectDependencies`) or concrete injection implementations (`PropertyInjector`).

### For Humans: What This Means (Not Belongs)
Keep this folder small and focused—just outcomes and tiny helpers.

## How Files Collaborate
`PropertyInjector` returns `PropertyResolution` for each `PropertyPrototype`. `InjectDependencies` uses those results to decide whether to set a property or leave it untouched.

### For Humans: What This Means (Collaboration)
PropertyInjector decides; PropertyResolution carries the decision; InjectDependencies applies it.
