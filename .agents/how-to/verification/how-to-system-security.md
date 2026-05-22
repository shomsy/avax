# How to System Security

## Status

**MANDATORY** - This document defines non-negotiable security rules for AvaX.

## Normative Language

The words **MUST**, **MUST NOT**, **REQUIRED**, **MANDATORY**, **SHOULD**, **SHOULD NOT**, **MAY**, **FORBIDDEN**, *
*BLOCKER**, **HIGH**, **MEDIUM**, **LOW** are governance keywords.

- **MUST / REQUIRED / MANDATORY**: non-negotiable rule.
- **MUST NOT / FORBIDDEN**: prohibited pattern.
- **SHOULD**: expected default unless documented exception exists.
- **SHOULD NOT**: discouraged pattern requiring justification.
- **MAY**: optional behavior.
- **BLOCKER**: violation prevents GREEN status.
- **HIGH**: must be fixed before production-complete unless explicitly accepted.
- **MEDIUM**: must be tracked and fixed or explicitly deferred.
- **LOW**: cleanup or documentation issue.

---

## 1. Status of This Document

This document is part of the AvaX governance system.

It defines how security must be designed, implemented, reviewed, tested, observed, and proven across AvaX.

This document applies to:

```text
framework runtime
components
public surfaces
flows
capabilities
configuration
foundation primitives
HTTP endpoints
console commands
workers
queues
events
message buses
database access
cache access
filesystem access
external service integrations
operator tools
developer tooling
AI-generated code
examples
tests
```

This document is not a checklist to satisfy after implementation.

This document is a design law.

Security must shape the design before code exists.

Security must be proven before production-ready claims are made.

The core AvaX law still wins:

```text
folder says flow or capability
unit says responsibility
function says exact action
```

Security rules must follow that law.

Security must not create vague folders such as:

```text
Security/
  Services/
  Managers/
  Helpers/
  Utils/
  Guards/
  Stuff/
```

Security behavior must be named by what it protects or what action it performs.

Good:

```text
Capabilities/
  PasswordHashing/
    HashPassword.php
    VerifyPasswordHash.php

  AccessDecision/
    AuthorizeUserAccess.php
    AccessDenied.php

  SecretRedaction/
    RedactSecret.php
    RedactAuthorizationHeader.php

  RequestAuthentication/
    AuthenticateBearerToken.php
    AuthenticationFailed.php
```

Bad:

```text
Capabilities/
  Security/
    SecurityService.php
    AuthManager.php
    TokenHelper.php
```

---

## 2. Security Thesis

AvaX must be secure by default.

Risk must be explicit.

Sensitive data must be protected by design.

Public boundaries must be hostile to accidental exposure.

Runtime behavior must be safe under long-lived workers.

Observability must never become a data leak.

Developer convenience must not silently weaken security.

Security is not one component.

Security is a property of every public boundary, every data boundary, every runtime boundary, and every integration
boundary.

---

## 3. Security Sources and Alignment

This document is aligned with the spirit of recognized secure software practices, especially:

```text
OWASP ASVS
OWASP API Security Top 10
NIST Secure Software Development Framework
secure-by-default design
least privilege
defense in depth
explicit trust boundaries
security verification as evidence
```

These references are guidance.

AvaX governance remains the project-specific source of truth.

If an external standard is stricter for a specific project, the stricter rule wins.

---

## 4. Non-Negotiable Security Laws

### 4.1 Deny by Default

Access must be denied unless explicitly allowed.

No public endpoint, operation, message consumer, admin command, or internal capability may assume access is allowed by
default.

Bad:

```php
if ($user !== null) {
    return true;
}

return true;
```

Good:

```php
if ($user === null) {
    throw AuthenticationRequired::forAction('approve-release');
}

if (! $policy->allows($user, $action, $resource)) {
    throw AccessDenied::forAction($action);
}

return true;
```

### 4.2 Least Privilege

A unit must receive only the permissions, secrets, data, and capabilities it needs.

Do not pass the whole container.

Do not pass the whole request when only a user id is needed.

Do not pass the whole config when only one safe value is needed.

Do not pass raw credentials across wide call chains.

### 4.3 Never Trust Input

All input is untrusted until validated and normalized.

Input includes:

```text
HTTP request body
HTTP headers
query parameters
route parameters
cookies
uploaded files
CLI input
environment variables
config files
queue messages
event payloads
webhook payloads
database records read from untrusted sources
cache values
filesystem content
external API responses
AI model output
```

Validation must happen at the boundary.

Normalization must happen before domain behavior.

Authorization must happen before protected behavior.

### 4.4 Secure Defaults

Default configuration must be safe.

Dangerous behavior must require explicit opt-in.

Examples:

```text
debug errors disabled in production
raw exception output disabled in production
secret logging disabled always
unsafe CORS disabled by default
raw SQL logging disabled by default
session cookies secure by default
same-site cookies restrictive by default
token TTL finite by default
external calls require timeout
```

### 4.5 No Security by Convention

Security must not depend on developers remembering invisible conventions.

Bad:

```text
Remember to call authorize() before this method.
```

Good:

```text
The public flow itself calls authorization before doing protected work.
A test proves the protected path is denied without authorization.
```

### 4.6 No Silent Bypass

Any security bypass must be:

```text
explicit
named
scoped
documented
tested
forbidden in production unless justified
```

Bad:

```php
if ($debug) {
    return true;
}
```

Good:

```php
if ($localSecurityBypass->isAllowedForLocalDevelopmentOnly()) {
    return AccessDecision::allowedForLocalDevelopment();
}
```

