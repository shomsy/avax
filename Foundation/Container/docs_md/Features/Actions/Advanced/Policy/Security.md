# Security

## Quick Summary
Security provides a fluent, chainable API for configuring container security policies, enabling fine-grained control over container behavior in security-sensitive applications. It supports strict mode enforcement, tag-based access control, and custom security rules that can be composed to create comprehensive security postures. This class acts as the primary interface for security policy configuration in advanced injection scenarios.

### For Humans: What This Means
Imagine Security as the master control panel for a high-security facility—the interface where you set all the access rules, alarm sensitivities, and security protocols. Instead of dealing with individual locks and cameras, you configure comprehensive security policies through a clean, chainable API. For containers, Security lets you define exactly how strict the container should be about security, who can access what, and what rules must be followed.

## Terminology (MANDATORY, EXPANSIVE)
**Security Policy**: A set of rules and configurations that define the security behavior of the container, including access controls, validation requirements, and enforcement mechanisms. In this file, policies are configured through the fluent API. It matters because it establishes the security boundaries for container operations.

**Strict Mode**: A heightened security configuration that enables comprehensive validation, mandatory access controls, and security-first defaults. In this file, strict() enables this mode. It matters because it provides defense-in-depth for security-critical applications.

**Tag-Based Access**: Security controls based on service tags that group related services under common access rules. In this file, allowTagged() implements this control. It matters because it enables scalable security management for service groups.

**Fluent Security DSL**: A domain-specific language for security configuration that allows method chaining for expressive policy definition. In this file, this is the core API pattern. It matters because it makes complex security configurations readable and maintainable.

### For Humans: What This Means
These are the security configuration vocabulary. Security policy is the rulebook. Strict mode is lockdown. Tag-based access is group permissions. Fluent security DSL is the easy configuration language.

## Think of It
Picture the security control room of a modern building where guards can configure access rules, alarm sensitivities, and emergency protocols through an intuitive touchscreen interface. Security is that touchscreen interface for container security—allowing you to define comprehensive security policies through simple, chainable method calls. Whether you need strict lockdown, tag-based permissions, or custom rules, the fluent API makes complex security configuration feel natural.

### For Humans: What This Means
This analogy shows why Security exists: user-friendly security configuration. Without it, setting up container security would require understanding complex policy objects and manual configuration. Security creates the intuitive interface that makes enterprise-grade security configuration accessible.

## Story Example
Before Security existed, configuring container security required direct manipulation of policy objects and manual rule setup. Developers had to understand the internal security architecture and write custom configuration code. With Security, security configuration became fluent and declarative. Complex security postures could be defined with readable method chains. A secure container configuration that previously required dozens of configuration calls could now be expressed in a few fluent method calls.

### For Humans: What This Means
This story illustrates the configuration complexity Security solves: manual security setup. Without it, container security was like programming individual security cameras and locks. Security creates the centralized control system that makes security configuration systematic and maintainable.

## For Dummies
Let's break this down like setting up parental controls on a smart TV:

1. **The Problem**: Container security needs complex configuration that can't be done with simple settings.

2. **Security's Job**: It's the advanced settings menu that lets you configure detailed security policies.

3. **How You Use It**: Chain methods to build up your security configuration step by step.

4. **What Happens Inside**: Each method configures different aspects of the security policy.

5. **Why It's Helpful**: You get enterprise-grade security configuration through an intuitive, chainable API.

Common misconceptions:
- "Security is just about encryption" - It's about access control, validation, and policy enforcement.
- "Security configuration is complex" - The fluent API makes it approachable.
- "Security is only for web apps" - It's essential for any application handling sensitive operations.

### For Humans: What This Means
Security isn't overwhelming complexity—it's organized protection. It takes the challenge of enterprise security configuration and makes it systematic through a clean, fluent interface. You get powerful security controls without becoming a security expert.

## How It Works (Technical)
Security acts as a fluent builder over ContainerPolicy, providing method chaining that configures security settings and returns the builder for continued configuration. Each method modifies the underlying policy object, enabling complex security postures through composable method calls.

### For Humans: What This Means
Under the hood, it's like a configuration wizard that updates a settings object with each step. You call methods that modify the security policy, and each returns the same object so you can keep configuring. It's a clean way to build up complex security configurations incrementally.

## Architecture Role
Security sits at the security configuration layer of the advanced injection system, providing the fluent API for policy configuration while delegating enforcement to the Guard system. It enables declarative security setup without coupling configuration to implementation.

