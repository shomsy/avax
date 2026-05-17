# Security Review Template

**Date**: YYYY-MM-DD HH:MM TZ
**Reviewer/Agent**: [Name/ID]
**Target**: [PR/Commit/Task Reference]

## Summary of Changes
[Briefly describe the architectural or code changes]

## OWASP Check
- [ ] Injection
- [ ] Broken Authentication
- [ ] Sensitive Data Exposure
- [ ] XML External Entities (XXE)
- [ ] Broken Access Control
- [ ] Security Misconfiguration
- [ ] Cross-Site Scripting (XSS)
- [ ] Insecure Deserialization
- [ ] Using Components with Known Vulnerabilities
- [ ] Insufficient Logging & Monitoring

## Findings
### BLOCKER
- [List findings or "None"]

### HIGH
- [List findings or "None"]

### MEDIUM
- [List findings or "None"]

## Verification Evidence
[Link to validation outputs, scan results, or static analysis runs]

## Decision
[ ] APPROVED (Clean)
[ ] BLOCKED (Fix Required)
[ ] APPROVED WITH ACCEPTED DEBT (Requires RISKS.md entry)
