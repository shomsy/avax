# ADR-005: External Identity Gets Its Own Root

Status: accepted

OAuth, OIDC, and federation are grouped under `ExternalIdentity` because they share one concern:

- negotiating or consuming trust with external clients and providers

They are not local account lifecycle concerns and they are not SCIM sync concerns.

Consequence:

- protocol completeness can evolve without polluting local auth flows
- federation and OAuth stay close enough to share policy where needed
