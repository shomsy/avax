# Features/Think/Prototype/Contracts

## What This Folder Represents
This folder defines stable contracts for prototype creation.

Technically, contracts in `Think/Prototype/Contracts` decouple the rest of the container from concrete factory implementations. That lets you swap factories (or decorate them) without rewriting the code that depends on “something that can produce a `ServicePrototype` for a class”.

### For Humans: What This Means (Represent)
It’s an agreement about what a “prototype factory” must be able to do, so you can change the implementation later without breaking users.

## What Belongs Here
- Interfaces for prototype factories.

### For Humans: What This Means (Belongs)
If it’s an interface that defines the shape of prototype creation, it belongs here.

## What Does NOT Belong Here
- Implementations (those are in `Think/Prototype`).
- Reflection details (those are in `Think/Analyze`).

### For Humans: What This Means (Not Belongs)
Contracts are just promises, not the actual work.

## How Files Collaborate
Implementations (like `ServicePrototypeFactory`) implement these contracts. Higher-level container components typehint the contracts, so they stay decoupled from specific factories.

### For Humans: What This Means (Collaboration)
Your app code depends on “a factory”, not “this specific factory”.

