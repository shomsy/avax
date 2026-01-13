# Core

## What This Folder Represents

This folder contains the fundamental building blocks of the Container system, implementing the core dependency injection
machinery that powers the entire component. It houses the kernel implementation, configuration management, and the
sophisticated resolution pipeline that handles object creation and dependency wiring. The Core components provide the
low-level infrastructure that makes dependency injection possible, abstracting away complexity while ensuring
performance, reliability, and extensibility.

### For Humans: What This Means (Represent)

Think of this folder as the engine room of a ship. While the main Container class is the bridge where you steer, the
Core components are the powerful machinery below deck that makes everything work. It's where the real magic happens—the
complex algorithms and data structures that turn simple requests into fully assembled objects with all their
dependencies satisfied.

## Terminology (MANDATORY, EXPANSIVE)

**Dependency Injection Kernel**: The central processing unit of the container system that orchestrates all dependency
resolution activities. In this folder, ContainerKernel serves this role. It matters because it coordinates the complex
interactions between all container components.

**Resolution Pipeline**: A structured sequence of processing stages that systematically transforms service requests into
fully instantiated objects. In this folder, ResolutionPipeline implements this pattern. It matters because it provides a
modular, testable approach to complex object construction.

**Service Definition**: A declarative description of how a service should be created, including its class, dependencies,
and lifecycle. In this folder, ContainerConfig and related classes manage these definitions. It matters because it
separates service configuration from implementation.

**Kernel Context**: A mutable data structure that carries resolution state and metadata through the pipeline stages. In
this folder, KernelContext provides this functionality. It matters because it enables communication between pipeline
steps without tight coupling.

**Lifecycle Strategy**: An algorithm that determines how service instances are created, shared, and disposed of. In this
folder, lifecycle components implement these strategies. It matters because it controls memory usage and service
isolation.

**Resolution Scope**: A bounded context for service lifetimes that allows different instances in different contexts. In
this folder, scope management components handle this. It matters because it enables proper resource management in
complex applications.

### For Humans: What This Means (Terms)

These are the core dependency injection vocabulary. The kernel is the brain coordinating everything. The resolution
pipeline is the assembly line. Service definitions are the blueprints. Kernel context is the work order. Lifecycle
strategies are the rules for sharing. Resolution scopes are the boundaries for different contexts.

## Think of It

Imagine a sophisticated factory floor where raw materials enter one end and finished products emerge from the other,
with each workstation performing a specialized operation in perfect sequence. The Core folder is that factory—the
intricate machinery and control systems that transform simple service requests into complex, fully-wired object graphs.
Each component is a precision tool designed for its specific role in the dependency injection process.

### For Humans: What This Means (Think)

This analogy shows why Core exists: to provide the manufacturing capability for dependency injection. Without it, each
service request would require manual assembly of all dependencies, leading to error-prone and maintenance-heavy code.
Core creates the automated production line that makes sophisticated object composition reliable and efficient.

## Story Example

Before the Core components existed, dependency injection was implemented through manual factory methods and constructor
calls scattered throughout applications. Each service creation required explicit knowledge of its dependencies and their
creation order. With the Core system, dependency resolution became automatic and declarative. A complex object graph
that once required dozens of manual instantiation steps now resolves automatically through the kernel's orchestration.

### For Humans: What This Means (Story)

This story illustrates the automation problem Core solves: manual dependency management. Without it, dependency
injection was like assembling furniture without instructions—possible but tedious and error-prone. Core creates the
intelligent assembly system that makes complex object graphs manageable and reliable.

## For Dummies

Let's break this down like understanding how a car engine works:

1. **The Problem**: Objects need other objects to work, but connecting them manually is complex and error-prone.

2. **Core's Job**: It's the engine that automatically figures out what objects need and connects them correctly.

3. **How You Use It**: You describe what services exist and how they relate; Core handles the actual connection logic.

4. **What Happens Inside**: The kernel analyzes requests, the pipeline builds objects step-by-step, and contexts track
   the process.

5. **Why It's Helpful**: It turns complex object wiring into simple declarations, making applications more maintainable.

Common misconceptions:

- "Core is just the kernel" - It encompasses the entire foundational infrastructure for dependency injection.
- "It's monolithic" - It's composed of focused, interchangeable components working together.
- "It's only for instantiation" - It handles the complete lifecycle from creation to cleanup.

### For Humans: What This Means (Dummies)

Core isn't just components—it's the foundational intelligence that makes dependency injection work. It takes the
fundamental problem of connecting objects and solves it with systematic, reliable infrastructure. You get sophisticated
object management without understanding the internal complexity.

## How It Works (Technical)

The Core folder implements a layered architecture with ContainerKernel at the top coordinating ContainerConfig and the
Kernel subsystem. The kernel uses a resolution pipeline that processes requests through sequential steps, each handling
a specific aspect of dependency injection. Contexts carry state through the pipeline, while lifecycle strategies
determine instance management. Configuration influences all behavior through ContainerConfig.

### For Humans: What This Means (How)

Under the hood, it's like a well-designed assembly line. The kernel is the foreman coordinating the work. Configuration
is the instruction manual. The pipeline is the conveyor belt with specialized stations. Contexts are the work orders.
Lifecycle strategies are the quality control rules. Everything works together to produce consistent, correct results.

## Architecture Role

Core sits at the foundation of the container architecture, providing the essential mechanisms that enable dependency
injection while remaining independent of specific features or integrations. It defines the core protocols and data
structures that all other components build upon, ensuring consistency and interoperability across the entire system.

### For Humans: What This Means (Role)

In the container's architecture, Core is the bedrock—the fundamental layer that everything else rests upon. It provides
the essential capabilities that make dependency injection possible, without being tied to specific use cases or external
integrations.

## What Belongs Here

- Kernel implementations that orchestrate dependency resolution
- Configuration classes that define container behavior and settings
- Resolution pipeline components that handle the step-by-step object creation process
- Core contracts and interfaces that define the system's architecture
- Fundamental data structures for service definitions and contexts
- Lifecycle management components for object initialization and cleanup

### For Humans: What This Means (Belongs)

Anything that's essential to how dependency injection fundamentally works belongs here. If it's about the core mechanics
of creating objects and connecting them together, it should be in Core. This isn't about fancy features or user
interfaces—it's about the foundational technology that makes the container possible.

## What Does NOT Belong Here

- User-facing APIs (those belong in the main Container class)
- Feature extensions or specialized injection logic
- Security policies or validation rules
- Monitoring and observability tools
- Service providers or third-party integrations
- Testing utilities or development tools

### For Humans: What This Means (Not Belongs)

Don't put application-specific features or user conveniences here. Core is for the unchanging fundamentals—the laws of
physics for dependency injection. Everything else builds on top of this foundation but doesn't live inside it.

## How Files Collaborate

ContainerKernel serves as the central orchestrator, coordinating with ContainerConfig for settings and the Kernel
subsystem for complex resolution logic. The kernel delegates to specialized components within the Kernel/ folder for
different aspects of the resolution process. Configuration flows from ContainerConfig to influence kernel behavior,
while the kernel provides the main integration point for the public Container API.

### For Humans: What This Means (Collab)

The files in Core work together like a well-choreographed team. ContainerKernel is the conductor, ContainerConfig is the
rulebook, and the Kernel folder contains the specialized musicians. They communicate through well-defined interfaces,
each handling their part of the dependency injection symphony without stepping on each other's toes.
