# Contracts

## What This Folder Represents
Interfaces that define the public contracts for the resolution subsystem (engine and parameter resolver). It exists to keep kernel steps and other features decoupled from specific implementations.

### For Humans: What This Means
These are the “plug shapes” for resolving services and arguments. Implementations can change without breaking the kernel.

## What Belongs Here
- `EngineInterface`: Contract for resolving a service from a `KernelContext`.
- `DependencyResolverInterface`: Contract for resolving parameter lists into argument arrays.

### For Humans: What This Means
If it defines how the resolution engine or dependency resolver should behave, it belongs here.

## What Does NOT Belong Here
Concrete implementations (`Engine`, `DependencyResolver`) and unrelated interfaces.

### For Humans: What This Means
No implementations here—only the contracts.

## How Files Collaborate
Kernel steps depend on `EngineInterface` while instantiation/invocation depend on `DependencyResolverInterface`. Concrete classes implement these and are wired into the container.

### For Humans: What This Means
The kernel talks to interfaces, and the container supplies the implementations.
