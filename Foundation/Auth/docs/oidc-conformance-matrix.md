# OIDC Conformance Matrix

This matrix records the kernel-owned OIDC behavior that is implemented today and
what still remains outside this package.

| Capability | Status | Ownership | Verification |
| --- | --- | --- | --- |
| discovery metadata | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| JWKS publication | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| authorization code + `openid` | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| PKCE for public clients | supported | kernel | `tests/Flows/OAuth/OAuthFlowTest.php` |
| nonce required for `openid` | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| RS256 ID-token issuance | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| userinfo from active access token | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| overlap JWKS during key rollover | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| dynamic client registration | not supported | external package or app | n/a |
| front-channel logout | partial | kernel | `tests/Flows/Oidc/OidcFlowTest.php`, `tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php` |
| back-channel logout | partial | kernel | `tests/Flows/Oidc/OidcFlowTest.php`, `tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php` |
| pairwise subject identifiers | supported | kernel | `tests/Capabilities/Oidc/SubjectIdentifierTest.php`, `tests/Flows/Oidc/OidcFlowTest.php` |
| PAR request storage | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php`, `tests/Integrations/Http/Oidc/ServeOidcHttpSurfaceTest.php` |
| JARM response signing | supported | kernel | `tests/Flows/Oidc/OidcFlowTest.php` |
| JAR client-signed request objects | partial | external package or app | `tests/Flows/Oidc/ValidateRequestObjectTest.php` |
| external certification | not supported | delivery program | n/a |
