# SecureServiceResolver

## Quick Summary
- This file wraps service resolution with security features: access policies, encrypted configuration handling, auditing, and “hardening” hooks.
- It exists so your container can enforce security constraints at the point where objects are actually handed out.
- It removes the complexity of sprinkling security checks across your app by giving you one secure entry point for resolution.

### For Humans: What This Means
It’s a security guard for the container: before you get a service, it checks rules, logs what happened, and tries to keep sensitive data protected.

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

### For Humans: What This Means
This file is trying to keep “who can get what” and “how secrets are handled” under control.

## Think of It
Think of it like a bank teller window. You don’t just hand out cash (services). You check identity (policy), record the transaction (audit), and you keep sensitive data in a safe (encryption).

### For Humans: What This Means
It’s not paranoia; it’s about making “dangerous things” predictable and trackable.

## Story Example
You have a `payment.gateway` service that contains API keys in configuration. You mark it as encrypted, and you set an access policy that only allows resolution inside payment-processing contexts. When someone tries to resolve it outside those contexts, it’s denied and logged. When it is resolved legitimately, its encrypted properties are decrypted into safe runtime values.

### For Humans: What This Means
You prevent accidental leaks and you get a clear paper trail when someone tries something suspicious.

## For Dummies

This section gives you a slow, step-by-step mental model and a beginner-safe walkthrough of what the file does.

### For Humans: What This Means
If you’re new to this area, read this first. It helps you avoid getting lost in terminology and lets you use the code with confidence.

1. You call `resolveSecure($context)` instead of “resolve normally”.
2. It logs the attempt.
3. It checks a policy (if one exists).
4. It resolves the service.
5. If the service is marked encrypted, it decrypts protected data.
6. It records more logging/telemetry.

## How It Works (Technical)
The resolver keeps two registries: an access policy map and an encrypted-service list. `resolveSecure()` logs a resolution attempt, checks access policy via `checkAccessPolicy()`, delegates to an internal resolver method, decrypts config if the service is marked encrypted, then logs post-resolution security context. The class offers utilities for encryption (`encryptServiceConfig()`), decryption (`decryptServiceConfig()`), security scanning (`validateServiceSecurity()`), audit retrieval (`getSecurityAudit()`), and type-based hardening (`hardenService()`).

### For Humans: What This Means
It’s basically “resolve + policies + encryption + logging” bundled together.

## Architecture Role
- Why this file lives in `Guard/Enforce`: it’s not the container core—it’s an enforcement wrapper you put around resolution.
- What depends on it: security-conscious bootstraps, admin tooling, and any environment that needs strict access control.
- What it depends on: encryption service, container config, logging integration, and resolution context.
- System-level reasoning: security is more reliable when enforced centrally at the boundary where services leave the container.

### For Humans: What This Means
If you enforce security in one place, you don’t have to remember to enforce it everywhere.

## Methods (MANDATORY)


This section is the API map of the file: it documents what each method does, why it exists, and how you should use it.

### For Humans: What This Means
When you’re trying to use or debug this file, this is the part you’ll come back to. It’s your “what can I call, and what happens?” cheat sheet.

### Method: __construct(…)

#### Technical Explanation
Injects the encryption service, container configuration access, and logging integration.

##### For Humans: What This Means
It grabs the tools it needs: a lockbox (encrypter), settings (config), and a notebook (logger).

##### Parameters
- `$encrypter`: Encrypt/decrypt service for sensitive values.
- `$config`: Container config for security settings (if used by implementation).
- `$logger`: Logging integration for security events.

##### Returns
- Nothing.

##### Throws
- None directly.

##### When to Use It
- When wiring secure resolution into your container.

##### Common Mistakes
- Constructing without proper logger integration and then losing audit trails.

### Method: resolveSecure(…)

#### Technical Explanation
Performs secure resolution: logs, checks policy, resolves, decrypts if needed, and monitors access.

##### For Humans: What This Means
This is the “safe way” to ask for a service.

##### Parameters
- `$context`: A resolution context containing the service id/abstract and caller info.

##### Returns
- The resolved service instance.

##### Throws
- `RuntimeException` when access is denied.
- `ReflectionException` when reflection-based checks fail.

##### When to Use It
- For sensitive services (payment, crypto, filesystem, DB), or in high-security environments.

##### Common Mistakes
- Calling normal resolution and expecting security to be enforced “somewhere else”.

### Method: decryptServiceConfig(…)

#### Technical Explanation
Walks object properties and decrypts values that look like encrypted payloads.

##### For Humans: What This Means
It opens the lockbox and puts usable values back into the object.

##### Parameters
- `$service`: The resolved service instance (or any value).