### 4.7 No Secret Exposure

Secrets must never be logged, dumped, thrown in exception messages, written to reports, returned in API responses,
included in telemetry, or stored in public artifacts.

Secrets include:

```text
passwords
password hashes
API keys
access tokens
refresh tokens
session ids
CSRF tokens
private keys
database passwords
authorization headers
reset tokens
verification tokens
webhook signing secrets
encryption keys
cloud credentials
```

### 4.8 Security Needs Proof

No code may be called secure without evidence.

Evidence may be:

```text
security tests
authorization tests
input validation tests
static analysis
threat model
security review
redaction tests
runtime doctor checks
dependency audit
configuration audit
```

No proof means no green status.

---

## 5. Security Boundary Model

Every public or cross-system entrypoint must define its security boundary.

A security boundary answers:

```text
Who can call this?
What data can enter?
What is trusted?
What is untrusted?
What identity is known?
What authorization is required?
What validation happens?
What normalization happens?
What is rejected?
What is logged?
What must never be logged?
What failure is returned?
What behavior is observable?
```

Security boundary examples:

```text
HTTP endpoint
console command
queue consumer
event listener
webhook receiver
public facade
runtime control command
doctor check
admin endpoint
external API client
database migration runner
file upload handler
```

A boundary without a security decision is incomplete.

---

## 6. Data Classification Rule

Every component that handles data must know what class of data it handles.

Recommended classes:

```text
PublicData
InternalData
ConfidentialData
SecretData
PersonalData
SecuritySensitiveData
FinancialData
OperationalData
TelemetryData
```

### 6.1 PublicData

Data safe to expose publicly.

Examples:

```text
public documentation
public package metadata
public status page summary
```

### 6.2 InternalData

Data intended for internal application behavior.

Examples:

```text
internal ids
feature flags
component status
non-sensitive diagnostics
```

### 6.3 ConfidentialData

Data that must not be exposed publicly.

Examples:

```text
customer records
private reports
internal architecture state
business data
```

### 6.4 SecretData

Data that must never be exposed.

Examples:

```text
API keys
tokens
passwords
private keys
session ids
authorization headers
webhook secrets
```

### 6.5 PersonalData

Data that identifies or describes a person.

Examples:

```text
email address
name
phone number
user id
IP address where policy treats it as personal
profile attributes
```

### 6.6 SecuritySensitiveData

Data useful to attackers.

Examples:

```text
stack traces
server paths
dependency versions
config paths
internal route maps
role names when sensitive
permission maps when sensitive
security policy internals
```

### 6.7 Data Classification Proof

A component handling sensitive data must document:

```text
data classes handled
storage rules
logging rules
redaction rules
retention rules
public response rules
test coverage
```

---

## 7. Threat Modeling Rule

Every serious public boundary, external integration, authentication flow, authorization flow, file upload flow, payment
flow, admin/control flow, or cross-system message flow must have a small threat model.

A threat model does not need to be academic.

It must answer:

```text
What are we protecting?
Who might attack or misuse this?
What can enter?
What can leave?
What trust boundary is crossed?
What can be spoofed?
What can be tampered with?
What can be replayed?
What can be leaked?
What can be abused for cost or denial of service?
What is the safe failure mode?
Which tests prove the protection?
```

### 7.1 Threat Model Placement

Use the smallest useful artifact.

Allowed:

```text
components/<Area>/<Component>/README.md
EVIDENCE/security-reports/
docs/security/
```

Do not create `System/ThreatModels/` by default.

Threat modeling is evidence.

It is not a runtime folder.

---

## 8. PublicSurface Security Rule

Every `PublicSurface/` unit is a security boundary.

A public surface must not:

```text
skip validation
skip authorization
expose internals
return raw exceptions
return raw secrets
accept ambiguous input
retain request-scoped state
call runtime-specific implementation accidentally
leak mutable internal objects
```

A public surface must:

```text
accept stable public input
normalize simple public input
delegate real behavior to flows or capabilities
return stable public output
hide internal machinery
document breaking security changes
```

### 8.1 PublicSurface Security Proof

Required tests:

```text
public call rejects invalid input
public call denies unauthorized access when protected
public call does not expose internal exception details
public call does not return secrets
public call delegates to internal owner
```

---

## 9. Endpoint Security Rule

Every HTTP endpoint must define:

```text
method
route
input source
authentication requirement
authorization requirement
CSRF requirement where applicable
rate limit or resource limit where applicable
request body size limit where applicable
content type requirement where applicable
response shape
error shape
sensitive-data redaction rule
observability rule
```

### 9.1 Endpoint Checklist

Every endpoint must answer:

```text
Can anonymous users call it?
Can authenticated users call it?
Which roles or permissions are required?
Which resource or object is being accessed?
Is object-level authorization checked?
Can the caller influence identifiers?
Can the caller influence file paths?
Can the caller influence redirects?
Can the caller influence external URLs?
Can the caller trigger expensive work?
Can the caller cause repeated side effects?
```

### 9.2 Endpoint Naming

Endpoint flow names must say what happens.

Good:

```text
Flows/
  RegisterUser/
  LoginUser/
  RefreshAccessToken/
  UploadProfilePhoto/
  ReadInvoice/
  CancelSubscription/
```

Bad:

```text
Flows/
  UserEndpoint/
  ApiHandler/
  RequestProcessor/
```

### 9.3 Object-Level Authorization Rule

If an endpoint uses an id from the user, it must check access to that object.

Bad:

```php
$invoice = $invoices->find($request->route('id'));

return InvoiceResponse::from($invoice);
```

