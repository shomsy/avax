---
name: self-explaining-architecture
description: Creates and validates self-explaining architecture documentation — local READMEs, dictionaries, ADRs, diagrams, flows, mistakes files, and examples at every ownership boundary. Use when creating architecture docs, reviewing architecture clarity, or adding local documentation to components.
---

# Self-Explaining Architecture Skill

## Purpose

This skill guides the creation, review, and validation of self-explaining architecture documentation.

A system is self-explaining when:
- every important folder has a README
- every core term has a dictionary entry
- every locked decision has an ADR
- every non-trivial flow has a diagram
- every common mistake is documented
- every public surface has examples

## Activation Triggers

Activate when task:

- creates new architecture documentation
- reviews architecture clarity
- adds documentation to a component or boundary
- creates local README, dictionary, ADR, diagram, flow, or mistakes file
- validates documentation completeness
- prepares a subsystem for AI agents to work with
- prepares a subsystem for new team members

## Must Read

- `.agents/how-to/documentation/how-to-write-self-explaining-architecture.md`
- `.agents/how-to/documentation/how-to-document.md`
- `.agents/how-to/architecture/how-to-architecture.md`
- `.agents/how-to/components/how-to-design-components.md`
- `.agents/templates/architecture/` (template directory)

## Workflow

### Step 1: Identify Boundaries

Scan the target area and identify every important architectural boundary:

```text
- component root
- every System/ subfolder (PublicSurface, Flows, Capabilities, Configuration, Foundation)
- every flow directory
- every capability directory
- every public surface directory
- every configuration/assembly directory
```

### Step 2: Assess Current State

For each boundary, assess:

```text
- Does it have a README?
- Does it have dictionary entries for core terms?
- Does it have ADRs for locked decisions?
- Does it have diagrams for non-trivial flows?
- Does it have a mistakes file?
- Does it have examples for public surfaces?
```

Classify each as:

- DOCUMENTED: all required files exist and are current
- PARTIAL: some files exist, some missing
- UNDOCUMENTED: no local documentation

### Step 3: Create Missing Documentation

For each UNDOCUMENTED or PARTIAL boundary:

1. Create `README.md` using `folder-README.md` template
2. Create `dictionary/` entries for core terms using `dictionary-entry.md` template
3. Create `adr/` entries for locked decisions using `adr.md` template
4. Create `diagrams/` for non-trivial flows using `flow-diagram.md` template
5. Create `mistakes/` file using `mistakes.md` template
6. Create `examples/` for public surfaces

### Step 4: Validate

Validate each file against its template:

```text
- README: must explain what belongs, what does NOT belong, ownership, common mistakes
- Dictionary: must have What It Is, What It Is NOT, Common Confusion
- ADR: must have context, decision, alternatives, consequences, tradeoffs
- Diagram: must have Mermaid source, step-by-step explanation, participants
- Mistakes: must have mistake, correct approach, detection, fix
```

### Step 5: Cross-Reference

Ensure documentation cross-references:

```text
- README links to dictionary, ADR, and mistakes files
- Dictionary entries link to related concepts and code paths
- ADRs link to related dictionary entries and governance rules
- Diagrams link to ADRs and dictionary
- Mistakes files link to ADRs and governance rules
```

## Required Evidence

Every self-explaining architecture task must include:

- `boundary-inventory.md`: list of boundaries assessed
- `documentation-gap-analysis.md`: what exists, what is missing
- `documentation-created.md`: what was created
- `validation-output.md`: validation results

## Quality Criteria

Documentation is acceptable when:

```text
1. Every important boundary has a README
2. Every README says what belongs AND what does NOT belong
3. Every core term has a dictionary entry with What It Is, What It Is NOT, Common Confusion
4. Every locked decision has an ADR with alternatives and tradeoffs
5. Every non-trivial flow has a diagram with step-by-step explanation
6. Every common mistake is documented
7. Every public surface has an example
8. Cross-references exist between related documents
9. AI can navigate the documentation without guessing
10. A new team member can understand the boundary without asking
```

## Integration

This skill should be loaded together with:

- `avax-enterprise-codecraft` for architecture review
- `avax-component-dogfooding` for component boundary documentation
- `avax-api-compatibility-contract` for public surface documentation
- `avax-source-of-truth-resolver` before making documentation claims

## Final Rule

No local explanation, no understanding.

No understanding, no maintainability.

No maintainability, no enterprise readiness.
