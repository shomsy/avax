# ADR-003: Access Is The Umbrella For Access Entry

Status: accepted

`Access` is the root owner for:

- authentication context entry
- authorization enforcement
- elevation gates
- risk-based access entry

It is not the owner of account lifecycle or factor lifecycle.

Consequence:

- access checks remain explicit and centralized
- account proof mechanisms stay in `Identity`