### For Humans: What This Means
In the advanced injection architecture, Security is the configuration interface—the settings panel that defines security behavior while the actual enforcement happens elsewhere. It provides the user-friendly way to configure what the system should protect against.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ContainerPolicy $policy): void

#### Technical Explanation
Creates a new Security configuration instance with the provided policy object to configure.

##### For Humans: What This Means
This initializes the security configurator with the policy object you want to modify. It's like opening the security control panel for a specific building.

##### Parameters
- `ContainerPolicy $policy`: The policy instance to configure through this fluent interface.

##### Returns
- `void`: Constructor doesn't return anything; it just sets up the configuration interface.

##### Throws
- None. Constructor only stores the policy reference.

##### When to Use It
- When creating security configurations for container setup.
- In application bootstrap code for security policy configuration.
- When implementing security-focused container configurations.

##### Common Mistakes
- Passing null or invalid policy objects.
- Not understanding that the constructor doesn't modify the policy yet.

### Method: strict(): self

#### Technical Explanation
Enables strict security mode by configuring comprehensive validation, mandatory access controls, and security-first defaults in the underlying policy.

##### For Humans: What This Means
This turns on maximum security settings—like setting all alarms to their highest sensitivity and requiring multiple forms of authentication. The container becomes very cautious about what it allows.

##### Parameters
- None.

##### Returns
- `self`: The same Security instance for method chaining.

##### Throws
- None. Security configuration is designed to be safe.

##### When to Use It
- In high-security environments where maximum validation is required.
- When implementing security-critical applications.
- When you need defense-in-depth security measures.

##### Common Mistakes
- Using strict() in development environments where it might be too restrictive.
- Assuming strict() enables all possible security features (it enables a baseline set).
- Not testing applications thoroughly after enabling strict mode.

### Method: allowTagged(string $tag): self

#### Technical Explanation
Grants access permissions for services marked with the specified security tag, enabling tag-based access control for grouped service permissions.

##### For Humans: What This Means
This allows access to all services that have been labeled with a specific security tag. It's like giving someone a keycard that works for all doors marked with "executive floor"—they get access to the whole group at once.

##### Parameters
- `string $tag`: The security tag that identifies the group of services to allow access to.

##### Returns
- `self`: The same Security instance for method chaining.

##### Throws
- None. Tag configuration is designed to be safe.

##### When to Use It
- When implementing role-based access to groups of services.
- For tag-based security policies where services are grouped by function.
- When you need scalable security management for service categories.

##### Common Mistakes
- Using overly broad tags that grant too much access.
- Not properly tagging services consistently.
- Confusing allowTagged() with other access control methods.

## Risks, Trade-offs & Recommended Practices
**Risk**: Overly strict security can impact application performance.

**Why it matters**: Comprehensive validation and access controls add processing overhead.

**Design stance**: Balance security needs with performance requirements.

**Recommended practice**: Use strict() selectively and profile performance impact in production environments.

**Risk**: Tag-based access can become complex to manage.

**Why it matters**: As applications grow, tag relationships can become hard to track.

**Design stance**: Use clear, hierarchical tagging schemes.

**Recommended practice**: Document tag meanings and relationships, and audit tag usage regularly.

**Risk**: Security configuration can mask legitimate access needs.

**Why it matters**: Overly restrictive policies can break valid application functionality.

**Design stance**: Test security configurations thoroughly in staging environments.

**Recommended practice**: Implement gradual security rollout and monitor for access issues.

### For Humans: What This Means
Security provides powerful protection but requires careful configuration. The trade-offs between security and usability are real, so it's important to find the right balance for your application's needs. The key is configuring security thoughtfully and testing thoroughly.

## Related Files & Folders
**ContainerPolicy**: The underlying policy object that Security configures. You create policies separately and then configure them with Security. It provides the actual security enforcement that Security sets up.

**Guard/**: Contains the security enforcement system that uses policies configured by Security. You encounter guard components when security violations occur. It provides the runtime security that Security configures.

**Advanced/**: Contains Security as part of the advanced injection capabilities. You use security configuration in advanced scenarios. It provides the broader context for security-enhanced injection.

### For Humans: What This Means
Security works with a complete security ecosystem. ContainerPolicy provides the foundation, Guard does the enforcement, Advanced gives the context. Together they create comprehensive container security.