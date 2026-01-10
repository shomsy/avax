# Contracts

## What This Folder Represents

This folder contains the interface definitions and contracts that establish the architectural boundaries and behavioral expectations for the kernel subsystem. These contracts define the protocols for pipeline steps, telemetry collection, lifecycle management, and context handling, ensuring consistent implementation across different components while maintaining loose coupling. The contracts serve as the formal agreements that enable the kernel's modular, extensible architecture.

### For Humans: What This Means (Represent)

Think of this folder as the rulebook and blueprints for how all the kernel components should interact. Just as a construction project needs detailed specifications for electrical wiring, plumbing, and structural elements, the kernel needs clear contracts defining how resolution steps work, how telemetry is collected, and how contexts are managed. These aren't the actual workers—they're the specifications that ensure everyone follows the same standards.

## What Belongs Here

- Interface definitions for kernel components and subsystems
- Contract specifications for pipeline step behavior
- Type definitions for context and telemetry structures
- Abstract base classes that establish common behavior patterns
- Marker interfaces that identify component capabilities

### For Humans: What This Means (Belongs)

Anything that defines "how things should work" rather than "how things actually work" belongs here. If it's about specifying behavior, structure, or capabilities without implementing them, it should be in Contracts.

## What Does NOT Belong Here

- Concrete implementations of interfaces
- Business logic or algorithmic code
- Configuration classes or data structures
- Utility functions or helper classes
- Runtime execution code

### For Humans: What This Means (Not Belongs)

Don't put the actual working code here. Contracts are about the agreements and specifications, not the implementations. The implementations go elsewhere but must follow these contracts.

## How Files Collaborate

KernelStep defines the execution contract that all pipeline steps must follow. KernelContext establishes the data contract for resolution state. StepTelemetry defines the monitoring contract. LifecycleStrategy specifies the lifecycle management contract. TerminalKernelStep marks special step capabilities. Together they create a cohesive contract system that enables the kernel's pluggable architecture.

### For Humans: What This Means (Collab)

The contracts work together like a comprehensive legal framework. Each interface establishes rules for a different aspect of kernel behavior, and implementations must follow all applicable contracts. It's like having contracts for electricity, plumbing, and construction that ensure all subcontractors work together safely and effectively.
