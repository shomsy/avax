# Identity Component Security Boundaries

## Overview

This document defines the security boundaries of the Identity component. Each boundary separates trusted from untrusted context and defines what must be validated when crossing it.

## Boundary Map

```
┌─────────────────────────────────────────────────┐
│                    Client                        │
│  (untrusted: credentials, tokens, tenant claims) │
└──────────────────┬──────────────────────────────┘
                   │ BOUNDARY 1: Request Ingress
                   ▼
┌─────────────────────────────────────────────────┐
│              PublicSurface                       │
│  (receives input, delegates, never trusts)       │
└──────────────────┬──────────────────────────────┘
                   │ BOUNDARY 2: Authentication
                   ▼
┌─────────────────────────────────────────────────┐
│          Authentication Capability               │
│  (validates credentials, produces identity)      │
└──────────────────┬──────────────────────────────┘
                   │ BOUNDARY 3: Identity Trust
                   ▼
┌─────────────────────────────────────────────────┐
│          Identity Context                        │
│  (trusted: verified claims, tenant context)      │
└──────────────────┬──────────────────────────────┘
                   │ BOUNDARY 4: Authorization
                   ▼
┌─────────────────────────────────────────────────┐
│          Authorization Boundary                  │
│  (decides: allow, deny, conditional)             │
└──────────────────┬──────────────────────────────┘
                   │ BOUNDARY 5: Data Access
                   ▼
┌─────────────────────────────────────────────────┐
│          Business Flows                          │
│  (executes with verified identity and tenant)    │
└─────────────────────────────────────────────────┘
```

## Boundary 1: Request Ingress

**Crossing From**: Untrusted client (HTTP request)
**Crossing To**: AvaX PublicSurface

**What Must Be Validated**:
- All input is untrusted until proven otherwise
- Credentials are extracted but not trusted
- Token strings are extracted but not validated yet
- Tenant signals are extracted but not validated yet

**What Is Forbidden**:
- Trusting any client-supplied identity claim
- Trusting any client-supplied tenant context
- Trusting any client-supplied permission assertion

## Boundary 2: Authentication

**Crossing From**: PublicSurface (unvalidated input)
**Crossing To**: Authentication Capability (credential verification)

**What Must Be Validated**:
- Credential type is recognized and supported
- Credential format is valid for its type
- Credential is passed to the correct validator

**What Is Forbidden**:
- Skipping credential verification
- Trusting credentials without verification
- Producing identity without successful verification

## Boundary 3: Identity Trust

**Crossing From**: Authentication Capability (verified credentials)
**Crossing To**: Identity Context (trusted claims)

**What Must Be Validated**:
- Credential verification produced a result
- Identity claims are complete and consistent
- Tenant context is resolved and validated

**What Is Forbidden**:
- Using unverified identity claims
- Using identity without tenant context (when required)
- Persisting identity across requests in long-lived workers

## Boundary 4: Authorization

**Crossing From**: Identity Context (trusted identity)
**Crossing To**: Authorization Boundary (permission decisions)

**What Must Be Validated**:
- Identity is verified (Boundary 3 passed)
- Requested action and target resource are defined
- Authorization policy is evaluated for the specific action and resource

**What Is Forbidden**:
- Assuming authentication implies authorization
- Skipping authorization because identity is verified
- Using route-level guards as the only authorization check

## Boundary 5: Data Access

**Crossing From**: Authorization Boundary (permission granted)
**Crossing To**: Business Flows (data operations)

**What Must Be Validated**:
- Authorization decision is allow
- Tenant context is attached and validated
- Data access is scoped to the resolved tenant

**What Is Forbidden**:
- Accessing data outside the resolved tenant context
- Accessing data without authorization decision
- Accessing data with unverified identity

## Cross-Cutting Rules

1. **No boundary may be skipped**: Each boundary must be crossed in sequence
2. **No boundary may be bypassed**: No shortcut from untrusted input to data access
3. **Each boundary fails closed**: Failure at any boundary denies the request
4. **Each boundary is observable**: Crossing each boundary produces a security event
5. **Boundaries are per-request**: No boundary state persists between requests in long-lived workers
