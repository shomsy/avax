# Security Operating Model (V3 Enterprise)

Version: 3.0.0
Status: Normative

## The "Security is LOUD" Principle
Security findings are never silent, never inferred, and never deferred without explicit, recorded human approval. 

## 1. Finding Severity & Escalation (OWASP-Class)

- **BLOCKER**: Active secret leakage, trivial remote code execution (RCE), unauthenticated data exposure, or sandbox escape. 
  - *Policy*: Immediate halt. Blocks commit, publish, and release. Must be fixed immediately.
- **HIGH**: Known exploitable vulnerability (e.g., OWASP Top 10 violation), dependency with known critical CVE, unsafe AI action (e.g., destructive system command).
  - *Policy*: Blocks commit. Escalates to Lead Architect. Must be fixed before any merge.
- **MEDIUM**: Defense-in-depth failure, missing security headers, non-exploitable dependency vulnerability.
  - *Policy*: Blocks release unless explicitly accepted into `RISKS.md` via the **Security Exception Workflow**.

## 2. Explicit Policies

### Unsafe Command & AI Action Policy
Agents MUST NOT execute destructive filesystem operations (`rm -rf /`, `chmod 777`), download and execute unverified binaries, or modify system-level configurations without explicit human approval.

### Execution Sandbox Policy
AI execution must be isolated. Secrets MUST NOT be injected into the AI context window unless explicitly requested and approved via short-lived, scoped tokens.

### Secret Leakage Policy
Any detection of a secret in source code, logs, or agent evidence is a BLOCKER. The secret must be revoked immediately, and the incident recorded.

### Dependency Vulnerability & Supply-Chain Policy
All dependencies must be scanned. Any Critical/High CVE in a deployed artifact blocks release. 

## 3. Vulnerability Lifecycle

1. **Detection**: Found via static analysis, dynamic scanning, or recursive review.
2. **Triage**: Classified as BLOCKER, HIGH, or MEDIUM.
3. **Remediation**: Developer or Agent fixes the code.
4. **Verification**: Security evidence generated proving the fix.
5. **Closure**: Logged in `.agents/management/evidence/security/`.

## 4. Security Exception Workflow

To accept a MEDIUM security finding (YELLOW debt):
1. Create a risk entry in `RISKS.md`.
2. Must include specific business justification.
3. Must include an expiry date (max 30 days).
4. Must be explicitly approved by a designated security owner.
