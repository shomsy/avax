# Register Flow

Registration now returns `RegistrationResult`, not the internal `User` entity.

## Happy Path

1. Rate limit is checked for the email address.
2. Email and username uniqueness are verified through `UserSourceInterface`.
3. The password is hashed through `PasswordHasher`.
4. A new internal `User` is created and persisted.
5. The public result is projected to `AuthenticatedUser`.

## Public Types

- `RegistrationData`
- `RegistrationResult`

## Failure Model

- duplicate email: `RegistrationFailed::emailTaken()`
- duplicate username: `RegistrationFailed::usernameTaken()`

## Notes

Registration does not automatically log the user in. Email verification can be initiated separately through
`beginEmailVerification()`.
