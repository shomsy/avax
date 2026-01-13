# SecureServiceResolver

## Quick Summary

- This file wraps service resolution with security features: access policies, encrypted configuration handling,
  auditing, and “hardening” hooks.
- It exists so your container can enforce security constraints at the point where objects are actually handed out.
- It removes the complexity of sprinkling security checks across your app by giving you one secure entry point for
  resolution.

### For Humans: What This Means (Summary)

It’s a security guard for the container: before you get a service, it checks rules, logs what happened, and tries to
keep sensitive data protected.

## Terminology (MANDATORY, EXPANSIVE)

- **Secure resolution**: Resolving services while enforcing security controls.
    - In this file: `resolveSecure()` is the main secure resolution entry point.
    - Why it matters: “resolution” is the best choke point for access control.
- **Access policy**: A callable rule that decides if a service can be accessed.
    - In this file: policies are stored in `$accessPolicies` keyed by service id.
    - Why it matters: it’s the simplest way to represent “who can resolve what”.
- **Encrypted service**: A service that requires encryption/decryption of sensitive config data.
    - In this file: service ids are tracked in `$encryptedServices`.
    - Why it matters: secrets shouldn’t live in plaintext inside resolved objects.
- **Audit logging**: Recording security-relevant events for troubleshooting and compliance.
    - In this file: logging integration is done via `LoggerFactoryIntegration`.
    - Why it matters: without logs, you can’t prove or debug what happened.
- **Hardening**: Applying defensive changes or checks based on service type.
    - In this file: `hardenService()` contains type-based “enforce prepared statements / TLS / path restrictions” hooks.
    - Why it matters: it’s a place to apply safe defaults for risky components.
- **Reflection**: Inspecting objects/classes at runtime.
    - In this file: reflection is used to detect encrypted strings and scan methods.
    - Why it matters: reflection adds power but can add runtime cost.

### For Humans: What This Means (Terms)

This file is trying to keep “who can get what” and “how secrets are handled” under control.

## Think of It

Think of it like a bank teller window. You don’t just hand out cash (services). You check identity (policy), record the
transaction (audit), and you keep sensitive data in a safe (encryption).

### For Humans: What This Means (Think)

It’s not paranoia; it’s about making “dangerous things” predictable and trackable.

## Story Example

You have a `payment.gateway` service that contains API keys in configuration. You mark it as encrypted, and you set an
access policy that only allows resolution inside payment-processing contexts. When someone tries to resolve it outside
those contexts, it’s denied and logged. When it is resolved legitimately, its encrypted properties are decrypted into
safe runtime values.

### For Humans: What This Means (Story)

You prevent accidental leaks and you get a clear paper trail when someone tries something suspicious.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means (Dummies)

If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code
with confidence.

1. You call `resolveSecure($context)` instead of “resolve normally”.
2. It logs the attempt.
3. It checks a policy (if one exists).
4. It resolves the service.
5. If the service is marked encrypted, it decrypts protected data.
6. It records more logging/telemetry.

## How It Works (Technical)

The resolver keeps two registries: an access policy map and an encrypted-service list. `resolveSecure()` logs a
resolution attempt, checks access policy via `checkAccessPolicy()`, delegates to an internal resolver method, decrypts
config if the service is marked encrypted, then logs post-resolution security context. The class offers utilities for
encryption (`encryptServiceConfig()`), decryption (`decryptServiceConfig()`), security scanning (
`validateServiceSecurity()`), audit retrieval (`getSecurityAudit()`), and type-based hardening (`hardenService()`).

### For Humans: What This Means (How)

It’s basically “resolve + policies + encryption + logging” bundled together.

## Architecture Role

- Why this file lives in `Guard/Enforce`: it’s not the container core—it’s an enforcement wrapper you put around
  resolution.
- What depends on it: security-conscious bootstraps, admin tooling, and any environment that needs strict access
  control.
- What it depends on: encryption service, container config, logging integration, and resolution context.
- System-level reasoning: security is more reliable when enforced centrally at the boundary where services leave the
  container.

### For Humans: What This Means (Role)

If you enforce security in one place, you don’t have to remember to enforce it everywhere.

## Methods

This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means (Methods)

When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what
happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation (__construct)

Injects the encryption service, container configuration access, and logging integration.

##### For Humans: What This Means (__construct)