Good:

```php
$invoice = $invoices->find($invoiceId);

$authorization->authorize(
    action: 'read-invoice',
    actor: $actor,
    resource: $invoice,
);

return InvoiceResponse::from($invoice);
```

Authentication is not authorization.

Role check is not always object authorization.

### 9.4 Resource Consumption Rule

Endpoints must not allow unbounded resource use.

Protect:

```text
pagination limit
upload size
request body size
batch size
export size
search complexity
GraphQL complexity if used
queue fanout
email/SMS trigger count
external API call count
AI token usage
```

Expensive endpoints require explicit limits.

---

## 10. Input Validation Rule

Validation must happen at the boundary before domain behavior.

Validation must be explicit, named, and testable.

Good:

```text
Capabilities/
  LoginInputValidation/
    ValidateLoginInput.php

Flows/
  RegisterUser/
    ValidateRegistrationInput.php
```

Bad:

```text
Helpers/
  Validator.php

Services/
  InputService.php
```

### 10.1 Validation Must Define

```text
required fields
optional fields
types
length limits
allowed formats
allowed enum values
range limits
normalization rules
rejection rules
error messages
sensitive field rules
```

### 10.2 Normalize Before Use

Normalize input once.

Examples:

```text
trim strings when appropriate
normalize email case when appropriate
normalize Unicode where appropriate
parse ids into value objects
parse dates into immutable date objects
parse booleans explicitly
```

Do not normalize silently in multiple places.

### 10.3 Reject Ambiguous Input

Reject input when:

```text
type is ambiguous
encoding is invalid
identifier format is invalid
array shape is unexpected
unknown fields are forbidden
file extension and MIME type disagree
URL is not allowed
redirect target is unsafe
```

### 10.4 Validation Proof

Required tests:

```text
valid input accepted
missing required field rejected
wrong type rejected
too long value rejected
invalid enum rejected
unexpected field rejected when policy requires strict input
malicious input rejected or neutralized
```

---

## 11. Output Encoding Rule

Output must be encoded for the context where it is used.

Context matters.

Examples:

```text
HTML body
HTML attribute
JavaScript string
CSS value
URL
JSON
SQL
shell argument
log line
CSV
XML
Markdown
```

Do not use one generic escaping function for every context.

### 11.1 HTML and Views

Views must not render untrusted values raw.

Raw output must be explicit and justified.

Good:

```text
RenderEscapedHtml
RenderTrustedHtml
```

Bad:

```text
echo $userInput
```

### 11.2 JSON

JSON responses must use safe serialization.

Do not manually concatenate JSON.

### 11.3 CSV

CSV export must prevent formula injection when opened in spreadsheets.

Values starting with risky formula prefixes must be escaped or neutralized by policy.

### 11.4 Logs

Log output must be redacted and structured.

Never encode secrets into log messages.

---

## 12. Authentication Rule

Authentication verifies identity.

It does not grant permission by itself.

Authentication flows must define:

```text
credential type
failure behavior
rate limit
lockout or abuse policy
timing attack considerations
session creation rule
token creation rule
audit event
observability event
```

### 12.1 Password Authentication

Password authentication must use:

```text
modern password hashing
per-password salt through the password hashing algorithm
constant-time verification where applicable
rate limiting
generic error messages
secure password reset flow
password rehashing policy
```

Never store plaintext passwords.

Never log password input.

Never return whether username or password was wrong when that leaks user enumeration.

### 12.2 Token Authentication

Token authentication must define:

```text
token type
issuer
audience
expiry
rotation policy
revocation policy where required
storage rule
transport rule
signature or verification rule
clock skew policy
```

Never treat token presence as sufficient authorization.

### 12.3 Multi-Factor Authentication

MFA flows must define:

```text
enrollment
challenge
recovery
backup codes
trusted device policy
failure limits
audit events
```

MFA bypass must be explicit, tested, and restricted.

---

## 13. Authorization Rule

Authorization decides whether an authenticated or anonymous actor may perform an action on a resource.

Authorization must be explicit for protected behavior.

Do not hide authorization in random helpers.

Good:

```text
Capabilities/
  AccessDecision/
    AuthorizeUserAccess.php
    AccessDecision.php
    AccessDenied.php
```

Good:

```text
Flows/
  ReadInvoice/
    AuthorizeInvoiceRead.php
```

Bad:

```text
Services/
  AuthService.php
```

### 13.1 Authorization Must Include

```text
actor
action
resource
context
decision
denial reason where safe
audit event where needed
```

### 13.2 Role-Based Access Is Not Enough

Role checks may be useful.

But resource access often needs object-level checks.

Example:

```text
User has role customer.
User still cannot read another customer's invoice.
```

### 13.3 Authorization Proof

Required tests:

```text
anonymous denied when authentication is required
authenticated but unauthorized actor denied
authorized actor allowed
object-level access enforced
privilege escalation attempt denied
```

---

## 14. Session Security Rule

Sessions must be safe by default.

Session rules:

```text
session id must be random and high entropy
session id must not be logged
session id must rotate after login
session id must rotate after privilege elevation
session cookie must be HttpOnly
session cookie must be Secure in HTTPS environments
session cookie must use SameSite policy
session data must not store secrets unnecessarily
session lifetime must be finite
logout must invalidate session
```

### 14.1 Long-Lived Runtime Session Rule

In long-lived workers, session state must never leak between requests.

Request state must be scoped.

Shared runtime services must not retain current user, current request, current session, or current authorization
decision.

---

## 15. CSRF Rule

State-changing browser endpoints must protect against CSRF unless they are explicitly stateless and not
cookie-authenticated.

