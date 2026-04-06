# User Registration Flow

This document describes the process of registering a new user in the Avax Auth framework.

## Overview

The registration flow is orchestrated by the `Register` action and follows a feature-sliced, step-by-step approach.

```mermaid
sequenceDiagram
    participant App
    participant Register as Register (Action)
    participant Validate as ValidateRegistrationData
    participant Check as CheckUserCanBeRegistered
    participant Hash as HashRegisteredPassword
    participant Create as CreateRegisteredUser
    participant DB as UserSource

    App->>Register: execute(RegistrationData)
    Register->>Validate: execute(data)
    Validate->>DB: isEmailUnique(email)
    Validate-->>Register: void
    Register->>Check: execute(data)
    Check-->>Register: bool
    Register->>Hash: execute(password)
    Hash-->>Register: hash
    Register->>Create: execute(...)
    Create->>DB: save(user)
    Create-->>Register: User
    Register-->>App: User
```

## Key Components

- **`Register`**: The orchestrator action that coordinates the registration process.
- **`RegistrationData`**: A value object that holds the incoming registration information (email, username, password).
- **`ValidateRegistrationData`**: Checks if the data is valid and if the email/username is already taken.
- **`CheckUserCanBeRegistered`**: Performs additional business rule checks before proceeding.
- **`HashRegisteredPassword`**: Uses the `PasswordHasher` to securely hash the user's password.
- **`CreateRegisteredUser`**: Generates a new `UserId`, creates the `User` entity, and saves it to the `UserSource`.

## Exceptions

- **`RegistrationFailed`**: Thrown if any step of the registration process fails (e.g., duplicate email, invalid data).

## Example Usage

```php
use Avax\Auth\Register\Register;
use Avax\Auth\Register\RegistrationData;

$register = new Register(
    userSource: $userSource,
    passwordHasher: $passwordHasher,
    idGenerator: $idGenerator
);

try {
    $user = $register->execute(new RegistrationData(
        email: 'newuser@example.com',
        username: 'newuser',
        password: 'secure-password'
    ));
} catch (RegistrationFailed $e) {
    // Handle registration error
}
```
