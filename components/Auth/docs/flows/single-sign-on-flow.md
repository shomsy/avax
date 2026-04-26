# Single Sign-On Flow

Owner: `ExternalIdentity`

Primary implementation: `System/Capabilities/ExternalIdentity/SingleSignOn/FederationRuntime/`,
`System/Capabilities/ExternalIdentity/OpenIDConnect/Runtime/`, `System/Capabilities/ExternalIdentity/OAuth/Runtime/`

Sequence:

1. resolve tenant/provider connection
2. verify domain or client posture
3. start protocol negotiation
4. complete external assertion handling
5. link or provision identity
6. return authenticated result
