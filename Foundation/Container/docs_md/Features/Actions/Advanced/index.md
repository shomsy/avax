# Advanced

## What This Folder Represents
This folder contains sophisticated injection action implementations that handle complex dependency injection scenarios beyond standard constructor and method injection. It provides advanced techniques for conditional injection, lazy initialization, policy-based injection, and observable injection patterns. These advanced actions enable fine-grained control over the injection process for enterprise applications with complex requirements.

### For Humans: What This Means
Think of this folder as the advanced injection laboratory—the cutting-edge techniques for when standard injection just isn't enough. While basic injection covers the everyday needs, Advanced provides the specialized procedures for complex medical cases. It's like having advanced surgical tools and techniques available when the standard treatments need enhancement.

## Terminology (MANDATORY, EXPANSIVE)
**Conditional Injection**: Injection that occurs only under specific runtime conditions or configurations. In this folder, actions can implement conditional logic. It matters because it enables context-aware dependency provision.

**Lazy Injection**: Deferring dependency injection until the dependency is actually accessed or needed. In this folder, Lazy subfolder handles this pattern. It matters because it optimizes startup performance and resource usage.

**Policy-Based Injection**: Injection controlled by predefined policies or rules that determine how and when injection occurs. In this folder, Policy subfolder implements this. It matters because it enables governance and compliance in injection behavior.

**Observable Injection**: Injection actions that can be monitored, logged, or trigger events during the injection process. In this folder, Observe subfolder provides this capability. It matters because it enables debugging and auditing of injection operations.

**Injection Metadata**: Additional information attached to injection operations that influences behavior or enables introspection. In this folder, actions can use metadata for advanced control. It matters because it enables sophisticated injection customization.

### For Humans: What This Means
These are the advanced injection vocabulary. Conditional injection is selective treatment. Lazy injection is on-demand delivery. Policy-based injection follows strict protocols. Observable injection leaves a trail. Metadata provides extra context.

## Think of It
Imagine a specialized hospital wing where doctors use advanced techniques for complex cases—targeted radiation therapy, genetic treatments, robotic surgery, and experimental protocols. The Advanced folder is that specialized wing for dependency injection—the place where standard procedures are enhanced with cutting-edge techniques for the most challenging injection scenarios.

### For Humans: What This Means
This analogy shows why Advanced exists: sophisticated injection solutions. Without it, complex dependency scenarios would require manual workarounds or custom implementations. Advanced creates the specialized techniques that make enterprise-grade injection possible.

## Story Example
Before Advanced actions existed, complex injection scenarios required manual implementation or complex workarounds. Conditional injection based on runtime state, lazy initialization of expensive dependencies, and policy-controlled injection all had to be handled manually. With Advanced actions, these sophisticated patterns became reusable components. A complex application with conditional dependencies could now use standardized advanced actions instead of custom injection logic.

### For Humans: What This Means
This story illustrates the complexity problem Advanced solves: manual advanced injection. Without it, sophisticated dependency scenarios were time-consuming and error-prone. Advanced creates the professional techniques that make complex injection manageable.

## For Dummies
Let's break this down like advanced cooking techniques:

1. **The Problem**: Standard recipes work for basic meals, but complex dishes need advanced techniques.

2. **Advanced's Job**: It's the culinary laboratory with molecular gastronomy, sous-vide, and experimental techniques.

3. **How You Use It**: Choose the appropriate advanced action for your complex injection scenario.

4. **What Happens Inside**: Lazy defers creation, Policy enforces rules, Observe provides monitoring.

5. **Why It's Helpful**: It enables sophisticated dependency injection for complex enterprise applications.

Common misconceptions:
- "Advanced actions are too complex" - They're focused tools for specific advanced needs.
- "Advanced actions slow everything down" - They're optimized for performance.
- "Advanced actions are rarely needed" - They're essential for enterprise applications.

### For Humans: What This Means
Advanced isn't overwhelming complexity—it's specialized capability. It takes the advanced injection challenges and solves them with focused, powerful tools. You get enterprise-grade injection without the complexity overhead.

## How It Works (Technical)
The Advanced folder contains specialized injection action implementations that extend the base injection framework with advanced capabilities. Each subfolder provides a specific advanced injection pattern implemented as reusable action classes that integrate with the resolution pipeline.

### For Humans: What This Means
Under the hood, it's like a collection of specialized injection modules. Each module provides a specific advanced capability that plugs into the standard injection framework. They work through well-defined interfaces to extend injection capabilities without breaking compatibility.

## Architecture Role
Advanced sits at the specialization layer of the injection actions, providing advanced capabilities that extend the core injection framework while remaining optional. It defines the extension points for sophisticated injection patterns.

### For Humans: What This Means
In the injection actions architecture, Advanced is the research and development wing—the place where new injection techniques are developed and refined. It provides the advanced capabilities while maintaining compatibility with the core system.

## What Belongs Here
- Conditional and context-aware injection actions
- Lazy initialization and deferred injection implementations
- Policy-controlled and rules-based injection actions
- Observable and auditable injection actions
- Metadata-driven injection customizations
- Advanced injection strategy implementations

### For Humans: What This Means
Anything that implements sophisticated injection techniques beyond basic injection belongs here. If it's an advanced pattern for complex dependency scenarios, it should be in Advanced.

## What Does NOT Belong Here
- Basic injection actions (belong in parent Actions/)
- Core resolution mechanics (belong in Core/)
- Standard service registration (belongs in main Container)
- User interfaces (belongs in application)
- Business logic (belongs in application)

### For Humans: What This Means
Don't put standard injection here. Advanced is for sophisticated techniques that go beyond the basics, not replacements for standard functionality.

## How Files Collaborate
Lazy provides deferred injection, Policy enforces rules, Observe enables monitoring, and all work together to create comprehensive advanced injection capabilities. Actions can combine multiple advanced techniques for complex scenarios.

### For Humans: What This Means
The Advanced actions collaborate like a medical team. Lazy handles timing, Policy sets protocols, Observe provides oversight. They work together to deliver sophisticated, well-controlled injection solutions.