CSRF protection is required for:

```text
forms
cookie-authenticated state-changing requests
admin actions
account changes
password changes
email changes
payment actions
```

CSRF token failures must not leak secrets.

CSRF tokens must not be logged.

---

## 16. CORS Rule

CORS must be restrictive by default.

Do not use wildcard origins with credentials.

Allowed origins must be explicit.

CORS configuration must define:

```text
allowed origins
allowed methods
allowed headers
credential policy
max age
environment-specific behavior
```

Unsafe CORS is a public security bug.

---

## 17. Secrets Rule

Secrets must be handled as toxic data.

### 17.1 Secret Sources

Secrets may come from:

```text
environment
secret manager
encrypted config
deployment platform
CI/CD variables
operator input
```

### 17.2 Secret Storage

Secrets must not be stored in:

```text
source code
git history
logs
debug dumps
reports
error messages
cache keys
URLs
query strings
client-side code
```

### 17.3 Secret Access

Secret access must be narrow.

Good:

```text
Capabilities/
  SecretReading/
    ReadDatabasePassword.php
    ReadWebhookSigningSecret.php
```

Bad:

```text
Config::all()
```

### 17.4 Secret Rotation

Any long-lived secret must have a rotation story.

At minimum:

```text
where the secret lives
who can rotate it
what changes after rotation
how old and new secrets coexist during transition
how failure is diagnosed
```

---

## 18. Configuration Security Rule

Configuration is a security boundary.

Configuration must be validated before runtime behavior depends on it.

Configuration must define:

```text
required keys
optional keys
default values
sensitive keys
redaction rules
environment restrictions
production-safe defaults
```

### 18.1 Dangerous Configuration

Dangerous options must be explicit.

Examples:

```text
debug=true
log_bindings=raw
disable_auth=true
allow_insecure_tls=true
trust_all_proxies=true
allow_any_origin=true
```

Dangerous options must be forbidden or loudly reported in production.

### 18.2 Config Redaction

Doctor checks, config dumps, debug screens, and reports must redact sensitive values.

Good:

```text
database.password = [REDACTED]
api.secret = [REDACTED]
```

Bad:

```text
database.password = actual-password
```

---

## 19. Cryptography Rule

Do not invent cryptography.

Use proven platform libraries and reviewed algorithms.

Cryptography code must define:

```text
purpose
algorithm
key source
key length
nonce or IV rule
authentication rule
rotation rule
failure behavior
test vectors where useful
```

### 19.1 Encryption

Encryption must be authenticated.

Encrypted data without integrity protection is dangerous.

### 19.2 Hashing

Password hashing and general hashing are different.

Use password hashing for passwords.

Use message authentication codes for integrity when a secret key is involved.

Do not use fast general-purpose hashes for password storage.

### 19.3 Randomness

Security tokens must use cryptographically secure randomness.

Do not use predictable randomness for tokens, password reset codes, session ids, or keys.

### 19.4 Crypto Naming

Good:

```text
Capabilities/
  PasswordHashing/
    HashPassword.php
    VerifyPasswordHash.php

  PayloadEncryption/
    EncryptPayload.php
    DecryptPayload.php

  TokenSigning/
    SignAccessToken.php
    VerifyAccessTokenSignature.php
```

Bad:

```text
CryptoHelper
SecurityUtils
EncryptionManager
```

---

## 20. Database Security Rule

Database access must protect against injection, unauthorized access, data leakage, and unsafe migrations.

### 20.1 SQL Injection

Use parameter binding.

Never concatenate untrusted input into SQL.

Bad:

```php
$sql = "SELECT * FROM users WHERE email = '" . $email . "'";
```

Good:

```php
$query->where(column: 'email', operator: '=', value: $email);
```

### 20.2 Identifier Safety

Parameter binding does not protect SQL identifiers.

Table names, column names, sort fields, and directions must be allowlisted.

Bad:

```php
$orderBy = $request->query('order_by');

$query->orderBy($orderBy);
```

Good:

```php
$orderBy = $allowedSortFields->resolve($request->query('order_by'));

$query->orderBy($orderBy);
```

### 20.3 Query Logging

Raw query bindings may contain secrets or personal data.

Raw binding logging must be disabled by default.

If enabled for local debugging, it must be forbidden in production or heavily guarded.

### 20.4 Migration Security

Migrations must be safe to run once.

Migrations must not leak secrets.

Migrations must not silently drop data in production without explicit destructive-operation policy.

### 20.5 Database Proof

Required tests:

```text
parameter binding used for user input
unsafe sort field rejected
sensitive bindings redacted in logs
migration does not rerun accidentally
destructive migration requires explicit approval where policy requires it
```

---

## 21. Cache Security Rule

Cache can leak data.

Cache must be treated as a data boundary.

Rules:

```text
cache keys must not contain raw secrets
cache values must not expose another user data
tenant/user scope must be part of key when needed
sensitive values must have short TTL or avoid cache
cache invalidation must be explicit for sensitive changes
debug cache dumps must redact sensitive values
```

Bad:

```text
cache key: reset-token:<raw-token>
```

Good:

```text
cache key: password-reset:<hash-of-token-id>
```

### 21.1 Cache Poisoning

Never trust cached data if the source is untrusted or cross-tenant.

Cache writes must respect authorization and tenant boundaries.

---

## 22. Filesystem Security Rule

Filesystem access must protect against path traversal, unsafe uploads, unsafe permissions, and data leakage.

### 22.1 Path Rule

Never use raw user input as a filesystem path.

