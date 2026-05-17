# Term
Policy

# Classification
Authorization Logic Component

# Purpose
Encapsulates authorization rules and access decision logic for specific resources. A policy determines whether a given user or actor is permitted to perform a specific action on a specific resource, answering yes/no to authorization questions.

# Why Allowed
Policy is the standard Laravel authorization pattern, formalized through its Gate and Policy system. Laravel policy classes map methods to actions (e.g., `view`, `create`, `update`, `delete`, `viewAny`) and receive a user and a model instance to make authorization decisions. This pattern is also used in ASP.NET Core authorization (policy-based authorization handlers), Kubernetes RBAC (policies define access rules), cloud IAM systems (AWS IAM policies, GCP IAM policies), and Open Policy Agent (Rego policies for infrastructure and application authorization). A policy has clear characteristics: it is bounded to a specific resource or model, it answers yes/no authorization questions, it is testable in isolation, it does not perform side effects, and it separates authorization logic from business logic. A policy is not a generic rule holder — it is a focused authorization component that makes access decisions based on the user, the resource, and the requested action.

# Allowed Contexts
- Authorization rules for specific resources or models
- Access control decisions (can this user do this thing to this resource?)
- Resource-level permissions (ownership, role-based, attribute-based)
- RBAC and ABAC systems where policies define the authorization matrix
- Pre-authorization checks before business logic execution
- Post-authorization hooks for audit logging or conditional behavior

# Forbidden Misuse
- As a generic business rules holder for non-authorization logic
- As a catch-all for any conditional logic or if/else branching
- Policies that perform side effects (sending emails, modifying data, creating records)
- Policies that depend on request state instead of user and resource
- Using "Policy" to describe validation, formatting, or transformation rules

# Ecosystem References
- https://laravel.com/docs/authorization#creating-policies
- https://learn.microsoft.com/en-us/aspnet/core/security/authorization/policies
- https://kubernetes.io/docs/reference/access-authn-authz/rbac/
- https://www.openpolicyagent.org/

# Allowed Patterns
- UserPolicy
- PostPolicy
- OrderPolicy
- TeamPolicy
- InvoicePolicy
- WorkspacePolicy

# Forbidden Patterns
- Policies/ (as folder for random conditionals or non-authorization logic)
- PolicyManager (managers are forbidden; policies are evaluated, not managed)
- BusinessPolicy (too vague — which resource? what authorization?)
- ValidationPolicy (validation is not authorization — use validators)
- Policy/Policy (recursive, meaningless)
