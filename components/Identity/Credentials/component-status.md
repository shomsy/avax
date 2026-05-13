# Credentials Status

Status: SCAFFOLD
Reason: Implementation exists but uses static in-memory array for credential storage — NOT suitable for production.
Owns: Credential storage and retrieval (planned).
Does not own: Authentication, authorization, or token management.
Current behavior: store()/read()/forget() using static array. No persistence, no encryption, no access control.
SECURITY WARNING: Current implementation stores credentials in static memory. DO NOT use for real credentials.
Production-ready: No.
Tests: None.
Health/doctor: Not applicable — scaffold with security concern.
Roadmap: Replace with secure credential store (encrypted, persistent, access-controlled).
Do not use until: Secure implementation exists.
