# Actions

## What This Folder Represents

This folder contains specialized injection action components that implement various dependency injection techniques
beyond basic constructor injection. Each subfolder provides a specific type of injection capability, from advanced
injection strategies to specific injection targets like methods or properties. These actions enable sophisticated
dependency injection scenarios while maintaining clean separation from the core resolution mechanics.

### For Humans: What This Means (Represent)

Think of this folder as the specialized injection toolkit—the precision instruments for complex dependency scenarios.
While basic constructor injection covers most cases, Actions provides the specialized tools for when you need to inject
dependencies into specific methods, handle advanced scenarios, or perform custom injection logic. It's like having
specialized medical equipment beyond the basic syringe.

## Terminology (MANDATORY, EXPANSIVE)

**Injection Actions**: Specialized components that perform specific types of dependency injection with custom logic. In
this folder, each subfolder implements different action types. It matters because it enables complex injection scenarios
beyond constructor injection.

**Advanced Injection**: Sophisticated injection techniques that go beyond basic parameter resolution. In this folder,
Advanced subfolder contains these. It matters because it handles complex dependency scenarios.

**Method Injection**: The process of injecting dependencies into specific methods rather than constructors. In this
folder, Invoke subfolder handles this. It matters because it enables runtime dependency provision.

**Property Injection**: Injecting dependencies directly into object properties. In this folder, Inject subfolder may
contain this. It matters because it enables flexible dependency assignment.

**Instantiation Actions**: Components that control how objects are created during dependency injection. In this folder,
Instantiate subfolder handles this. It matters because it enables custom object creation logic.

**Resolution Actions**: Components that participate in the service resolution process. In this folder, Resolve subfolder
contains these. It matters because it enables custom resolution logic.

### For Humans: What This Means (Terms)

These are the injection action vocabulary. Injection actions are specialized injectors. Advanced injection is complex
techniques. Method injection targets specific methods. Property injection goes directly to properties. Instantiation
actions control creation. Resolution actions participate in resolution.

## Think of It

Imagine a hospital pharmacy with different types of delivery systems—oral medications, intravenous drips, topical
creams, inhalation devices, injections. The Actions folder is that specialized pharmacy—the different delivery
mechanisms for providing dependencies to objects. Each type of action is optimized for a specific delivery scenario,
ensuring dependencies reach their targets effectively and safely.

### For Humans: What This Means (Think)

This analogy shows why Actions exists: specialized delivery mechanisms. Without it, all dependency injection would use
the same basic approach, unable to handle diverse scenarios. Actions creates the specialized delivery systems that make
sophisticated dependency injection possible.

## Story Example

Before Actions existed, complex injection scenarios required manual implementation or workarounds. Method injection,
property injection, and advanced resolution strategies all had to be handled manually. With Actions, these became
standardized, reusable components. An application that previously required custom injection logic for each complex
scenario could now use pre-built actions with simple configuration.

### For Humans: What This Means (Story)

This story illustrates the standardization problem Actions solves: scattered injection logic. Without it, advanced
dependency injection was like having custom delivery methods for every medication—time-consuming and error-prone.
Actions creates the standardized delivery protocols that make complex injection manageable.

## For Dummies

Let's break this down like different ways to administer medication:

1. **The Problem**: Basic injection covers simple cases, but complex scenarios need specialized approaches.

2. **Actions' Job**: It's the pharmacy with different delivery methods for different needs.

3. **How You Use It**: Choose the appropriate action type for your injection scenario.

4. **What Happens Inside**: Advanced handles complex logic, Inject targets properties, Invoke targets methods,
   Instantiate controls creation, Resolve participates in resolution.

5. **Why It's Helpful**: It makes sophisticated dependency injection scenarios possible and manageable.

Common misconceptions:

- "Actions are required" - They're optional for advanced injection scenarios.
- "Actions replace basic injection" - They complement and extend basic injection.
- "Actions are complex" - They're focused tools for specific injection needs.

### For Humans: What This Means (Dummies)

Actions isn't overwhelming complexity—it's organized specialization. It takes the problem of advanced dependency
injection and solves it with focused, purpose-built tools. You get sophisticated injection capabilities without
complexity overhead.

## How It Works (Technical)

The Actions folder implements a categorized architecture where each subfolder provides a specific injection capability.
Components implement common interfaces and can be composed with the resolution pipeline. Each action type handles a
different aspect of the injection process, from instantiation to method invocation.

### For Humans: What This Means (How)

Under the hood, it's like a categorized medicine cabinet. Each drawer has tools for a specific type of delivery.
Advanced injection has complex techniques, method injection targets methods, property injection targets properties.
Everything works through standard interfaces to create flexible injection capabilities.

## Architecture Role

Actions sits at the specialization layer of the injection system, providing advanced capabilities that extend the core
injection mechanisms. It defines the extension points for custom injection logic while maintaining compatibility with
the standard resolution pipeline.

### For Humans: What This Means (Role)

In the container's architecture, Actions is the specialization clinic—the advanced treatment center for complex
injection cases. It provides the specialized capabilities while working within the overall healthcare system.

## What Belongs Here

- Advanced injection strategy implementations
- Method and property injection components
- Custom instantiation logic
- Resolution participation components
- Injection action interfaces and base classes
- Specialized injection behavior implementations

### For Humans: What This Means (Belongs)

Anything that implements specialized dependency injection techniques belongs here. If it's an advanced way to inject
dependencies into objects, it should be in Actions.

## What Does NOT Belong Here

- Core resolution mechanics (belongs in Core/)
- Basic service registration (belongs in main Container)
- Configuration management (belongs in Config/)
- Security policies (belongs in Guard/)
- User interfaces (belongs in main application)

### For Humans: What This Means (Not Belongs)

Don't put fundamental container operations here. Actions is for specialized injection techniques that enhance the core,
not replace it.

## How Files Collaborate

Advanced provides sophisticated injection strategies, Inject handles property injection, Invoke manages method
injection, Instantiate controls object creation, and Resolve participates in the resolution process. They work together
through common interfaces to enable complex injection scenarios.

### For Humans: What This Means (Collaboration)

The Actions components collaborate like a medical team. Advanced strategies provide the complex treatments, method
injection targets specific procedures, property injection delivers to specific sites. They coordinate through standard
protocols to deliver comprehensive injection capabilities.