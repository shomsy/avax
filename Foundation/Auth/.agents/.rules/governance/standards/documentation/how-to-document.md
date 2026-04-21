# How To Document

Version: 2.1.0
Status: Normative

This file defines reusable documentation law for technical and operational
documents.

Programming-facing flow docs such as `how-this-works.md` should also follow
`how-to-document-flow.md`.

## Documentation Doctrine

- Documents must teach the real system, not an imagined one.
- Broken links, stale commands, and fake capabilities are documentation bugs.
- Prefer enforceable contracts and real paths over abstract architecture filler.
- When a document explains a risky or confusing flow, include a visual or a
  step-by-step walkthrough.
- If a claim cannot be defended by shipped code, shipped commands, or a bound
  contract, the claim does not belong in the document.
- Canonical authored source and generated derived output must be distinguished
  explicitly; do not let generated documents become the hidden editable truth.

## Machine-Verifiable Rules

The following should be linted or gateable where practical:

- required headings or control blocks exist
- internal anchors and links resolve
- commands and paths are syntactically valid for the repository
- declared terms are reused consistently
- forbidden placeholder patterns are absent
- required validation and recovery sections are present for operational docs

## Tone And Teaching Rules

- Use simple, direct English or Serbian as allowed by the adopting repository.
- Keep documents operational, not theatrical.
- Explain what the thing is before explaining why the architecture is elegant.
- Prefer concrete verbs such as `reads`, `writes`, `checks`, `builds`,
  `returns`, `fails`, and `restores`.
- Use banal examples when they reduce time-to-understanding.
- Do not hide weak naming behind pretty explanations. If a path or API is
  badly named, say so plainly.

## Minimum Required Structure

Every important technical document should explain:

1. what it is
2. why it matters
3. the contract or rule
4. the life-cycle of the data or execution path
5. how to validate it
6. how to recover when it fails

## Additional Required Structure For Operational Docs

If the document governs runtime, deploy, incident, release, or recovery
behavior, it should also include:

1. trust boundary or protected surface
2. inputs and expected outputs
3. failure posture (`fail closed` vs acceptable degraded behavior)
4. rollout or release decision inputs
5. evidence artifacts or logs an operator must capture

## Source And Derived Artifact Rules

- declare the canonical authored source when a document, manifest, or book-like
  artifact is generated from smaller source files
- generated linear outputs should be treated as derived artifacts and should
  not be manually maintained unless the repository contract says otherwise
- if one document aggregates many source chapters, the regeneration path must
  be obvious
- chapter vocabulary, section types, or document families should use a stable
  naming contract when tooling depends on them
- documentation generators must not silently invent content that bypasses the
  authored source contract

## Canonical Term Contract

For complex or unfamiliar documents:

- declare important technical terms in one obvious place
- link the first use of a term to its local anchor or authoritative source
- avoid changing names for the same concept mid-document
- do not introduce new technical entities inside a metaphor or analogy section

## Diagram Taxonomy

When diagrams are used, pick the smallest honest category:

- boundary diagram: scope, actors, trust perimeter
- dynamic flow diagram: sequence, lifecycle, handoff
- failure diagram: failure detection, stop path, recovery path
- decision diagram: GO / NO-GO or threshold logic

Diagrams should make order explicit and should not require the reader to guess
which step happens first.

## Naming Honesty In Docs

Documentation must support the same naming law as the code:

- folder says flow or capability
- file says responsibility
- function says exact action

If the name is muddy, the document should surface that as a problem, not hide
it.

## Forbidden Tone

Do not use:

- advertising language
- meta commentary about how smart the document is
- abstract philosophy before operational purpose
- filler sentences that sound important but explain nothing
- placeholder text that implies unfinished certainty

## Suggested Failure Codes

Use repository-local gates to enforce codes such as:

- `DOC-STRUCT-001`: missing required section
- `DOC-LINK-001`: broken anchor or broken link
- `DOC-TERM-001`: undeclared or inconsistently reused canonical term
- `DOC-RUNBOOK-001`: missing validation or recovery path in an operational doc
- `DOC-REALITY-001`: doc claims a capability not backed by shipped truth
