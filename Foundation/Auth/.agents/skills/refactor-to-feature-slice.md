# Skill: Refactor to Feature-Sliced Architecture

This skill defines how to refactor existing layered Auth code to feature-sliced structure.

## When to Use

- Converting `Actions/`, `Adapters/`, `Contracts/` to feature slices
- Reorganizing existing code to follow vertical-slice pattern
- Creating new feature slices from existing components

## Current vs Target

### Current (Layered - needs refactoring)

```
Foundation/Auth/
├── Actions/
│   ├── Login.php
│   ├── Logout.php
│   └── Register.php
├── Adapters/
│   ├── Identity.php
│   ├── JwtIdentity.php
│   └── SessionIdentity.php
├── Contracts/
│   ├── AuthInterface.php
│   └── IdentityInterface.php
└── Data/
    └── Credentials.php
```

### Target (Feature-Sliced)

```
Foundation/Auth/
├── Login/
│   ├── LoginAction.php
│   ├── LoginController.php
│   └── LoginResponse.php
├── Register/
│   ├── RegisterAction.php
│   └── RegisterDTO.php
├── Session/
│   ├── GetUser.php
│   ├── Check.php
│   └── Logout.php
└── Shared/
    ├── Contracts/
    ├── Adapters/
    └── Exceptions/
```

## Refactoring Steps

### 1. Identify Features

Map current files to features:

| Current                | Feature Slice                  |
|------------------------|--------------------------------|
| `Login.php` action     | `Login/LoginAction.php`        |
| `Identity.php` adapter | `Shared/Adapters/Identity.php` |
| `Credentials.php` DTO  | `Shared/Data/Credentials.php`  |

### 2. Create Feature Folders

```
mkdir -p Foundation/Auth/{Login,Register,Session,Access,Shared}
```

### 3. Move and Rename

```bash
# Move Login action
mv Actions/Login.php Login/LoginAction.php

# Move shared to Shared
mv Contracts/ Shared/Contracts/
mv Adapters/ Shared/Adapters/
```

### 4. Update Namespaces

```php
// Before
namespace Avax\Auth\Actions;

// After
namespace Avax\Auth\Login;
```

### 5. Update Dependencies

Update imports in files that reference moved classes.

## Shared Layer

Features may have shared code:

| Folder               | Contents                 |
|----------------------|--------------------------|
| `Shared/Contracts/`  | All interfaces           |
| `Shared/Adapters/`   | Reusable implementations |
| `Shared/Exceptions/` | Custom exceptions        |
| `Shared/Data/`       | Cross-feature DTOs       |

## Verification

- [ ] All tests pass
- [ ] Static analysis passes
- [ ] No import errors
- [ ] Autoloading works

## Note

If current architecture is intentional (e.g., for migration), document in
`.agents/management/DECISIONS.md` why layered is preserved.