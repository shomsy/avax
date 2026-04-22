---
title: RequestedInputs - Runtime Architecture
owner: HTTP Foundation Team
last_reviewed: 2026-04-18
classification: internal
---

# RequestedInputs Capability

The `RequestedInputs` component is the **Capability Owner** for typed, sanitized, and unified access to incoming request data (Query Parameters + Parsed Body). It completely abstracts away the `$_GET` and `$_POST` paradigms in favor of a clean, state-injected model.

## Core Semantic Rules (The Contract)

1. **Composition over Inheritance**: `RequestedInputs` is not a Request. It is a capability object exposed via `$request->inputs()`.
2. **Body Wins Collision**: If the same key exists in both the Query and the Parsed Body, the **Body value strictly wins**. 
3. **Presence vs Nullability (`isset` vs `array_key_exists`)**:
   - `has('key')` uses strict `array_key_exists`. A key can be present but hold a `null` value.
   - `hasNonNull('key')` ensures the key is both present and not strictly `null`.
   - `null` is a valid domain value (e.g., explicitly cleared fields in JSON payloads).
4. **Source-Awareness via `InputValue`**: You can explicitly ask for the source of a value using `$inputs->value('key')`, which returns an `InputValue` object detailing whether the value came from the `body`, `query`, or is `missing`.

## Runtime Assembly Flow

Inputs are strictly assembled exactly **once** during the `AssembleIncomingRequest` pipeline:

1. **Single-Pass IO**: The raw body stream is read exactly once and buffered.
2. **Strict Parsing**: The buffered body is parsed via `ParseBodyByContentType` into a `ParsedBody` capability.
3. **Merging**: `MergedInputs` takes `QueryParams` and `ParsedBody` and unifies them in memory.
4. **Injection**: `RequestedInputs` acts as a facade over `MergedInputs`, injecting the stateless `InputSanitizer` and `MapRequestedInputsToDto` services for extended capabilities.

## Type Safety & Casting

The API enforces strict type casting at the boundary. If a value cannot be safely cast, it falls back to the provided default (or a sensible primitive default).

```php
$inputs = $request->inputs();

$id    = $inputs->int('id', 0);           // Strict int cast
$name  = $inputs->string('name', '');     // Strict string cast
$flags = $inputs->array('flags', []);     // Enforces array
$debug = $inputs->bool('debug', false);   // Understands 'true', '1', 'on', 'false', '0', 'off'
$role  = $inputs->enum('role', Role::class); // BackedEnum hydration
```

## Security & Sanitization

Raw access is permitted for safe domain transfers, but when echoing to views or logs, the `sanitized()` facade must be used:

```php
// Strips control characters
$cleanString = $inputs->sanitized()->string('title');

// Encodes for HTML output (htmlspecialchars)
$safeHtml = $inputs->sanitized()->html('description');

// Encodes for JS injection
$safeJs = $inputs->sanitized()->js('config');
```

## DTO Hydration Boundary

`RequestedInputs` is the exclusive integration point for Data Transfer Objects.

```php
// Automatically maps merged inputs via Reflection
$dto = $request->inputs()->as(RegisterUserDto::class);
```
