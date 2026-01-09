# Features/Core

## What This Folder Represents
This folder contains the core building blocks that everything else in the Container component depends on: contracts (interfaces), small DTOs, attributes, enums, exceptions, and utilities. It exists to keep the container’s foundational vocabulary stable and reusable across the kernel, features, providers, guard, and observe layers.

### For Humans: What This Means
This is the container’s foundation layer. If you imagine the container as a building, these files are the concrete, beams, and standard parts that everything else is built from.

## What Belongs Here
- Public contracts that define what the container can do (`ContainerInterface`, `RegistryInterface`, `ResolverInterface`, etc.).
- Core exception types and error contracts.
- Small DTOs used to communicate results and diagnostic information.
- Attributes and enums used to express intent in code (like service lifetime).
- Utility helpers that are used across the component.

### For Humans: What This Means
If a file defines the container’s “language” (interfaces, errors, small value objects), it belongs here.

## What Does NOT Belong Here
- Concrete runtime behavior (kernel steps, engines, resolvers).
- Feature orchestration logic.
- Application-specific policies.

### For Humans: What This Means
Don’t put “the engine” here—put the rules, types, and shared primitives here.

## How Files Collaborate
Contracts define the APIs used across the system. Enums and attributes annotate intent (lifetime, injection points). Exceptions provide consistent failure signaling. DTOs carry structured outcomes. Utilities support common transformations. Together, they provide a stable base so higher layers can be implemented without circular dependencies.

### For Humans: What This Means
These pieces let the rest of the container talk the same language and fail the same way, so everything stays predictable.