Path input must be normalized and constrained to an allowed root.

Bad:

```php
$file = $storagePath . '/' . $_GET['file'];
```

Good:

```text
ResolveSafeStoragePath
RejectPathTraversal
EnsurePathInsideStorageRoot
```

### 22.2 Upload Rule

File upload must define:

```text
max size
allowed MIME types
allowed extensions
content validation where needed
storage location
visibility
virus/malware scanning policy where required
filename normalization
metadata stripping when required
```

### 22.3 Permissions

Created files and directories must use safe permissions.

Do not create world-writable files by default.

### 22.4 Public Files

A file is not public just because it exists.

Public access must be explicit.

---

## 23. External I/O Security Rule

External calls are security boundaries.

External I/O includes:

```text
HTTP APIs
payment providers
email providers
SMS providers
object storage
search index
message broker
AI provider
CDN
webhooks
```

External I/O must define:

```text
timeout
TLS verification
allowed host policy where relevant
authentication
secret handling
request redaction
response validation
failure classification
retry safety
observability redaction
```

### 23.1 SSRF Rule

If user input can influence a URL, host, IP, redirect, or callback target, SSRF protection is required.

Protection may include:

```text
allowlisted hosts
blocked private IP ranges
blocked link-local addresses
blocked metadata service addresses
scheme restrictions
DNS rebinding protection where relevant
redirect restrictions
```

Never fetch arbitrary user-provided URLs without policy.

---

## 24. Webhook Security Rule

Webhook receivers must verify authenticity.

A webhook flow must define:

```text
signature verification
timestamp tolerance
replay protection
idempotency key
payload size limit
schema validation
failure response
audit event
secret rotation
```

Good naming:

```text
Flows/
  ReceiveStripeWebhook/
    ReceiveStripeWebhook.php
    VerifyStripeWebhookSignature.php
    RememberReceivedStripeEvent.php
```

Bad:

```text
WebhookHandler
WebhookService
```

---

## 25. Queue and Message Security Rule

Queue messages are untrusted until validated.

Even internal messages can be stale, replayed, malformed, duplicated, or produced by older code.

Every consumer must define:

```text
message schema
version
authentication or origin trust where relevant
authorization where relevant
idempotency behavior
retry behavior
dead-letter behavior
redaction rule
poison message handling
```

### 25.1 Message Payload Rule

Messages must not contain raw secrets.

Messages should avoid unnecessary personal data.

Prefer identifiers and lookups when safe.

But do not force unsafe re-query patterns if the event needs stable facts.

Balance privacy, consistency, and usefulness.

---

## 26. Event Security Rule

Events can become public API.

If an event crosses a component, bounded context, service, or external boundary, it needs:

```text
schema
versioning rule
compatibility rule
redaction rule
consumer expectation
failure behavior
```

Domain events inside one boundary may be more flexible.

Integration events must be stable.

---

## 27. Serialization Rule

Serialization is a security boundary.

Never deserialize untrusted data into executable objects or arbitrary classes.

Prefer explicit schemas.

Rules:

```text
avoid native object deserialization for untrusted input
validate serialized shape
limit nesting depth where needed
limit payload size
reject unknown types unless explicitly allowed
version payloads when stored or published
```

Good:

```text
DecodeJsonPayload
ValidateMessageSchema
```

Bad:

```text
unserialize($userInput)
```

---

## 28. Error Exposure Rule

Errors must be useful internally and safe externally.

Public responses must not expose:

```text
stack traces
server paths
SQL
secrets
tokens
private config
internal class names when sensitive
dependency versions when sensitive
```

Production error output must be generic.

Logs may contain more detail, but still must redact secrets.

### 28.1 Error Naming

Good:

```text
AuthenticationRequired
AccessDenied
InvalidInput
UnsafeRedirectTarget
WebhookSignatureInvalid
```

Bad:

```text
SecurityException
SystemError
SomethingWentWrongException
```

Errors should help the developer and operator understand the failure without leaking unsafe detail to users.

---

## 29. Logging and Observability Security Rule

Observability must not leak secrets.

Logs, metrics, traces, audit events, timelines, reports, and doctor output must redact sensitive data.

### 29.1 Structured Logging

Prefer structured logs.

Every security-relevant event should include:

```text
event name
actor id when safe
resource id when safe
action
result
correlation id
request id
reason code
```

Do not include raw credentials, tokens, or sensitive payloads.

### 29.2 Audit Events

Audit events are required for:

```text
login
logout
failed login where useful
password change
email change
MFA change
role or permission change
token creation or revocation
admin action
security setting change
destructive operation
secret access where policy requires it
```

Audit events must be tamper-aware where required by project policy.

---

## 30. Runtime Security Rule

AvaX targets long-lived runtime readiness.

Long-lived runtimes make state leaks dangerous.

Runtime security rules:

```text
no current user in singleton
no current request in singleton
no current session in singleton
no per-request authorization decision in singleton
no unbounded static mutable state
reset request-scoped state after each request
reset tenant context after each request
reset correlation context after each request
```

### 30.1 Reset Proof

Required tests or doctor checks:

```text
request state does not leak between requests
tenant state does not leak between requests
authorization state does not leak between requests
correlation id changes per request when expected
resettable state clears sensitive state
```

---

## 31. Admin and Control Plane Security Rule

Admin endpoints, operator commands, doctor checks, diagnostics, and control plane APIs are sensitive.

They must define:

```text
authentication
authorization
environment restrictions
rate limits
audit events
redaction
safe output
dangerous action confirmation
```

Doctor checks must not leak secrets.