##### Returns
- The service, potentially modified in-place if it’s an object with encrypted properties.

##### Throws
- `ReflectionException` when reflection fails.

##### When to Use It
- When you’ve marked a service as encrypted and want runtime values.

##### Common Mistakes
- Expecting it to decrypt arbitrary formats; it uses heuristics.

### Method: setAccessPolicy(…)

#### Technical Explanation
Registers an access policy callable for a service id.

##### For Humans: What This Means
It sets the “who can access this?” rule.

##### Parameters
- `$serviceId`: The protected service id.
- `$policy`: Callable that receives context and returns allowed/denied.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- During boot, when configuring security posture.

##### Common Mistakes
- Putting heavy I/O inside policies; they run on every resolution.

### Method: markAsEncrypted(…)

#### Technical Explanation
Marks a service id as requiring encrypted configuration handling.

##### For Humans: What This Means
It’s the “treat this service as sensitive” flag.

##### Parameters
- `$serviceId`: The sensitive service id.

##### Returns
- Nothing.

##### Throws
- None.

##### When to Use It
- When a service contains secrets or protected configuration values.

##### Common Mistakes
- Marking non-sensitive services and paying encryption overhead for no gain.

### Method: encryptServiceConfig(…)

#### Technical Explanation
Encodes a config array to JSON and encrypts it.

##### For Humans: What This Means
It turns normal config into “sealed config”.

##### Parameters
- `$config`: Configuration array to encrypt.

##### Returns
- An encrypted string payload.

##### Throws
- `RuntimeException` when JSON encoding fails.

##### When to Use It
- When storing sensitive config into persistence or logs safely.

##### Common Mistakes
- Encrypting already-encrypted values and creating nested confusion.

### Method: validateServiceSecurity(…)

#### Technical Explanation
Scans a service’s public methods and characteristics to flag security issues (dangerous methods, filesystem access).

##### For Humans: What This Means
It tries to spot “this service can do scary things” patterns.

##### Parameters
- `$serviceId`: Identifier for reporting context.
- `$service`: The resolved service object.

##### Returns
- An array of security issue entries.

##### Throws
- Reflection-related errors can bubble up depending on implementation.

##### When to Use It
- Audits, diagnostics, and security-aware boot checks.

##### Common Mistakes
- Treating these findings as proof of vulnerability; they’re signals, not verdicts.

### Method: getSecurityAudit(…)

#### Technical Explanation
Returns an audit trail snapshot for a service (implementation may be mock/stub).

##### For Humans: What This Means
It’s “show me the security history for this service”.

##### Parameters
- `$serviceId`: Service id.

##### Returns
- An array with audit information.

##### Throws
- None.

##### When to Use It
- Admin panels and incident investigations.

##### Common Mistakes
- Assuming this returns real audit data if the underlying audit store isn’t wired.

### Method: hardenService(…)

#### Technical Explanation
Applies type-based security hardening hooks (DB/HTTP/filesystem) to the service instance.

##### For Humans: What This Means
It tries to apply safe defaults when a service looks risky.

##### Parameters
- `$service`: Service instance to harden.

##### Returns
- The same service instance (potentially modified by hardening hooks).

##### Throws
- None directly.

##### When to Use It
- As a post-resolution safety step for infrastructure services.

##### Common Mistakes
- Expecting it to “magically secure” everything; hardening hooks must be implemented for your specific service types.

## Risks, Trade-offs & Recommended Practices
- Risk: Heuristic detection (encrypted strings, dangerous methods) can false-positive/false-negative.
  - Why it matters: you can block legitimate behavior or miss real threats.
  - Design stance: treat heuristics as signals; log and alert rather than silently changing behavior.
  - Recommended practice: keep allowlists/denylists explicit and configurable; don’t rely only on heuristics.
- Trade-off: Reflection and encryption cost performance.
  - Why it matters: these operations add latency and CPU overhead.
  - Design stance: use secure resolution for sensitive services or environments; keep fast paths for non-sensitive cases.
  - Recommended practice: cache policies, limit scanning, and avoid doing heavy work on every resolution.

### For Humans: What This Means
Security isn’t free. Use it where it matters, and make the costs visible.

## Related Files & Folders
- `docs_md/Guard/Enforce/ResolutionPolicy.md`: The policy concept this file complements (callables vs interface-based policies).
- `docs_md/Observe/Metrics/LoggerFactoryIntegration.md`: Used for audit-style logging.
- `docs_md/Observe/Timeline/ResolutionTimeline.md`: A related diagnostics tool for resolution behavior.

### For Humans: What This Means
This file enforces security; the Observe layer helps you see what’s happening.

