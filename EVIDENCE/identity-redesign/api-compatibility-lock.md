# Identity API Compatibility Lock

## Status

LOCKED — Current public API is characterized and tested.

## Current Public API

### Identity Root DSL (`Identity::class`)

| Method | Return Type | Status | Test Coverage |
|--------|------------|--------|--------------|
| `auth()` | `Auth` | GREEN | `authReturnsAuthSurface` |
| `access()` | `Access` | GREEN | `accessReturnsAccessSurface` |
| `credentials()` | `Credentials` | GREEN | `credentialsReturnsCredentialsSurface` |
| `tokens()` | `Tokens` | GREEN | `tokensReturnsTokensSurface` |
| `tenancy()` | `Tenancy` | GREEN | `tenancyReturnsTenancySurface` |
| `risk()` | `Risk` | GREEN | `riskReturnsRiskSurface` |
| `externalIdentity()` | `ExternalIdentity` | GREEN | `externalIdentityReturnsExternalIdentitySurface` |

### Auth Surface (`Auth::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `check()` | `(): bool` | `(): bool` | NONE | KEEP |
| `guest()` | `(): bool` | `(): bool` | NONE | KEEP |
| `user()` | `(): User\|null` | `(): AuthenticatedUser\|null` | Return type name | RENAME in Slice 4 |
| `login()` | `(Credentials $credentials): AuthenticationResult` | `(LoginCredentials $credentials): AuthenticationContext` | Input/result types | MIGRATE in Slice 4 |
| `logout()` | `(): void` | `(): void` | NONE | KEEP |
| `register()` | `(RegistrationData $request): RegistrationResult` | `(RegisterUserRequest $request): RegisteredUser` | Input/result types | MIGRATE in Slice 4 |
| `changePassword()` | `(ChangePasswordData $request): void` | `(ChangePasswordRequest $request): void` | Input type name | RENAME in Slice 4 |
| `logoutAllSessions()` | `(): void` | `(): void` | NONE | KEEP |
| — | — | `current(): AuthenticationContext` | MISSING | ADD in Slice 4 |
| — | — | `requireAuthentication(): void` | MISSING | ADD in Slice 4 |

### Access Surface (`Access::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `allows()` | `(string $permission, mixed $resource): bool` | `(UserPermission $permission, mixed $resource): bool` | Param type | MIGRATE in Slice 4 |
| `denies()` | `(string $permission, mixed $resource): bool` | `(UserPermission $permission, mixed $resource): bool` | Param type | MIGRATE in Slice 4 |
| `authorize()` | `(string $permission, mixed $resource): void` | — | Renamed | Target uses `requirePermission` instead |
| `requireAuthentication()` | `(): void` | `(): void` | NONE | KEEP |
| `requireRole()` | `(UserRole $role): void` | `(UserRole $role): void` | NONE | KEEP |
| `requirePermission()` | `(UserPermission $perm): void` | `(UserPermission $perm): void` | NONE | KEEP |
| `isElevated()` | `(): bool` | — | Extra | KEEP as internal |
| `beginElevation()` | `(): void` | — | Extra | Moved to `Admin` surface |
| `endElevation()` | `(): void` | — | Extra | Moved to `Admin` surface |
| — | — | `requirePolicy(AccessPolicy $policy): void` | MISSING | ADD in Slice 4 |
| — | — | `requireResourceOwner(int $ownerUserId): void` | MISSING | ADD in Slice 4 |

### Credentials Surface (`Credentials::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `store()` | `(string $userId, array $credentials): void` | — | REMOVED | DEPRECATE — not in target DSL |
| `read()` | `(string $userId): ?array` | — | REMOVED | DEPRECATE — not in target DSL |
| `forget()` | `(string $userId): void` | — | REMOVED | DEPRECATE — not in target DSL |
| `mfa()` | `(): Mfa` | `(): Mfa` | NONE | KEEP |
| `passkeys()` | `(): Passkey` | `(): Passkeys` | Return type name | RENAME in Slice 5 |
| — | — | `passwords(): Passwords` | MISSING | ADD in Slice 4 |