Status endpoints must not expose sensitive internals.

Admin actions must be audited.

---

## 32. Debug and Development Mode Rule

Debug mode is dangerous.

Debug mode must be:

```text
off by default in production
explicitly configured
detected by runtime doctor
forbidden in production unless special policy allows it
```

Debug output must still redact secrets.

Local convenience must not become production behavior.

---

## 33. Tenant Security Rule

Any multi-tenant system must treat tenant id as a security boundary.

Tenant rules:

```text
tenant context must be explicit
tenant context must not leak between requests
tenant id from user input must be authorized
queries must be tenant-scoped where required
cache keys must include tenant scope where required
events must include tenant scope where required
logs must avoid cross-tenant sensitive exposure
```

### 33.1 Tenant Proof

Required tests:

```text
tenant A cannot read tenant B data
tenant A cannot update tenant B data
tenant-scoped cache does not leak values
tenant context resets between requests
```

---

## 34. Privacy and Data Minimization Rule

Collect only what is needed.

Store only what is needed.

Return only what is needed.

Log only what is safe.

Retain only as long as needed.

Privacy-sensitive flows must answer:

```text
why this data is collected
where it is stored
who can access it
how long it is retained
how it is deleted
how it is exported if required
how it is redacted from logs
```

Do not use privacy-sensitive data as debug convenience.

---

## 35. Supply Chain Security Rule

Dependencies are part of the attack surface.

Every project must define:

```text
dependency update policy
dependency audit command
lock file policy
package source policy
abandoned package policy
vulnerability handling policy
script execution policy
```

Rules:

```text
do not commit unknown generated vendor code
do not run untrusted install scripts without review
do not add packages for trivial behavior
pin or lock dependencies according to project policy
review transitive dependency risk for sensitive systems
```

---

## 36. Build and Deployment Security Rule

Build and deployment must protect secrets, artifacts, and runtime configuration.

Rules:

```text
secrets must not be baked into artifacts
debug artifacts must not be deployed to production
build logs must redact secrets
deployment config must be validated
file permissions must be safe
production environment must fail closed when required config is missing
```

Deployment should run security gates before release where available.

---

## 37. AI-Generated Code Security Rule

AI-generated code is untrusted until reviewed and tested.

AI output must not be accepted when it:

```text
adds authentication bypass
adds authorization bypass
logs secrets
hardcodes credentials
disables TLS verification
uses unsafe deserialization
uses raw SQL with user input
uses broad catch and suppresses failures
creates skeleton security classes
changes public security behavior without tests
```

AI agents must follow active stage lock.

AI agents must not call unsafe code "temporary" if it lands in production paths.

Security-sensitive AI changes require explicit review.

---

## 38. Naming Rules for Security Code

Security names must be exact.

Good:

```text
AuthenticateBearerToken
AuthorizeInvoiceRead
VerifyPasswordHash
HashPassword
RotateSessionAfterLogin
ValidateCsrfToken
VerifyWebhookSignature
RedactAuthorizationHeader
RejectUnsafeRedirectTarget
EnsurePathInsideStorageRoot
```

Weak:

```text
AuthService
SecurityManager
TokenHelper
Guard
Protector
Validator
PermissionProcessor
```

Use the domain word when it is real:

```text
Authentication
Authorization
AccessDecision
PasswordHashing
TokenSigning
SecretRedaction
SessionRotation
WebhookVerification
```

Do not create generic `Security/Services`.

Security must be readable as exact protection.

---

## 39. Security Folder Placement

Security logic belongs where the protected behavior lives.

Examples:

```text
Flows/
  LoginUser/
    LoginUser.php
    VerifyLoginCredentials.php
    RotateSessionAfterLogin.php

Flows/
  ReceiveStripeWebhook/
    ReceiveStripeWebhook.php
    VerifyStripeWebhookSignature.php
    RememberReceivedStripeEvent.php

Capabilities/
  PasswordHashing/
    HashPassword.php
    VerifyPasswordHash.php

Capabilities/
  SecretRedaction/
    RedactSecret.php
    RedactAuthorizationHeader.php

Capabilities/
  AccessDecision/
    AuthorizeUserAccess.php
    AccessDecision.php
```

Avoid:

```text
Security/
  Helpers/
  Services/
  Managers/
```

A security capability may exist when the protection is reusable.

A security flow may exist when the protection is part of one end-to-end behavior.

---

## 40. Security Test Rule

Security behavior must be tested.

Required security test categories:

```text
authentication tests
authorization tests
object-level authorization tests
input validation tests
output encoding tests where applicable
secret redaction tests
CSRF tests where applicable
session rotation tests
token expiry tests
webhook signature tests
idempotency tests for external messages
path traversal tests
unsafe redirect tests
SQL injection prevention tests
tenant isolation tests where applicable
runtime state leak tests
```

Security tests must include negative cases.

A security test suite that only proves the happy path is not a security test suite.

---

## 41. Security Static Analysis Rule

Static checks should catch repeatable security mistakes.

Examples:

```text
raw superglobal usage outside allowed boundary
raw SQL concatenation
unsafe unserialize
debug output in production path
secret-like names in logs
PublicSurface importing runtime internals
generic SecurityService dumping ground
missing authorization in protected endpoint
```

If a rule can be automated, automate it.

---

## 42. Security Review Checklist

A security review passes only if:

```text
[ ] trust boundaries are identified
[ ] public inputs are validated
[ ] protected actions are authorized
[ ] object-level access is checked where ids are user-controlled
[ ] secrets are redacted
[ ] errors are safe externally
[ ] logs do not leak sensitive data
[ ] external calls have timeout and TLS policy
[ ] webhooks verify signatures
[ ] repeated execution is safe where needed
[ ] session/token rules are explicit
[ ] tenant isolation is preserved where applicable
[ ] cache keys do not leak or mix sensitive data
[ ] filesystem paths are constrained
[ ] debug behavior is blocked in production
[ ] long-lived runtime state is reset
[ ] security tests exist
[ ] dangerous configuration is detected
```

If one item is missing, the design is not security-green.

---

## 43. Security Proof Matrix

| Area          | Required Proof                                        |
|---------------|-------------------------------------------------------|
| PublicSurface | invalid input rejected, internals not exposed         |
| Endpoint      | authentication and authorization tests                |
| Object access | user cannot access another user's resource            |
| Input         | malicious or malformed input rejected                 |
| Output        | untrusted content encoded for context                 |
| Secrets       | logs, errors, reports redact sensitive values         |
| Session       | rotation after login, logout invalidates session      |
| Token         | expiry, invalid signature, wrong audience rejected    |
| CSRF          | state-changing browser request without token rejected |
| Webhook       | invalid signature rejected, replay blocked            |
| Database      | user input parameterized, identifiers allowlisted     |
| Cache         | tenant/user scope prevents leakage                    |
| Filesystem    | path traversal rejected                               |
| External I/O  | timeout and failure behavior tested                   |
| Queue         | duplicate message safe                                |
| Runtime       | request/user/tenant state does not leak               |
| Config        | dangerous production config rejected                  |
| Admin         | unauthorized admin action denied and audited          |

---

## 44. Security Classification

### GREEN

```text
Security boundary is explicit.
Controls are implemented.
Negative tests exist.
Sensitive data is redacted.
Runtime behavior is safe.
Evidence is current.
```

### YELLOW

```text
Security design is plausible.
Some controls exist.
Proof is incomplete.
May remain during active repair or design.
```

### RED

```text
Security boundary is unclear.
Authorization or validation is missing.
Secrets may leak.
Runtime state may leak.
Proof is stale or missing.
```

### BLOCKER

```text
authentication bypass
authorization bypass
secret exposure
unsafe deserialization of untrusted input
raw SQL with user input
unsafe file path from user input
debug output in production
tenant data leak
public endpoint exposing sensitive internals
production code disabling TLS verification
```

Blockers must be fixed before promotion.

---

## 45. Incident Readiness Rule

Security incidents must be diagnosable.

Security-sensitive systems should define:

```text
audit events
security logs
correlation ids
operator diagnostics
safe evidence collection
secret rotation plan
token revocation plan
session invalidation plan
vulnerability patch process
```

Incident response cannot depend on reading raw secrets or exposing private data.

---

## 46. V1 / V2 / V3 Security Discipline

### 46.1 V1 Security Minimum

V1 must prove:

```text
autoload and runtime do not expose unsafe debug behavior
PublicSurface does not leak internals
request state does not leak in long-lived runtime model
config secrets are redacted
database query logging redacts bindings by default
HTTP kernel has safe error output
doctor checks do not expose secrets
```

### 46.2 V2 Security Expansion

V2 must prove:

```text
authentication
authorization
session security
token security
tenant isolation if tenancy exists
queue/message security
external integration security
API compatibility/security boundary
operator control plane security
```

### 46.3 V3 Security Expansion

V3 must validate system-design security risks:

```text
abuse resistance
resource exhaustion
cross-service authorization
message replay
event compatibility
multi-region secret/config behavior
failure simulations
security architecture tests
```

V2 and V3 security implementation remains locked until active stage allows it.

Planning may continue.

---

## 47. Existing Code Recovery Rule

When recovering code from `avax-backup.txt`, `Framework.txt`, `Components.txt`, or git history, security must be
revalidated.

Old code is not automatically safe.

Recovered code must be checked for:

```text
raw input usage
missing authorization
raw SQL
unsafe filesystem access
secret logging
debug output
weak token handling
unsafe session behavior
unsafe deserialization
runtime state leakage
old namespace bypasses
test-only code in production path
```

Old behavior is evidence.

Current governance is the target.

Security tests are the judge.

---

## 48. Final Security Law

Security is not a layer added at the end.

Security is a property of every boundary.

Public input is hostile until proven valid.

Authenticated does not mean authorized.

Authorization must protect the object, not only the route.

Secrets are toxic.

Logs are public enough to hurt you.

Debug mode is dangerous.

External systems fail and lie.

Queues duplicate work.

Workers leak state unless reset.

Configuration can be an attack surface.

AI-generated code is untrusted until proven.

AvaX must fail closed, redact by default, expose only what is necessary, and prove security with tests and evidence.

If security makes the design clearer and safer, it belongs.

If security is hidden in vague helpers, it failed.

---

## 51. Security Must Scream Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

Security-sensitive findings MUST be loud, explicit, and blocking by default.

Any OWASP-class weakness, injection risk, authentication bypass, authorization bypass, sensitive data leak, unsafe
deserialization, unsafe redirect, filesystem traversal, command execution risk, SSRF risk, XSS risk, CSRF risk,
SQL/query injection risk, weak cryptography, secret exposure, unsafe logging, or session/cookie weakness MUST be
classified as HIGH or BLOCKER unless proven otherwise.

Security findings MUST NOT be hidden as:

```text
cleanup
style issue
minor refactor note
pre-existing harmless debt
accepted risk without owner/expiry
non-blocking note
code quality nit
low-priority cleanup
```

A security finding may be downgraded only with:

