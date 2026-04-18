---
title: RequestedInputs - How This Works
owner: HTTP Foundation Team
last_reviewed: 2026-04-18
classification: internal
---

# RequestedInputs Capability

The `RequestedInputs` component provides a domain-aware, typed, and sanitized view of the incoming request data (Query Parameters + Parsed Body).

## Core Principles

1. **Composition over Inheritance**: `RequestedInputs` is a standalone capability that consumes `MergedInputs`.
2. **Body over Query**: Body parameters take precedence over query parameters in merged views.
3. **Type Safety**: Provides explicit methods for `string`, `int`, `float`, `bool`, and `array`.
4. **Sanitization by Default**: The `sanitized()` view enforces security policies before data reaches the domain.

## Component Structure

- **RequestedInputs**: The primary API for input access.
- **MergedInputs**: Internal state container for merged query and body data.
- **InputSanitizer**: Action owner responsible for filtering and cleaning raw input.
- **MapRequestedInputsToDto**: Integration point for hydrating Data Transfer Objects.

## Usage Example

```php
$inputs = $request->inputs();

// Raw access
$id = $inputs->int('id');

// Sanitized access
$name = $inputs->sanitized()->string('name');

// DTO Mapping
$dto = $inputs->as(CreateUserDto::class);
```

## Assembly Flow

Inputs are assembled during the `ServerRequest` initialization via the `AssembleIncomingRequest` pipeline.

1. **IO Capture**: `PrepareRequest` captures the raw body once.
2. **Merging**: `MergedInputs` combines GET and POST data.
3. **Injection**: `RequestedInputs` is created and injected with the required service dependencies (Sanitizer, Mapper).

## Security Boundaries

Always use `sanitized()` when returning data to a browser context to prevent XSS and other injection attacks.
```php
$safeName = $inputs->sanitized()->html('name');
```
