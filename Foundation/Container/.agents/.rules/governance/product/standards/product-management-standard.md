# Product Management & Operations (ProdOps) Standard

Version: 1.0.0
Status: Normative / Agent-Agnostic

## Purpose

Define a standardized framework for Product Management and discovery activities within
this governance system. This ensures that agents can maintain strategic alignment,
process user research, and produce standard product artifacts (PRDs, Personas) that
meet quality gates.

## 0) The Context Library (Living Context)

Strategic context must be maintained as a "living" resource in `.agent/context/`.
This library should include:

- **Personas**: Structured customer/user profiles.
- **Stakeholder Map**: Analysis of influence and interest.
- **Style Guide**: Tone, voice, and writing standards.
- **Strategy Doc**: High-level vision and roadmap.

## 1) The PRD Lifecycle (Product Requirements)

The PRD (Product Requirements Document) is the source of truth for the **"What"** and **"Why"**.
A PRD is ready for implementation only when it passes a multi-perspective review.

**Standard PRD Structure**:

1. **Problem Statement**: What real pain are we solving?
2. **Success Metrics (KPIs)**: How do we measure impact?
3. **Proposed Solution**: High-level approach/flow.
4. **Target Personas**: Who is this for?
5. **Technical Constraints**: Initial feasibility notes.
6. **Out of Scope**: What are we NOT doing?

## 2) Discovery & Rituals

### Meeting Snapshot to Action

Every meeting snapshot must be processed into:

- **Triggers**: What events started the discussion?
- **Decisions**: What was finalized?
- **Actions**: Specific items for `TODO.md`.
- **Context Updates**: Changes to the Persona or Strategy docs.

### User Interview Processing

Raw interview notes must be distilled into:

- **Pain Points**: Explicit frustrations.
- **Quotes**: Direct evidence of need.
- **Insights**: Non-obvious patterns across users.

## 3) Multi-Perspective Review

Before high-stakes changes (Major features, Strategy shifts), the agent must
simulate or solicit feedback from at least three specialized roles:

- **Engineering**: Feasibility, complexity, and security.
- **UX/Design**: Usability, consistency, and persona alignment.
- **Legal/Compliance**: Risk, privacy, and regulatory adherence.
- **Executive/Business**: Alignment with vision, ROI, and metrics.

## Completion Criteria

A ProdOps activity is complete only when:

1. Artifacts follow the defined scaffolds (`PRD.md`, etc.).
2. High-stakes PRDs have recorded multi-perspective feedback.
3. The `.agent/context/` is updated with relevant findings.
