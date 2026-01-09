# GuardResolution

## Quick Summary
GuardResolution provides enterprise-grade security enforcement for container service resolution, implementing an immutable security guard that evaluates resolution attempts against configurable policies. It returns structured DTO responses instead of throwing exceptions, enabling graceful security handling while maintaining comprehensive audit trails and compliance reporting. This component serves as the critical security checkpoint that prevents unauthorized autowiring and ensures container operations remain within defined security boundaries.

### For Humans: What This Means
Imagine GuardResolution as the elite security team at a maximum-security facility—the highly trained professionals who check every person and package entering the building, making careful decisions about who gets in and who gets turned away. Instead of just saying "no" with an alarm, they provide detailed explanations and maintain complete records of every security decision. For containers, GuardResolution is that security team that checks every service resolution attempt and decides whether it's safe to proceed.

## Terminology (MANDATORY, EXPANSIVE)
**Security Guard**: An active security component that evaluates and potentially blocks operations based on security policies. In this file, GuardResolution serves this role for service resolution. It matters because it provides runtime security enforcement.

**Policy Evaluation**: The process of checking operations against configured security rules to determine allowance or denial. In this file, this occurs in the check() method. It matters because it ensures security compliance.

**Structured Response**: Using DTOs to return operation results instead of throwing exceptions, enabling graceful error handling. In this file, SuccessDTO and ErrorDTO provide this. It matters because it prevents application disruption.

**Immutable Security**: Security components that cannot be modified after creation, ensuring predictable and thread-safe behavior. In this file, the readonly design provides this. It matters because it prevents security bypasses.

**Audit Trail**: A complete record of security decisions and operations for compliance and analysis. In this file, structured responses enable audit trails. It matters because it supports regulatory compliance.

### For Humans: What This Means
These are the security guard vocabulary. Security guard is the checkpoint officer. Policy evaluation is the rule checking. Structured response is the detailed decision letter. Immutable security is the locked-down system. Audit trail is the security logbook.

## Think of It
Picture the TSA security checkpoint at an airport—trained professionals who evaluate every passenger and bag, make security decisions, provide clear feedback when things are denied, and maintain detailed records of all security activities. GuardResolution is that TSA checkpoint for dependency injection—evaluating every service resolution attempt, making security decisions, providing structured feedback, and maintaining comprehensive security records.

### For Humans: What This Means
This analogy shows why GuardResolution exists: professional security evaluation. Without it, service resolution would be like boarding a plane without security checks—potentially dangerous and without accountability. GuardResolution creates the professional security evaluation that makes container operations safe and auditable.

## Story Example
Before GuardResolution existed, security for service resolution was inconsistent and disruptive. Some security checks would throw exceptions that crashed applications, others provided minimal feedback, and audit trails were incomplete. With GuardResolution, security became professional and non-disruptive. Resolution attempts could be evaluated gracefully, detailed feedback provided for blocked operations, and comprehensive audit trails maintained. Container security became enterprise-ready.

### For Humans: What This Means
This story illustrates the inconsistent security problem GuardResolution solves: disruptive and incomplete security checks. Without it, security was like having different rules at different doors—confusing and potentially dangerous. GuardResolution creates the professional, consistent security system that makes container operations trustworthy.

## For Dummies
Let's break this down like airport security screening:

1. **The Problem**: Service resolution needs security evaluation without crashing the application.

2. **GuardResolution's Job**: It's the security checkpoint that evaluates and decides on service resolutions.

3. **How You Use It**: Create a guard with a policy and check service resolution permissions.

4. **What Happens Inside**: Policy evaluation occurs, structured responses are returned, audit information is preserved.

5. **Why It's Helpful**: You get professional security evaluation that doesn't break your application.

Common misconceptions:
- "GuardResolution throws exceptions" - It returns structured DTOs for graceful handling.
- "It's only for blocking" - It enables comprehensive security evaluation and auditing.
- "It's complex to use" - Simple check() method with clear structured responses.

### For Humans: What This Means
GuardResolution isn't disruptive—it's protective. It takes the critical job of security evaluation and makes it professional and non-disruptive. You get enterprise-grade security without breaking your application flow.