It grabs the tools it needs: a lockbox (encrypter), settings (config), and a notebook (logger).

##### Parameters (__construct)

- `$encrypter`: Encrypt/decrypt service for sensitive values.
- `$config`: Container config for security settings (if used by implementation).
- `$logger`: Logging integration for security events.

##### Returns (__construct)

- Nothing.

##### Throws (__construct)

- None directly.

##### When to Use It (__construct)

- When wiring secure resolution into your container.

##### Common Mistakes (__construct)

- Constructing without proper logger integration and then losing audit trails.

### Method: resolveSecure(…)

#### Technical Explanation (resolveSecure)

Performs secure resolution: logs, checks policy, resolves, decrypts if needed, and monitors access.

##### For Humans: What This Means (resolveSecure)

This is the “safe way” to ask for a service.

##### Parameters (resolveSecure)

- `$context`: A resolution context containing the service id/abstract and caller info.

##### Returns (resolveSecure)

- The resolved service instance.

##### Throws (resolveSecure)

- `RuntimeException` when access is denied.
- `ReflectionException` when reflection-based checks fail.

##### When to Use It (resolveSecure)

- For sensitive services (payment, crypto, filesystem, DB), or in high-security environments.

##### Common Mistakes (resolveSecure)

- Calling normal resolution and expecting security to be enforced “somewhere else”.

### Method: decryptServiceConfig(…)

#### Technical Explanation (decryptServiceConfig)

Walks object properties and decrypts values that look like encrypted payloads.

##### For Humans: What This Means (decryptServiceConfig)

It opens the lockbox and puts usable values back into the object.

##### Parameters (decryptServiceConfig)

- `$service`: The resolved service instance (or any value).

##### Returns (decryptServiceConfig)

- The service, potentially modified in-place if it’s an object with encrypted properties.

##### Throws (decryptServiceConfig)

- `ReflectionException` when reflection fails.

##### When to Use It (decryptServiceConfig)

- When you’ve marked a service as encrypted and want runtime values.

##### Common Mistakes (decryptServiceConfig)

- Expecting it to decrypt arbitrary formats; it uses heuristics.

### Method: setAccessPolicy(…)

#### Technical Explanation (setAccessPolicy)

Registers an access policy callable for a service id.

##### For Humans: What This Means (setAccessPolicy)

It sets the “who can access this?” rule.

##### Parameters (setAccessPolicy)

- `$serviceId`: The protected service id.
- `$policy`: Callable that receives context and returns allowed/denied.

##### Returns (setAccessPolicy)

- Nothing.

##### Throws (setAccessPolicy)

- None.

##### When to Use It (setAccessPolicy)

- During boot, when configuring security posture.

##### Common Mistakes (setAccessPolicy)

- Putting heavy I/O inside policies; they run on every resolution.

### Method: markAsEncrypted(…)

#### Technical Explanation (markAsEncrypted)

Marks a service id as requiring encrypted configuration handling.

##### For Humans: What This Means (markAsEncrypted)

It’s the “treat this service as sensitive” flag.

##### Parameters (markAsEncrypted)

- `$serviceId`: The sensitive service id.

##### Returns (markAsEncrypted)

- Nothing.

##### Throws (markAsEncrypted)

- None.

##### When to Use It (markAsEncrypted)

- When a service contains secrets or protected configuration values.

##### Common Mistakes (markAsEncrypted)

- Marking non-sensitive services and paying encryption overhead for no gain.

### Method: encryptServiceConfig(…)

#### Technical Explanation (encryptServiceConfig)

Encodes a config array to JSON and encrypts it.

##### For Humans: What This Means (encryptServiceConfig)

It turns normal config into “sealed config”.

##### Parameters (encryptServiceConfig)

- `$config`: Configuration array to encrypt.

##### Returns (encryptServiceConfig)

- An encrypted string payload.

##### Throws (encryptServiceConfig)

- `RuntimeException` when JSON encoding fails.

##### When to Use It (encryptServiceConfig)

- When storing sensitive config into persistence or logs safely.

##### Common Mistakes (encryptServiceConfig)

- Encrypting already-encrypted values and creating nested confusion.

### Method: validateServiceSecurity(…)

#### Technical Explanation (validateServiceSecurity)

Scans a service’s public methods and characteristics to flag security issues (dangerous methods, filesystem access).