```text
exact threat explanation
affected path
exploitability assessment
mitigation proof
test or gate evidence
owner
expiry if accepted temporarily
truth/backlog entry
explicit stage-blocking decision
```

### Minimum Severity Rule

- exploitable or likely exploitable security weakness = **BLOCKER**
- potential OWASP-class weakness = **HIGH** or **BLOCKER**
- defense-in-depth gap = **MEDIUM** or **HIGH**, depending on blast radius
- documentation-only security clarification = **LOW** only when no exploit path exists

---

## 52. Security Review Trigger Rule

### Status

**MANDATORY**
**Severity:** HIGH

### Rule

Security review is mandatory when a change touches any of the following:

```text
authentication
authorization
roles/permissions
sessions
cookies
CSRF
CORS
redirects
user input
request parsing
validation
serialization/deserialization
database query building
filesystem I/O
file upload/download
logging
secrets
hashing
encryption
HTTP client/server
queues and message payloads
cache keys containing user or user-derived data
template/rendering
command/process execution
event payloads crossing boundaries
webhooks
signed URLs
tokens
API keys
password reset flows
rate limiting
tenant isolation
sandboxing
plugin execution
object storage paths
URL generation
proxy/trusted header handling
```

### Required Review Evidence

If triggered, the review MUST include this table:

| Area | Changed? | Risk checked | Finding | Severity | Fix/mitigation | Blocks commit? |
|---|---:|---|---|---|---:|

### Silent Skip Rule

If the change does not trigger security review, the governance review MUST explicitly state why.

Security review cannot be skipped silently.

---

## 53. Security Commit Block Rule

### Status

**MANDATORY**
**Severity:** BLOCKER

### Rule

A commit is FORBIDDEN if the current change introduces, exposes, or leaves unresolved any security issue classified as
BLOCKER, HIGH, OWASP-class weakness, authentication bypass, authorization bypass, injection risk, XSS risk, CSRF risk,
SSRF risk, unsafe redirect, unsafe deserialization, path traversal, command execution risk, secret exposure, sensitive
data logging, weak cryptography/hashing, session/cookie weakness, unsafe file upload/download, database query injection
risk, unsafe event payload crossing trust boundary, unsafe queue payload handling, unsafe tenant boundary, or unsafe
plugin/sandbox execution.

### Required Action

1. Do not commit.
2. Document the finding.
3. Fix it first.
4. Rerun validation.
5. Rerun security review.
6. Rerun recursive governance review.
7. Commit only when security review is clean.

### Temporary Acceptance

A security issue may remain only if final status is YELLOW or RED. Never GREEN.

If temporarily accepted, it MUST have:

```text
exact issue
affected path
severity
exploitability assessment
owner
mitigation
expiry or version
backlog or truth entry
evidence
explicit decision whether it blocks the current stage
```

### GREEN Commit Rule

A GREEN commit with unresolved security issue is forbidden.

---

## 54. Security and Performance Trigger Cross-Rule

Security review MUST be triggered by changes to: user input, authentication, authorization, session, cookies, CSRF,
CORS, encryption, hashing, secrets, filesystem I/O, HTTP client/server, serialization/deserialization, database query
building, queue payloads, cache keys containing user data, logging of sensitive data, redirects, file upload/download,
template rendering, command/process execution, event payloads crossing trust boundaries, tenant isolation, or
plugin/sandbox execution.

Performance review MUST be triggered by changes to: hot paths, loops over routes/listeners/middleware, reflection,
container resolution, event dispatch, queue workers, database query execution, route matching, filesystem scans, cache
compile/read/write, boot/worker startup, long-lived runtime reset, serialization, hydration/extraction, projections/read
models, graph compilation, or runtime scope creation/closing.

If triggered, review evidence must include why security/performance is relevant, what was checked, result, remaining
risk, and blocking decision. If not triggered, review must say why.

---

## 55. Critical Quality Signal Rule

### Status

**MANDATORY**
**Severity:** HIGH (escalates to BLOCKER — see below)

### Severity Escalation

Default severity is HIGH.

Escalates to **BLOCKER** when the issue threatens:

```text
security
data integrity
runtime safety
long-lived worker safety
truth/evidence integrity
public API compatibility
dependency graph correctness
rollback/recovery safety
```

Examples that MUST be BLOCKER when active:

```text
exploitable security issue
data corruption risk
request state stored in singleton
unresolved runtime composition leak in active runtime
fake GREEN
gate PASS with RED content
mandatory gate scans zero active files
hidden fallback dependency in runtime
missing required dependency discovered in business/runtime code
service locator in business/runtime code
```

### Rule

The review MUST loudly flag anything that threatens security, data integrity, runtime safety, long-lived worker safety,
dependency graph correctness, public API compatibility, static analysis baseline, test reliability, performance hot
paths, observability of failures, rollback/recovery safety, container verification, request scope isolation, tenant
isolation, state reset safety, or failure boundary correctness.

The following must not pass silently:

```text
hidden fallback construction
runtime service assembly
service locator usage in business code
missing dependency checks in business or runtime code
mutable static state without reset proof
request state stored in singleton
fake ServiceProvider
fake PublicSurface
broad try/catch swallowing errors
broad PHPStan ignores
weak tests
assertTrue(true)
evidence claiming GREEN while validation says otherwise
gate PASS with RED content
mandatory gate with zero scanned files
fake compatibility shim
duplicate canonical concepts
large builders acting as hidden containers
examples showing non-canonical style
```

### Core Principle

If it can create a security hole, corrupt data, hide a runtime failure, break long-lived workers, or fake correctness,
it must scream in review.
