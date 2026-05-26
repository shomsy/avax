# Authorization

## What It Is

Authorization is the process of determining whether a verified identity has permission to perform a specific action on a specific resource. Authorization answers the question: "Are you allowed to do this?"

Authorization requires a previously authenticated identity. It operates on the principle of deny-by-default.

## What It Is NOT

- Authorization is NOT authentication. Authorization assumes identity is already established.
- Authorization is NOT role assignment. Roles are one model for expressing permissions, but authorization is the broader concern of access decisions.
- Authorization is NOT tenant scoping. Tenant boundaries are a prerequisite for authorization, but tenant isolation is a separate concern.
- Authorization is NOT audit logging. Authorization decisions should be logged, but logging is a consequence, not the decision itself.

## Common Confusion

The most common confusion is treating route-level guards as sufficient authorization. Route guards check whether a request can reach an endpoint, but authorization must also protect the resource itself. A user may reach the correct endpoint but still lack permission for the specific resource they are requesting.

Another confusion is treating roles as permissions. A role is a grouping mechanism. Authorization decisions must be based on actual permissions, which may be derived from roles, attributes, policies, or direct grants.

## In AvaX

AvaX treats authorization as:

- A decision point that receives verified identity, requested action, target resource, and context
- A policy-evaluation system that returns allow, deny, or conditional
- A deny-by-default system where unexpressed permissions are denied
- A system that protects the resource, not only the route
- An observable system where every decision produces an auditable event

Authorization in AvaX is separate from authentication. Authentication produces identity. Authorization consumes identity and produces decisions.