## How It Works (Technical)
GuardResolution implements an immutable security guard that evaluates service resolution attempts against a configured ResolutionPolicy. It returns structured SuccessDTO or ErrorDTO responses instead of throwing exceptions, enabling graceful security handling while preserving complete audit information.

### For Humans: What This Means
Under the hood, it's like a security checkpoint with a clear protocol. Check the policy, make a decision, return a structured response with all the details. No exceptions, no disruptions—just clear security decisions with full information.

## Architecture Role
GuardResolution sits at the security enforcement layer of the container architecture, providing active security evaluation while maintaining clean separation from policy definition. It enables runtime security decisions without coupling security logic to application flow.

### For Humans: What This Means
In the container's architecture, GuardResolution is the security evaluator—the professional who makes security decisions without being part of the main application flow. It provides the security expertise that keeps operations safe.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(ResolutionPolicy $policy): void

#### Technical Explanation
Creates a new GuardResolution instance with the specified security policy for evaluating service resolution attempts.

##### For Humans: What This Means
This sets up the security guard with the rules it should follow when checking service resolutions.

##### Parameters
- `ResolutionPolicy $policy`: The security policy that defines what resolutions are allowed.

##### Returns
- `void`: Constructor doesn't return anything; it just initializes the guard.

##### Throws
- None. Policy is stored for later evaluation.

##### When to Use It
- When creating security evaluation for container operations.
- In security setup and policy enforcement initialization.
- When implementing container security guards.

##### Common Mistakes
- Passing invalid or unconfigured policies.
- Expecting the constructor to validate the policy (it just stores it).

### Method: check(string $abstract): SuccessDTO|ErrorDTO

#### Technical Explanation
Evaluates whether service resolution is permitted for the given abstract identifier, returning a structured response indicating the security decision.

##### For Humans: What This Means
This is the main security check method. Give it a class name and it tells you whether the container is allowed to create instances of that class.

##### Parameters
- `string $abstract`: The fully qualified class or interface name to check.

##### Returns
- `SuccessDTO|ErrorDTO`: Structured response indicating whether resolution is allowed.

##### Throws
- None. Security decisions are communicated via return values.

##### When to Use It
- Before attempting service resolution in security-sensitive contexts.
- In container security enforcement and access control.
- When implementing security-aware service resolution.

##### Common Mistakes
- Treating ErrorDTO as an exception (it's a normal return value).
- Not checking the response type before accessing properties.
- Using check() for validation only (it provides audit information too).

## Risks, Trade-offs & Recommended Practices
**Risk**: Security evaluation overhead can impact performance in high-frequency resolution scenarios.

**Why it matters**: Every resolution check adds processing time, especially with complex policies.

**Design stance**: Optimize policy evaluation and cache results where appropriate.

**Recommended practice**: Profile security check performance and optimize policy implementations.

**Risk**: Structured responses can be ignored by calling code.

**Why it matters**: Applications might proceed with blocked resolutions if responses aren't checked.

**Design stance**: Make security checks mandatory and fail-fast for violations.

**Recommended practice**: Always check response types and handle ErrorDTO appropriately.

**Risk**: Immutable design prevents runtime policy updates.

**Why it matters**: Security policies might need dynamic adjustment in response to threats.

**Design stance**: Design policies to be comprehensive at creation time.

**Recommended practice**: Create new guard instances for policy changes rather than modifying existing ones.

### For Humans: What This Means
GuardResolution provides powerful security capabilities but requires careful integration. The performance and safety trade-offs are important considerations, but the structured approach makes security enforcement much more manageable than traditional exception-based security.

## Related Files & Folders
**ResolutionPolicy**: Defines the security rules that GuardResolution enforces. You configure policies separately and pass them to the guard. It provides the security logic that the guard evaluates.

**Enforce/**: Contains the broader enforcement system that GuardResolution is part of. You encounter the guard in enforcement contexts. It provides the security framework.

**SuccessDTO/ErrorDTO**: Provide the structured response types that GuardResolution returns. You use these to handle security decisions. They enable the structured, non-throwing security approach.

### For Humans: What This Means
GuardResolution works with a complete security ecosystem. The policy defines rules, the enforcement provides context, DTOs enable structured responses. Together they create professional container security.