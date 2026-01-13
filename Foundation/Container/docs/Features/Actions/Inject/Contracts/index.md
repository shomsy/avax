# Contracts

## What This Folder Represents

The dependency injection sub-system’s contracts: interfaces that define what injection components must do without
committing the kernel to a specific implementation. It exists so injection logic stays swappable and testable.

### For Humans: What This Means (Represent)

These are the “plug shapes” for injection. Implementations can change, but the rest of the container can still talk to
them the same way.

## What Belongs Here

Interfaces like `PropertyInjectorInterface` that define required injection behaviors.

### For Humans: What This Means (Belongs)

Only interfaces that define how injection components should behave belong here.

## What Does NOT Belong Here

Concrete implementations, resolvers, and result objects.

### For Humans: What This Means (Not Belongs)

No working code here—only the contracts.

## How Files Collaborate

`PropertyInjectorInterface` is implemented by `PropertyInjector` and consumed by `InjectDependencies`, keeping
orchestration decoupled from property resolution details.

### For Humans: What This Means (Collaboration)

One interface defines the rules, one class implements them, another class uses the interface.
