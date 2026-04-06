# Skill: Auth Feature Implementation

This skill defines how to implement new authentication or authorization features
in the Avax Auth framework.

## When to Use

- Adding new auth action (Login, Logout, Register, etc.)
- Adding new adapter (Identity, Session, AccessControl)
- Adding new middleware (Auth, Role, Permission)
- Adding new feature slice

## Implementation Flow

### 1. Analyze the Feature

Before coding, answer:
- What user goal does this feature serve?
- What is the input? (DTO, credentials, request)
- What is the output? (User, bool, response)
- What adapters might be needed?

### 2. Determine the Slice

Choose the appropriate feature slice:

| Feature | Slice Location |
|---------|----------------|
| Login flow | `Login/` |
| Registration | `Register/` |
| Session management | `Session/` |
| Role management | `Roles/` |
| Permission checks | `Access/` |
| Password management | `Password/` |

### 3. Create the Structure

For a new feature slice:

```
FeatureName/
├── FeatureNameAction.php    # Use case
├── FeatureNameController.php # HTTP entry (if needed)
├── FeatureNameRequest.php    # Input DTO
└── FeatureNameResponse.php   # Output DTO (if needed)
```

### 4. Implement with DSL Naming

Follow the naming conventions:

```php
// Actions are verb-based, execute() is the entry
final readonly class Login
{
    public function execute(Credentials $credentials): UserInterface
}

// Adapters implement contracts
final class JwtIdentity implements IdentityInterface
{
    public function attempt(CredentialsInterface $credentials): bool
    public function user(): UserInterface|null
}

// Controllers are noun-verb
final class LoginController
{
    public function __invoke(LoginRequest $request): LoginResponse
}
```

### 5. Add Tests

- Unit test in `tests/Unit/`
- Integration test in `tests/Integration/`
- Cover success and failure paths

### 6. Document

Add docblock with:
- What the class/method does
- `@throws` tags
- Input/output types

## Quality Gates

Before marking complete:
- [ ] Static analysis passes (`composer analyse`)
- [ ] Tests pass (`composer test`)
- [ ] Code follows DSL naming
- [ ] Docblocks complete
- [ ] No sensitive data in logs