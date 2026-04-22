### URI BUILDER DOCS

---

# **UriBuilder Documentation**

## **Overview**

The `UriBuilder` is a PSR-7-compliant URI builder designed to simplify the creation, manipulation, and validation of
URIs. With its intuitive API and modular design, it provides static factories and chainable methods to construct URIs
dynamically.

---

## **Features**

- **PSR-7 Compliance**: Implements the `UriInterface` for seamless integration with frameworks and libraries.
- **DSL-like API**: Intuitive, human-readable method names like `addParam`, `replaceParam`, `addEndpoint`, etc.
- **Static Factories**: Create instances with methods like `createFromString` or `fromBaseUri`.
- **Validation**: Ensures all URI components conform to standards (e.g., scheme, host, query).
- **Traits for Reusability**: Modular functionality split into traits like `QueryParameterTrait` and
  `UriValidationTrait`.
- **Immutability**: Each method returns a new instance, preserving the original URI.

---

## **Table of Contents**

- [Installation](#installation)
- [Getting Started](#getting-started)
- [Static Factory Methods](#static-factory-methods)
- [Methods](#methods)
- [Traits](#traits)
- [Examples](#examples)
- [Query Parameter Management](#query-parameter-management)

---

## **Installation**

Install via Composer:

```bash
composer require your/package-name
```

---

## **Getting Started**

The `UriBuilder` makes creating and managing URIs simple and intuitive:

```
use Gemini\HTTP\URI\UriBuilder;

// Create a URI
$uri = UriBuilder::fromBaseUri('https://example.com')
    ->addEndpoint('/api/v1/resources')
    ->addParam('page', '1')
    ->addParam('limit', '10')
    ->build();

echo $uri;
// Output: https://example.com/api/v1/resources?page=1&limit=10
```

---

## **Static Factory Methods**

### **`UriBuilder::createFromString(string $uri)`**

Creates an instance of `UriBuilder` from a given URI string.

```
$uri = UriBuilder::createFromString('https://example.com/path?query=value');
```

---

### **`UriBuilder::fromBaseUri(string $baseUri, array $overrides = [])`**

Creates a `UriBuilder` based on a base URI with optional overrides for components.

#### Parameters:

- `baseUri`: The base URI string.
- `overrides`: An associative array of URI components (e.g., scheme, host, path, etc.).

```
$uri = UriBuilder::fromBaseUri('https://example.com', [
    'path' => '/new/path',
]);
```

---

## **Methods**

The `UriBuilder` provides chainable methods for modifying URI components:

### **General Methods**

- **`withScheme(string $scheme): self`**: Sets the URI scheme.
- **`withHost(string $host): self`**: Sets the URI host.
- **`withPort(?int $port): self`**: Sets the URI port.
- **`withPath(string $path): self`**: Sets the URI path.
- **`withQuery(string $query): self`**: Sets the entire query string.
- **`withFragment(string $fragment): self`**: Sets the URI fragment.
- **`withAddedQueryParams(array $params): self`**: Adds multiple query parameters to the existing ones.

---

### **Query Parameter Management**

- **`addParam(string $key, string $value): self`**: Adds a new query parameter without overwriting existing values.
- **`replaceParam(string $key, string $value): self`**: Replaces or adds a query parameter.
- **`removeParam(string $key): self`**: Removes a query parameter.
- **`updateParams(array $params): self`**: Adds or replaces multiple query parameters.
- **`getParams(): array`**: Returns all query parameters as an associative array.

---

### **DSL-Friendly Endpoint Management**

- **`addEndpoint(string $endpoint): self`**: Adds or modifies the endpoint of the URI. Alias for `withPath` with a more
  intuitive naming for API usage.

---

### **Build URI**

- **`build(): string`**: Returns the final URI string.

---

## **Traits**

### **QueryParameterTrait**

Provides methods for managing query parameters:

- **`addParam(string $key, string $value): self`**
- **`replaceParam(string $key, string $value): self`**
- **`removeParam(string $key): self`**
- **`updateParams(array $params): self`**
- **`getParams(): array`**

---

### **UriValidationTrait**

Handles validation and normalization of URI components, including:

- **Scheme Validation**: Ensures valid schemes (e.g., `http`, `https`).
- **Host Validation**: Normalizes and validates hostnames.
- **Port Validation**: Checks for valid port numbers.
- **Query Validation**: Ensures query strings are correctly encoded.
- **Path Validation**: Normalizes paths and removes invalid segments.

---

## **Examples**

### **Create a Basic URI**

```
$uri = UriBuilder::createFromString('https://example.com')
    ->addEndpoint('/api/v1/resources')
    ->addParam('key', 'value')
    ->build();

echo $uri;
// Output: https://example.com/api/v1/resources?key=value
```

---

### **Use fromBaseUri**

```
$uri = UriBuilder::fromBaseUri('https://example.com')
    ->addEndpoint('/new/path')
    ->addParam('key', 'value')
    ->build();

echo $uri;
// Output: https://example.com/new/path?key=value
```

---

### **Add or Replace Query Parameters**

```
$uri = UriBuilder::fromBaseUri('https://example.com')
    ->addParam('key', 'value')
    ->replaceParam('key', 'newValue')
    ->build();

echo $uri;
// Output: https://example.com?key=newValue
```

---

### **Remove Query Parameters**

```
$uri = UriBuilder::createFromString('https://example.com?key=value')
    ->removeParam('key')
    ->build();

echo $uri;
// Output: https://example.com
```

---

### **Update Multiple Query Parameters**

```
$uri = UriBuilder::createFromString('https://example.com')
    ->updateParams(['key1' => 'value1', 'key2' => 'value2'])
    ->build();

echo $uri;
// Output: https://example.com?key1=value1&key2=value2
```

---

### **Add an Endpoint**

Use `addEndpoint` for intuitive and DSL-friendly endpoint management:

```
$uri = UriBuilder::fromBaseUri('https://example.com')
    ->addEndpoint('/api/v1/resource')
    ->addParam('id', '123')
    ->build();

echo $uri;
// Output: https://example.com/api/v1/resource?id=123
```

---

### **Add Query Parameters Dynamically**

Use `withAddedQueryParams` to add multiple parameters at once:

```
$uri = UriBuilder::fromBaseUri('https://example.com')
    ->withAddedQueryParams(['key1' => 'value1', 'key2' => 'value2'])
    ->build();

echo $uri;
// Output: https://example.com?key1=value1&key2=value2
```

---
