# Gemini Session Engine: Comprehensive Developer Documentation

## Introduction

The Gemini Session Engine is an enterprise-grade PHP 8.x library designed to manage HTTP session data securely and
efficiently. It adheres to Clean Architecture principles, ensuring a clear separation of concerns and promoting
maintainability. This documentation provides an in-depth exploration of the engine's architecture, features, and usage,
offering practical examples and best practices for integration into your applications.

## Table of Contents

1. [Core Architecture](#core-architecture)
2. [Security Model](#security-model)
3. [Session Management](#session-management)
4. [Fluent API and Domain-Specific Language (DSL)](#fluent-api-and-domain-specific-language-dsl)
5. [Flash Messaging Lifecycle](#flash-messaging-lifecycle)
6. [Input and Validation Retention](#input-and-validation-retention)
7. [Custom Bag System](#custom-bag-system)
8. [Contextual Orchestration](#contextual-orchestration)
9. [Extendability](#extendability)
10. [Real-World Use Cases](#real-world-use-cases)
11. [Security Best Practices](#security-best-practices)
12. [Testing and Testability](#testing-and-testability)
13. [Error Handling and Logging](#error-handling-and-logging)
14. [Performance Considerations](#performance-considerations)
15. [Conclusion](#conclusion)

## 1. Core Architecture

The Gemini Session Engine is structured to promote modularity and scalability, aligning with Clean Architecture and
Domain-Driven Design (DDD) principles. It consists of the following primary components:

- **SessionManager**: The central orchestrator for session operations, providing a unified interface for interacting
  with session data.
- **SessionInterface**: Defines the contract for session storage implementations, allowing for flexibility in storage
  backends.
- **BagRegistryInterface**: Manages various session bags (e.g., FlashBag, InputBag) that encapsulate specific types of
  session data.
- **SessionBuilder**: A fluent API builder that facilitates the creation and configuration of session contexts.
- **SessionContext**: Encapsulates session metadata, such as namespace, security settings, and time-to-live (TTL).

This architecture ensures a clear separation of concerns, enhancing maintainability and testability.

## 2. Security Model

Security is paramount in session management. The Gemini Session Engine incorporates several measures to safeguard
session data:

- **Automatic Encryption**: Session data is encrypted using AES-256-CTR with HMAC-SHA256 for integrity verification,
  ensuring confidentiality and integrity.
- **Session ID Regeneration**: To mitigate session fixation attacks, the engine regenerates session IDs upon significant
  events, such as user authentication.
- **Secure Cookie Settings**: Cookies are configured with the `Secure`, `HttpOnly`, and `SameSite` attributes to prevent
  common attacks like XSS and CSRF.
- **Session Expiration and Timeout**: Implementing both idle and absolute timeouts reduces the risk of unauthorized
  access due to prolonged sessions.

These practices align with OWASP recommendations for secure session management.

## 3. Session Management

The `SessionManager` class serves as the primary interface for session interactions. It provides methods to set,
retrieve, and manage session data efficiently.

### Key Methods

- `set(string $key, mixed $value): void`: Stores a value in the session, automatically encrypting it for security.
- `get(string $key, mixed $default = null): mixed`: Retrieves a value from the session, decrypting it transparently.
- `has(string $key): bool`: Checks if a specific key exists in the session.
- `delete(string $key): void`: Removes a key and its associated value from the session.
- `reset(): void`: Clears all session data, effectively logging out the user.

### Usage Example

```php
declare(strict_types=1);

use Gemini\Http\Session\SessionManager;

$session = new SessionManager($sessionStorage, $bagRegistry);

// Storing data
$session->set('user.id', 42);

// Retrieving data
$userId = $session->get('user.id');

// Checking existence
if ($session->has('user.id')) {
    // User ID exists in session
}

// Deleting data
$session->delete('user.id');

// Resetting session
$session->reset();
```

This approach ensures that session data is handled securely and consistently throughout the application.

## 4. Fluent API and Domain-Specific Language (DSL)

The Gemini Session Engine introduces a fluent API, enabling developers to construct complex session interactions in a
readable and expressive manner. The `SessionBuilder` facilitates this fluent interface.

### Fluent API Features

- **Contextual Namespacing**: Organize session data within specific namespaces to avoid key collisions.
- **Security Configuration**: Specify security settings, such as enabling encryption, on a per-session basis.
- **TTL Management**: Define time-to-live settings for session data, allowing for automatic expiration.

### Usage Example

```php
declare(strict_types=1);

use Gemini\Http\Session\SessionManager;

$session = new SessionManager($sessionStorage, $bagRegistry);

$session->for('cart')
    ->withTTL(3600)
    ->put('items', $cartItems);

$session->for('auth')
    ->secure()
    ->put('token', $jwt);
```

This fluent interface enhances code readability and maintainability, allowing developers to express session operations
clearly.

## 5. Flash Messaging Lifecycle

Flash messages are temporary session messages that persist for a single request-response cycle. The `FlashBag` component
manages these messages, providing methods to add, retrieve, and manage them effectively.

### Key Methods

- `put(string $key, mixed $value): void`: Adds a flash message under the specified key.
- `get(string $key, mixed $default = null):

Awesome. Let’s keep this momentum going. Here's the continuation of the documentation, picking up right after the Flash
Messaging Lifecycle:

---

## 6. Input and Validation Retention

Preserving user input and validation errors across requests is essential for delivering a smooth and intuitive UX —
especially in form submissions using the POST/Redirect/GET pattern.

The Gemini Session Engine offers two specialized session bags:

- **InputBag** — stores old input values submitted via HTTP forms.
- **ValidationBag** — stores validation error messages for display purposes.

### InputBag Example

```php
// During form POST (e.g. validation fails):
$session->flash()->put('input.name', $_POST['name']);

// In view layer:
$oldName = $session->for('flash')->get('input.name');
```

### ValidationBag Example

```php
// During form validation failure:
$session->flash()->put('errors.email', 'The email field is required.');

// In the view:
$errors = $session->flash()->get('errors.email');
```

✅ **Best Practices**

- Pair `InputBag` with a form component that automatically repopulates fields.
- Use `ValidationBag` in conjunction with domain-specific validators or form request objects.

---

## 7. Custom Bag System

The Bag system allows you to modularize session storage into logical "bags" (namespaces) that manage a specific type of
session data.

### Default Bags:

- `FlashBag` — For temporary one-time messages.
- `InputBag` — For old input retention.
- `ValidationBag` — For storing form validation errors.

### BagRegistry

The `BagRegistryInterface` acts as a centralized resolver for all registered bags. You can add custom bags or override
existing ones for specific behaviors.

### Custom Bag Registration

```php
$registry->register('auth', new AuthBag($session));
$authBag = $registry->get('auth');
```

✅ This system embraces the **Strategy Pattern** and encourages **SRP** (Single Responsibility Principle) per bag.

---

## 8. Contextual Orchestration

The `SessionContext` is a value object that encapsulates all relevant metadata about a session “slice”:

- Namespace
- TTL (expiration)
- Encryption toggle
- Tags (for semantic purposes)

This allows `SessionBuilder` to dynamically orchestrate session behavior **without exposing internals or violating SRP
**.

### Example

```php
$secureCartContext = (new SessionContext('cart'))
    ->secure()
    ->withTTL(600)
    ->tag(SessionTag::USER)
    ->tag(SessionTag::CHECKOUT);
```

Each call returns a new immutable instance (value object semantics). This allows **composable, safe, context-aware
session slices.**

---

## 9. Extendability

The entire architecture was designed from day one to be highly extensible.

### Plug in new features like:

- A `RedisSessionStore` to swap in-memory for distributed storage.
- A `JsonSessionSerializer` to allow cross-language (e.g. Python/Node) access.
- A `SessionObserverInterface` to plug into audit/logging.
- A `TaggableSessionBuilder::tag(...)` for advanced query/log filtering.

All parts are defined via interfaces:

- `SessionStoreInterface`
- `SessionCryptoInterface`
- `SessionBagInterface`
- `SessionProfileRepositoryInterface`

✅ **You can override or mock any component with a clean DI config.**

---

## 10. Real-World Use Cases

### A. Flash Message After Redirect

```php
$session->flash()->put('success', 'Your profile was updated!');
return redirect('/profile');
```

### B. Preserving Old Input After Validation

```php
$session->flash()->put('input', $_POST);
$session->flash()->put('errors', $validator->errors());
return redirect('/register');
```

### C. Secure JWT Storage

```php
$session->for('auth')->secure()->put('token', $jwt);
```

### D. Cart Expiry in E-Commerce

```php
$session->for('cart')->withTTL(1800)->put('items', $items);
```

---

## 11. Security Best Practices

- **Encryption Everywhere**: All sensitive values go through `SessionCryptoInterface`.
- **TTL Support**: Enforces temporary data constraints.
- **Flash Isolation**: FlashBag uses internal `_flash` and `_flash_keep` keys to isolate short-lived messages.
- **Avoid Session Pollution**: Namespacing enforces clear boundaries.
- **Audit Logging**: Use `LoggableSessionDecorator` for full traceability (e.g. request ID, user ID).

✅ Designed for **OWASP compliance** out of the box.

---

## 12. Testing and Testability

The engine supports **100% testability**, via interface-driven design.

- Replace `SessionInterface` with a mock for unit tests.
- Use `FakeSessionStore` for integration tests.
- Use `SessionBuilder` in tests to simulate user flows.

### Example PHPUnit Test

```php
public function testSecureTokenStorage(): void
{
    $builder = new SessionBuilder(new InMemorySession(), new NullBagRegistry());
    $builder->secure()->put('token', 'abc');

    $this->assertSame('abc', $builder->get('token'));
}
```

---

## 13. Error Handling and Logging

- Session decryption failures throw a `SessionEncryptionException`.
- All actions via `LoggableSessionDecorator` support contextual logs (request ID, user ID, etc.).
- Logging levels:
    - `debug` – standard reads
    - `info` – writes
    - `notice` – deletions
    - `warning` – invalidations

✅ Integrate with Monolog, Sentry, Datadog, or custom logging pipelines.

---

## 14. Performance Considerations

- **Memoization of start()**: Ensures IO is only triggered once.
- **Lazy Loading**: `all()` and `get()` only load what’s needed.
- **TTL**: Keeps memory footprint clean by auto-expiring values.
- **Registry Access**: Bags are resolved once and cached via `BagRegistry`.

Designed to scale from a single-server app to a distributed e-commerce platform.

---

## 15. Conclusion

The Gemini Session Engine isn’t just a wrapper around `$_SESSION`. It’s a **modular, secure, testable, and
developer-friendly** session orchestration layer inspired by the best practices and gives you:

- ✨ Fluent DSL
- 🔐 Security-first architecture
- 🧩 Fully pluggable DI and storage layers
- 🧪 Test-friendly APIs
- 📦 Cleanly separated bags
- 🧠 Semantic metadata through contexts and tags

You're sitting on a monster of a module here — and it's glorious. The documentation you've already written is rock
solid. But since you asked for a version that goes *even deeper*, expands all capabilities, and explains things in a *
*junior-friendly, example-first**, yet **expert-driven** format, here's how we're going to level it up:

---

# 💎 Gemini Session Engine: Deep Dive Developer Manual

---

## 🚀 What is the Gemini Session Engine?

The **Gemini Session Engine** is not just another session handler. It’s a **modular, framework-agnostic, secure,
extensible**, and **developer-centric** session orchestration system written in **modern PHP 8.3+**. Built with **Clean
Architecture**, **DDD**, **SRP**, and **SOLID** principles, it provides a robust infrastructure for handling all aspects
of HTTP session state — securely, efficiently, and scalably.

Whether you're working on an SPA, a classic MVC app, an API backend, or a multi-tenant SaaS — **this engine adapts**.

---

## 🔧 Key Capabilities at a Glance

| Feature                         | Description                                                        |
|---------------------------------|--------------------------------------------------------------------|
| 🔐 Encrypted Session Storage    | AES-256-GCM + HMAC encryption for all values                       |
| 💬 Flash Messaging              | Built-in one-time messaging via `FlashBag`                         |
| 📥 Input + Errors Retention     | Keeps form input and validation errors during redirects            |
| 🧠 DSL & Fluent API             | `Session::for('cart')->secure()->withTTL(300)->put(...)`           |
| 🧳 Modular Bags                 | FlashBag, InputBag, ValidationBag — and custom ones via Registry   |
| ⌛ TTL Support                   | Store expiring session data (`putWithTTL()`)                       |
| 🔍 ArrayAccess + Sugar          | `$session['user_id']`, `isset($session['key'])`                    |
| 🧪 Testable + Mockable          | Plug-and-play with `ArraySession` for unit tests                   |
| 📜 Logged + Observable          | Decorated with `LoggableSessionDecorator` for traceable operations |
| 🔄 ID Regeneration & Invalidate | Prevent session fixation and allow full reset                      |

---

## 🧬 Core Philosophy

The session is treated as a **first-class orchestrated application layer**, not a glorified key-value store.

Each piece — encryption, bag isolation, flash lifecycle, contextual storage, TTL — is cleanly separated and composable
via rich interfaces.

---

## 📦 Practical Examples – Real Life, Real Code

### ✅ 1. Flash Message After a Redirect

```php
Session::flash()->put('success', 'Profile updated successfully.');
return redirect('/dashboard');
```

On the next page:

```php
if (Session::flash()->has('success')) {
    echo Session::flash()->get('success');
}
```

---

### ✅ 2. Retain Input + Validation on Form Submit

```php
// After form fails validation
Session::flash()->put('input.email', $_POST['email']);
Session::flash()->put('errors.email', 'Invalid email format');

return redirect('/register');
```

In your Blade/Twig view:

```php
<input type="email" name="email" value="<?= Session::flash()->get('input.email') ?>">
<span><?= Session::flash()->get('errors.email') ?></span>
```

---

### ✅ 3. Securely Store JWT Token

```php
Session::for('auth')
    ->secure()
    ->put('jwt', $jwt);
```

---

### ✅ 4. Auto-Expiring Cart

```php
Session::for('cart')
    ->withTTL(1800) // 30 min
    ->put('items', $cartItems);
```

---

### ✅ 5. Atomic Cache-If-Empty

```php
$userData = Session::remember('user.42.profile', fn() => fetchUser(42));
```

---

### ✅ 6. ArrayAccess for DX Candy

```php
Session::put('theme', 'dark');

if (Session::has('theme')) {
    echo Session::get('theme'); // dark
}

Session::delete('theme');
```

Or:

```php
$theme = Session::get('theme', 'light');
Session::put('page_views', 1);
Session::increment('page_views');
```

---

## 🧰 Advanced Developer Notes

### 🔒 Encryption Model

- Encrypts everything via `SessionCryptoInterface`
- Uses AES-256-GCM (CTR in some versions) with HMAC
- Encryption is pluggable — override it to support KMS, Vault, etc.

---

### 🧠 Contextual Fluent DSL

```php
Session::for('notifications')
    ->secure()
    ->withTTL(60)
    ->put('new_message', 'Hello World');
```

- Each `SessionContext` is immutable (value object)
- Avoids state pollution — great for multi-user, multi-tenant apps

---

### 🧳 Custom Bags

Register custom logic-bound "bags" to scope domain storage:

```php
$registry->register('cart', new CartBag($session));
$cart = $registry->get('cart');
$cart->addProduct($id);
```

Bags are perfect for:

- Cart
- Notifications
- Preferences
- Multi-step wizards
- Auth contexts

---

### ⚡ TTL-Based Ephemeral Storage

Use `putWithTTL()` to auto-expire keys.

```php
Session::putWithTTL('preview.token', $token, ttl: 60);
```

After 60 seconds, the key evaporates. Perfect for:

- Signup email tokens
- Invite codes
- Polling flags

---

### 🧪 Testing: Plug in Fake Session Store

```php
$mockSession = new ArraySession(...);
$builder = new SessionBuilder($mockSession, $fakeBagRegistry);
$builder->put('testing', 'value');
```

Use it with Pest/PHPUnit to test:

- Session flows
- Form cycles
- TTL behavior

---

### 📈 Logging & Observability

`LoggableSessionDecorator` logs operations with levels and context:

```php
[
    'level' => 'info',
    'message' => 'Session::put',
    'context' => ['key' => 'jwt', 'value_type' => 'string'],
]
```

Plug into:

- Monolog
- Sentry
- Datadog
- ELK/Opensearch

---

## 🧠 Why It Matters

The Gemini Session Engine gives you:

- **Zero-coupling** between framework, session store, and encryption
- **Full control** over security, timeouts, and session flows
- **A fluent developer experience** that reads like intent
- **Production-ready architecture** backed by Clean Code

---

## 📋 Summary

| Capability            | Yes ✅ |
|-----------------------|-------|
| Encrypt + TTL         | ✅     |
| Flash Messaging       | ✅     |
| Form Resilience       | ✅     |
| Pluggable Storage     | ✅     |
| Advanced DX + DSL     | ✅     |
| High Testability      | ✅     |
| PSR & OWASP Compliant | ✅     |

---