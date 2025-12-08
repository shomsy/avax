Gemini Request Module
---

# 📘 Gemini HTTP Request — Internal Modules Documentation

> 🧠 **Purpose:**  
> This module implements and extends PSR-7's `ServerRequestInterface`, enabling rich request handling capabilities,
> seamless integration with sessions, JSON parsing, input management, and JWT decoding. It is built with immutability,
> clarity, and extensibility in mind.

---

## 🧭 Index

- [`Request`](#request)
- [`AbsoluteServerRequest`](#absoluteserverrequest)
- [`ParameterBag`](#parameterbag)
- [`InputManagementTrait`](#inputmanagementtrait)
- [`SessionManagementTrait`](#sessionmanagementtrait)
- [`JwtTrait`](#jwttrait)

---

## 📦 `Request`

**Namespace:** `Gemini\HTTP\Request\Request`  
**Extends:** `AbsoluteServerRequest`  
**Implements:** `ServerRequestInterface`  
**Traits:**

- `InputManagementTrait`
- `SessionManagementTrait`
- `JwtTrait`

### 🧠 Responsibility

Central request object for application-level interaction.  
It wraps the base request, adds session support, JSON handling, input merging, JWT auth, etc.

### ✅ Key Features

- Instantiable from `$_SERVER`, `$_GET`, `$_POST`, etc.
- Unified input access: query, body, cookies, files
- Deep support for JSON requests
- Fluent session API
- Built-in JWT token decoding
- Laravel-style developer ergonomics

---

### 🔧 Constructor

```php
public function __construct(
    SessionInterface|string|null $session = null,
    array                        $serverParams = [],
    UriInterface|string|null     $uri = null,
    Stream|string|null           $body = null,
    array                        $queryParams = [],
    array                        $parsedBody = [],
    array                        $cookies = [],
    array                        $uploadedFiles = []
)
```

---

### 🔍 Usage Examples

#### Create from Globals

```php
$request = Request::createFromGlobals();
```

#### Read input value

```php
$userId = $request->input('user_id');
```

#### Read JSON

```php
$email = $request->json('email');
```

#### Access session

```php
$request->putSession('logged_in', true);
$isLoggedIn = $request->session('logged_in');
```

#### Get authenticated user (from JWT)

```php
$user = $request->getAuthenticatedUser();
```

---

## 🏗 `AbsoluteServerRequest`

**Namespace:** `Gemini\HTTP\Request\AbsoluteServerRequest`  
**Implements:** `ServerRequestInterface`

### 🧠 Responsibility

PSR-7 compliant implementation of a server request, with immutability and a parameter bag abstraction.

### ✅ Responsibilities

- Handles query, body, cookies, uploaded files, URI, headers
- Implements all `with*()` methods immutably
- Provides attribute storage (`route()` etc.)

---

### 🔧 Key Methods

| Method                 | Description                         |
|------------------------|-------------------------------------|
| `getQueryParams()`     | Returns `$_GET`-like data           |
| `getParsedBody()`      | Returns request body as array       |
| `getCookieParams()`    | Returns cookies                     |
| `getUploadedFiles()`   | Returns uploaded files              |
| `getHeaderLine($name)` | Returns header as string            |
| `route($key)`          | Shortcut to access route attributes |

---

### 🔍 Special Example

```php
$ip = $request->getClientIp(); // Smart detection across forwarded headers
$foo = $request->route('user_id'); // Equivalent to $request->getAttribute('user_id')
```

---

## 📦 `ParameterBag`

**Namespace:** `Gemini\HTTP\Request\ParameterBag`

### 🧠 Responsibility

Typed container for query/body/cookie parameters.

### ✅ Features

- Type-safe accessors: `getAsString()`, `getAsInt()`, `getAsBoolean()`
- Null-safe fallback logic
- Mutators: `set()`, `remove()`, `merge()`
- Internal use in `Request` class and traits

---

### 🔧 Example

```php
$bag = new ParameterBag(['foo' => 'bar']);
$bar = $bag->getAsString('foo');
```

---

## 🧩 `InputManagementTrait`

**Namespace:** `Gemini\HTTP\Request\Traits\InputManagementTrait`  
**Used in:** `Request`

### 🧠 Responsibility

Provides ergonomic access to all request inputs, across:

- Query (`$_GET`)
- Body (`$_POST`)
- Cookies (`$_COOKIE`)
- Files (`$_FILES`)
- JSON (`application/json`)

---

### ✅ Features

| Method         | Description                                |
|----------------|--------------------------------------------|
| `input($key)`  | Unified access across query and body       |
| `json($key)`   | Auto-decodes JSON                          |
| `has($key)`    | Checks if input exists                     |
| `allInputs()`  | Merged array of all sources                |
| `merge([...])` | Dynamically injects values into query/body |

---

### 🔍 Example

```php
$email = $request->input('email');
if ($request->has('email')) { /* do stuff */ }
$all = $request->allInputs();
```

---

## 🧩 `SessionManagementTrait`

**Namespace:** `Gemini\HTTP\Request\Traits\SessionManagementTrait`  
**Used in:** `Request`

### 🧠 Responsibility

Provides access to a session system via `SessionInterface`.

---

### ✅ Methods

| Method                          | Description              |
|---------------------------------|--------------------------|
| `session()`                     | Returns session object   |
| `getSessionValue($key)`         | Reads a key              |
| `setSessionValue($key, $value)` | Writes a key             |
| `getFlash($key)`                | Flash-data access        |
| `user()`                        | Reads 'user' session key |
| `setUser($user)`                | Writes user to session   |

---

### 🔍 Example

```php
$user = $request->user();
$request->setSessionValue('foo', 'bar');
```

---

## 🧩 `JwtTrait`

**Namespace:** `Gemini\HTTP\Request\Traits\JwtTrait`  
**Used in:** `Request`

### 🧠 Responsibility

Handles creation, extraction, and decoding of **JWT tokens**.

---

### ✅ Capabilities

| Method                             | Description             |
|------------------------------------|-------------------------|
| `setJwtSecret()`                   | Sets the secret key     |
| `generateJwtToken(array $payload)` | Generates token         |
| `getAuthenticatedUser()`           | Returns decoded token   |
| `decodeJwt($token)`                | Verifies and parses JWT |

---

### 🔍 Example

```php
$request->setJwtSecret($_ENV['JWT_SECRET']);
$user = $request->getAuthenticatedUser();
```

---

## 📚 Final Thoughts

Gemini's `Request` module is:

✅ **Standards-compliant** — full PSR-7 interface  
✅ **Extensible** — trait-based additions (JWT, session, etc.)  
✅ **Testable** — fully constructor-injected  
✅ **Elegant** — Modern PHP-esque API ergonomics  
✅ **Secure** — strict types, safe decoding, proper fallbacks

---