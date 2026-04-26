# Access Boundary

`Access` owns access decisions and access-entry posture.

It owns:

- authentication context entry
- access policy enforcement
- admin elevation entry
- resource ownership checks
- permission and role checks
- risk assessment entry

It does not own:

- password lifecycle
- sessions as persistence/runtime detail
- MFA enrollment and verification lifecycle
- OAuth/OIDC protocol mechanics

`Access` answers: “can this actor proceed now?”

`Identity` answers: “who is this actor and what factors or sessions prove it?”
