# SecureRequest — how this works

## What SecureRequest is

SecureRequest is HTTP request-as-DTO. It gives you a fully typed, validated, authorized request object in your
controller — no manual input parsing, no `rules()` method, no string-rule arrays.

## Canonical style

```php
final class RegisterRequest extends SecureRequest
{
    #[Required]
    #[StringType]
    #[AlphaNumOrEmail]
    public string $email;

    #[Required]
    #[StringType]
    #[Min(3)]
    #[Max(50)]
    public string $username;

    #[Required]
    #[StringType]
    #[Min(8)]
    #[Max(64)]
    #[RegexPattern('/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&]).{8,64}$/')]
    public string $password;

    public function authorize(): bool
    {
        return true;
    }

    protected function withValidation(ValidationContext $context): void
    {
        if ($context->request->password === $context->request->username) {
            $context->addViolation(
                field: 'password',
                message: 'Password must not match username.',
            );
        }
    }
}
```

## Controller usage

```php
final readonly class RegisterController
{
    public function __invoke(RegisterRequest $request): Response
    {
        // $request is already hydrated, validated, and authorized
        // No manual parsing, no $request->input('email')
        return new Response($request->email);
    }
}
```

The Container automatically detects `SecureRequest` subclasses and resolves them through the full lifecycle.

## Lifecycle order

1. `beforeHydration()` — before any processing
2. **Hydrate** public typed properties from input
3. `afterHydration()` — after properties are set
4. `beforeValidation()` — before attribute validation
5. **Attribute validation** — `#[Required]`, `#[StringType]`, `#[Min]`, etc.
6. `withValidation(ValidationContext $context)` — custom cross-field validation
7. `afterValidation()` — after all validation
8. If violations exist: `failedValidation($violations)` + throw `SecureRequestValidationFailed` (422)
9. `authorize()` — authorization check
10. If `! authorize()`: throw `SecureRequestAuthorizationFailed` (403)
11. `passedValidation()` — only called on full success

## Lifecycle hooks

| Hook                            | When                    | Purpose                                 |
|---------------------------------|-------------------------|-----------------------------------------|
| `authorize()`                   | After validation        | Return `true`/`false` for authorization |
| `beforeHydration()`             | First                   | Setup, logging                          |
| `afterHydration()`              | After properties set    | Post-hydrate transforms                 |
| `beforeValidation()`            | Before attribute checks | Pre-validation setup                    |
| `afterValidation()`             | After validation passes | Post-validation transforms              |
| `passedValidation()`            | After authorization     | Final setup before controller           |
| `failedValidation($violations)` | On validation failure   | Error handling, logging                 |
| `withValidation($context)`      | During validation       | Custom cross-field rules                |

## ValidationContext

```php
protected function withValidation(ValidationContext $context): void
{
    // Add custom violation
    $context->addViolation(
        field: 'password',
        message: 'Password must not match username.',
    );

    // Check existing violations
    if ($context->hasViolations()) { ... }

    // Get all violations
    $all = $context->violations();

    // Access raw input
    $input = $context->input;

    // Access the request object
    $request = $context->request;
}
```

## Exceptions

| Exception                          | HTTP Status | When                                     |
|------------------------------------|-------------|------------------------------------------|
| `SecureRequestValidationFailed`    | 422         | Attribute or custom validation fails     |
| `SecureRequestAuthorizationFailed` | 403         | `authorize()` returns false              |
| `SecureRequestResolutionFailed`    | 500         | Cannot resolve from current HTTP request |

## Input source priority

Route params > body (parsed) > query params

## What SecureRequest is NOT

- Not a replacement for the generic HTTP Request object
- Not a place for manual input parsing (`$request->input()`, `json_decode()`)
- Not a Laravel FormRequest clone (no `rules()` method)
- Not a DTO for non-HTTP contexts (use DataTransfer directly for that)

## Rules

- Use `DataObject` + `DataTransfer` for generic typed input/output outside HTTP.
- Use `SecureRequest` only for HTTP controller input.
- Do not pass meaningful boundary data as anonymous arrays in controllers.
- Do not `json_decode` request body manually in controllers when SecureRequest is available.
- Do not use `rules()`.
- Do not put HTTP behavior into DataTransfer.
- Do not put DTO behavior into DataStack/Data.