### Tokens Surface (`Tokens::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `authorize()` | `(array $request): stdClass` | — | REMOVED | DEPRECATE — OAuth flow, not token issuance |
| `exchangeCode()` | `(string $code): stdClass` | — | REMOVED | DEPRECATE — OAuth flow |
| `introspect()` | `(string $token): stdClass` | `(string $token): TokenIntrospection` | Return type | MIGRATE in Slice 4 |
| `revoke()` | `(string $token): void` | `(TokenId $tokenId): void` | Param type | MIGRATE in Slice 4 |
| `issue()` | `(string $sub): void` (EMPTY) | `(TokenSubject $subject): IssuedToken` | BROKEN | FIX in Slice 4 |
| — | — | `refresh(RefreshTokenRequest $request): IssuedToken` | MISSING | ADD in Slice 4 |

### Tenancy Surface (`Tenancy::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `currentTenant()` | `(): ?string` | `(): ?Tenant` | Return type | MIGRATE in Slice 6 |
| `requireTenant()` | `(): string` | `(): Tenant` | Return type | MIGRATE in Slice 6 |
| `admin()` | `(): Admin` | `(): Admin` | NONE | KEEP |
| `resolve()` | `(RequestInterface $request): string` | — | Extra | KEEP internal |
| `getTenantId()` | `(): ?string` | — | Extra | DEPRECATE — alias |
| `setTenantId()` | `(string $tenantId): void` | — | Extra | KEEP internal |
| `clearTenant()` | `(): void` | — | Extra | KEEP internal |
| `run()` | `(string $tenantId, Closure $op): mixed` | — | Extra | KEEP internal |
| `switch()` | `(string $tenantId): void` | — | Extra | KEEP internal |

### Admin Surface (`Admin::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `beginElevation()` | `(): void` (EMPTY) | `(): AdminElevation` | BROKEN | FIX in Slice 6 |
| — | — | `endElevation(): void` | MISSING | ADD in Slice 6 |
| — | — | `requireElevation(): void` | MISSING | ADD in Slice 6 |

### Risk Surface (`Risk::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `assessCurrent()` | `(): null` (always) | `(?string $ip, ?string $ua): ?RiskDecision` | BROKEN | FIX in Slice 6 |
| — | — | `signals(?int $userId): array` | MISSING | ADD in Slice 6 |
| — | — | `endpointPosture(): EndpointPosture` | MISSING | ADD in Slice 6 |

### ExternalIdentity Surface (`ExternalIdentity::class`)

| Method | Current Signature | Target Signature | Gap | Action |
|--------|------------------|-----------------|-----|--------|
| `link()` | `(string $userId, string $provider, array $data): void` | — | REMOVED | DEPRECATE — not in target DSL |
| `resolve()` | `(string $userId, string $provider): ?array` | — | REMOVED | DEPRECATE — not in target DSL |
| — | — | `oauth(): OAuth` | MISSING | ADD in Slice 5 |
| — | — | `oidc(): Oidc` | MISSING | ADD in Slice 5 |
| — | — | `federation(): Federation` | MISSING | ADD in Slice 5 |

## Compatibility Strategy

### Phase 1: Preserve (Slice 0-3)
- All current methods remain functional
- Characterization tests prove current behavior
- No breaking changes

### Phase 2: Add (Slice 4-6)
- New target DSL methods added alongside existing methods
- Both old and new APIs work
- Tests cover both

### Phase 3: Deprecate (Slice 7)
- Old methods marked `@deprecated`
- Migration guide written
- Tests verify migration path

### Phase 4: Remove (Post-redesign)
- Only after semantic version major bump
- Migration period documented

## Test Proof

- `IdentityTargetDslCharacterizationTest`: 22 tests, all GREEN
- Full Identity suite: 156 tests, 455 assertions, GREEN (2 expected warnings from negative test)
- All sub-area characterization tests pass

## Locked Date

2026-05-21

## Lock Owner

AI agent — autonomous backlog execution per refactor-identity.md
