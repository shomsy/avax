# How To Document Flow

Version: 1.0.0
Status: Normative

This file defines the law for programming-facing flow documents such as
`how-this-works.md`.

Use it when the question is:

- how a flow document should be written
- what a folder document must explain to be operationally useful
- how files and functions should be attached to a real trigger story
- how to stop inventory prose from pretending to be understanding

If this file and `how-to-document.md` ever disagree for a flow document, this
file wins.

## Core Idea

Flow documents must explain movement, not inventory.

The reader should be able to retell one concrete path like this:

`trigger -> first file -> first function -> main decision -> next handoff -> visible result -> failure path`

If a page only lists folders, files, or functions, it is incomplete.

## Canonical Success Test

A strong flow document lets a new reader answer these without guessing:

1. what this folder or slice really owns
2. which real trigger reaches it first
3. which file and function catch that trigger first
4. which step makes the main decision or transformation
5. what gets written, changed, rendered, or emitted
6. what the user or operator sees at the end
7. what failure or refusal looks like
8. where to debug first
9. which terms are easy to confuse here
10. why the folder, file, and function names are honest

If those answers are missing, the document is not done.

## Naming Mirror Rule

Flow documents must reinforce the code naming law rather than compensate for bad
names with nicer prose.

Use this mnemonic:

- folder says the flow or capability
- file says the responsibility
- function says the exact action

Rules:

1. if the described behavior does not match the name, say so plainly
2. do not normalize naming drift with prose alone
3. if the drift matters, record it in the backlog or review evidence
4. do not hide vague verbs behind softer narrative wording

## Writing Posture

Every flow document should teach in this order:

1. human layer
2. trigger story
3. technical layer
4. debug layer
5. dictionary

### Human Layer

Start like you are explaining the slice to:

- a new teammate on day one
- an operator who does not know the language yet
- or yourself after months away from the code

That means plain, direct, calm language.
It does not mean childish or vague language.

### Trigger Story

After the simple opening, show one real story:

- the exact command, event, request, or lifecycle trigger
- the first file
- the first function
- the main decision
- the next handoff
- the visible result
- the failure path

### Technical Layer

Only after the reader understands the story should the page widen into direct
files, functions, or child slices.

### Forbidden Tone

Do not open with:

- philosophy before purpose
- architecture poetry
- taxonomy before trigger
- vague phrases like `handles the flow`, `supports this`, or `works with`
- generated filler that avoids naming the real code

## Required Folder Contract

Each first-party ownership folder that needs explanation should contain a flow
document.

The page should explain:

- what real flow the folder owns
- how that flow is reached
- which direct files matter first
- what the folder reads, writes, changes, returns, or renders
- what happens next
- what can go wrong

## Canonical Heading Shape

Use this heading order unless a stricter local truth requires a plainer label:

1. `What this folder is`
2. `Real commands that reach this folder`
   or `Real commands or triggers that reach this folder`
3. `Exact CLI front doors`
   or `Exact upstream handoffs`
4. `The simplest story`
5. `The first important path`
6. `Direct files in this folder`
7. `Child folders in this folder` when child folders exist
8. `Debug first`
9. `What to remember`
10. `Dictionary`

Do not invent a different heading set only to sound fresh.

## First Important Path Rule

Every flow page must include one concrete path a new reader can follow without
guessing.

When possible, show:

1. the exact trigger
2. the first file and function
3. the main decision or transformation in this folder
4. the next handoff
5. the visible result or artifact
6. the failure or refusal path

Sequence diagrams, numbered bullet paths, or both are encouraged when they make
the order clearer.

## Direct File Chapter Rule

Each direct first-party file chapter should answer:

1. what this file owns
2. why its name is honest
3. what arrives here
4. what leaves here
5. why a debugger should open it first
6. which functions matter most

If the file itself is just an artifact and has no top-level functions, say so
plainly.

## Debug Layer Rule

Every flow document must tell the reader where to debug first.

Use symptom-first guidance such as:

- start in `<file>` when the artifact looks wrong
- start in `<function>` when the trigger is accepted but the result is missing
- start in `<child>/how-this-works.md` when the failure clearly belongs in the
  narrower slice

## Dictionary Rule

Flow documents should end with a small dictionary whenever the page depends on
terms a new reader may confuse.

Rules:

- define important terms once
- keep names stable through the page
- avoid swapping between multiple names for the same thing
- keep the dictionary short and operational

## Hard Fail Conditions

A flow document is incomplete when any of these is true:

- no real trigger is named
- no first file or first function is named
- it reads like inventory instead of movement
- it hides weak naming instead of surfacing it
- it never says what gets written, changed, or left behind
- it never says how failure looks or where to debug first