##### For Humans: What This Means (validateServiceSecurity)

It tries to spot “this service can do scary things” patterns.

##### Parameters (validateServiceSecurity)

- `$serviceId`: Identifier for reporting context.
- `$service`: The resolved service object.

##### Returns (validateServiceSecurity)

- An array of security issue entries.

##### Throws (validateServiceSecurity)

- Reflection-related errors can bubble up depending on implementation.

##### When to Use It (validateServiceSecurity)

- Audits, diagnostics, and security-aware boot checks.

##### Common Mistakes (validateServiceSecurity)

- Treating these findings as proof of vulnerability; they’re signals, not verdicts.

### Method: getSecurityAudit(…)

#### Technical Explanation (getSecurityAudit)

Returns an audit trail snapshot for a service (implementation may be mock/stub).

##### For Humans: What This Means (getSecurityAudit)

It’s “show me the security history for this service”.

##### Parameters (getSecurityAudit)

- `$serviceId`: Service id.

##### Returns (getSecurityAudit)

- An array with audit information.

##### Throws (getSecurityAudit)

- None.

##### When to Use It (getSecurityAudit)

- Admin panels and incident investigations.

##### Common Mistakes (getSecurityAudit)

- Assuming this returns real audit data if the underlying audit store isn’t wired.

### Method: hardenService(…)

#### Technical Explanation (hardenService)

Applies type-based security hardening hooks (DB/HTTP/filesystem) to the service instance.

##### For Humans: What This Means (hardenService)

It tries to apply safe defaults when a service looks risky.

##### Parameters (hardenService)

- `$service`: Service instance to harden.

##### Returns (hardenService)

- The same service instance (potentially modified by hardening hooks).

##### Throws (hardenService)

- None directly.

##### When to Use It (hardenService)

- As a post-resolution safety step for infrastructure services.

##### Common Mistakes (hardenService)

- Expecting it to “magically secure” everything; hardening hooks must be implemented for your specific service types.

## Risks, Trade-offs & Recommended Practices

## Why This Design (And Why Not Others)

## Technical Explanation

Secure resolution is intentionally modeled as a dedicated component instead of “sprinkled” checks:

- **Why a separate security-aware resolver**: it localizes security heuristics and audit logging, and makes it possible
  to apply security only where it’s needed (sensitive services, strict environments).
- **Why not traits**: security behavior needs clear inputs (policies, allow/deny lists, context) and clear outputs (
  audited decisions). Traits would make those dependencies implicit.
- **Why not static/global security flags**: security decisions often depend on runtime context and configuration
  profiles. Static globals are hard to test and easy to misuse.
- **Why not silently mutate behavior**: this design prefers explicit denial/logging over hidden “fixups”, because hidden
  mutation makes incidents harder to investigate.

Trade-offs accepted intentionally:

- Extra overhead (reflection/scanning) in exchange for safer resolution and better audit trails

### For Humans: What This Means

Security works best when it’s obvious. This class exists so you can point to one place and say: “This is where we decide
what’s safe.”

- Risk: Heuristic detection (encrypted strings, dangerous methods) can false-positive/false-negative.
    - Why it matters: you can block legitimate behavior or miss real threats.
    - Design stance: treat heuristics as signals; log and alert rather than silently changing behavior.
    - Recommended practice: keep allowlists/denylists explicit and configurable; don’t rely only on heuristics.
- Trade-off: Reflection and encryption cost performance.
    - Why it matters: these operations add latency and CPU overhead.
    - Design stance: use secure resolution for sensitive services or environments; keep fast paths for non-sensitive
      cases.
    - Recommended practice: cache policies, limit scanning, and avoid doing heavy work on every resolution.

### For Humans: What This Means (Risks)

Security isn’t free. Use it where it matters, and make the costs visible.

## Related Files & Folders

- `docs_md/Guard/Enforce/ResolutionPolicy.md`: The policy concept this file complements (callables vs interface-based
  policies).
- `docs_md/Observe/Metrics/LoggerFactoryIntegration.md`: Used for audit-style logging.
- `docs_md/Observe/Timeline/ResolutionTimeline.md`: A related diagnostics tool for resolution behavior.

### For Humans: What This Means (Related)

This file enforces security; the Observe layer helps you see what’s happening.
