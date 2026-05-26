# Authorization

## What It Is

Authorization is the process of determining whether an authenticated user or system is permitted to perform a specific action or access a specific resource.

## Why It Exists

Authentication proves identity. Authorization proves permission. Without authorization, any authenticated user could perform any action, which is a security failure.

## Real-World Analogy

Authentication is your building badge showing you are an employee. Authorization is the specific doors your badge can open. You are authenticated to enter the building, but not authorized to enter the server room.

## Ownership

Owned by the Identity component, Authorization capability.

## Common Confusion

People think authorization is about roles. Roles are one authorization strategy. AvaX authorization supports roles, permissions, policies, and tenant scopes. Authorization is the generic mechanism; roles/permissions/policies are implementations.

## What It Is NOT

- Authorization is NOT authentication
- Authorization is NOT role management (roles are data, not decisions)
- Authorization is NOT route protection (authorization protects resources, not URLs)

## Common Mistakes

1. **Authorizing by route pattern instead of resource identity** — Must protect the resource, not the URL.
2. **Hard-coding permissions in middleware** — Permissions should be data-driven, not hard-coded.
3. **Checking authorization without authentication** — Must verify identity before checking permissions.

## Relation to Other Concepts

- **Authentication**: Required before authorization (identity must be proven)
- **Policy**: An authorization rule that evaluates a specific condition
- **Role**: A group of permissions assigned to users
- **Tenant Context**: Authorization must respect tenant boundaries

## Where It Appears in Code

- Namespace: `AvaX\Components\Identity\Capabilities\Authorize`
- Key classes: `Authorize`, `AuthorizationResult`, `PolicyEvaluator`
- Key interfaces: `AuthorizerInterface`, `PolicyInterface`
