# Security & Policy Runtime — How This Works

## Overview

V4-12 provides the security and policy runtime needed for production-grade AvaX framework operation.

## Components

### Request Signing

HMAC-SHA256 signing for internal service-to-service requests with:
- **Canonical request**: Method, normalized path, sorted headers (excluding signature headers), body hash
- **Nonce/replay protection**: NonceStore tracks used nonces; replayed nonces are rejected
- **Timestamp tolerance**: Signatures expire after configurable tolerance (default 300 seconds)
- **Safe failure**: All failures return descriptive but non-revealing messages

Classes:
- `SignInternalRequest` — signs requests
- `VerifyInternalRequestSignature` — verifies signatures
- `CanonicalizeSignedRequest` — produces deterministic canonical strings
- `RejectExpiredSignature` — checks timestamp tolerance
- `RejectReplayedNonce` — checks nonce uniqueness
- `SignaturePayload` — transports signature data via headers
- `NonceStore` — tracks used nonces (reset-safe for long-lived workers)

### Policy Engine

Explicit allow/deny policy evaluation with default-deny semantics:
- **Default deny**: If no rule matches, the decision is deny
- **Deny takes precedence**: A matching deny rule immediately denies
- **Observable**: Policy decisions include reasons for auditing
- **Exception on failure**: `PolicyDeniedException` with 403 status code

Classes:
- `DefinePolicy` — fluent policy builder with allow/deny rules
- `EvaluatePolicy` — evaluates policies with default-deny
- `PolicyRule` — individual rule with optional condition
- `PolicyDecision`, `PolicyEffect`, `PolicySubject`, `PolicyAction`, `PolicyResource`, `PolicyContext`, `PolicyFailure`

### Feature Flags

Runtime feature toggling with environment overrides:
- **Safe default**: Unknown flags return false (disabled)
- **Environment override**: Per-environment state takes precedence
- **InMemory store**: Simple store for testing and single-process

Classes:
- `FeatureFlag`, `FeatureFlagName`, `FeatureFlagState`
- `EvaluateFeatureFlag`, `InMemoryFeatureFlagStore`, `FeatureFlagStore` interface

### Service Discovery

Local service registry for endpoint resolution:
- **Endpoint validation**: URLs are validated on construction
- **Multiple endpoints**: A service can have multiple endpoints
- **No external dependency**: Pure in-memory, no Consul/Etcd required

Classes:
- `ServiceName`, `ServiceEndpoint`
- `InMemoryServiceRegistry`, `ServiceRegistry` interface

### Security Doctor

`CheckSecurityRuntime` produces doctor findings for all security components.

## Failure Behavior

- Missing signature headers → failure with "Missing signature headers" message
- Expired signature → failure with age and tolerance info
- Replayed nonce → failure with "Nonce replay detected"
- Signature mismatch → failure with "Signature mismatch" (no secrets revealed)
- No matching policy → deny with "No matching policy rule. Default deny."
- Unknown feature flag → false (disabled)

## Limits

- NonceStore is in-memory only; distributed nonce tracking is ROADMAP
- Feature flags store is in-memory; persistent store is ROADMAP
- Service registry is in-memory; external discovery is ROADMAP
- Request signing uses HMAC-SHA256; asymmetric signing is ROADMAP
