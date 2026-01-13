\# ARCHITECTURE EXECUTION POLICY

\*\*Post-Review Execution Rules for All Systems\*\*



---



\## 1. Purpose

This document defines the \*\*mandatory transition from architecture review to execution\*\*.

Its purpose is to eliminate ambiguity between:

\* \*\*analysis / review mode\*\*

\* \*\*implementation / refactor mode\*\*

Once this policy applies, the system must evolve through \*\*controlled execution\*\*, not repeated re-evaluation.

This policy applies to \*\*all subsystems\*\*, \*\*all future prompts\*\*, and \*\*all architectural refactor work\*\*.



---



\## 2. Review Closure Rule

An enterprise architecture review is considered \*\*closed\*\* when:

\* A final decision is recorded (`Keep and Improve`, `Redesign`, or `Rewrite Candidate`)

\* Findings and next steps are explicitly listed

\* The system is classified as \*\*viable for evolution\*\*

At that point:



> \*\*The review phase ends.\*\*



No additional review cycles are implied unless explicitly requested.



---



\## 3. Execution Mode Declaration (Critical)

After review closure, the project enters \*\*Execution Mode\*\*.

Execution Mode means:

\* Work performed is \*\*implementation\*\*, not analysis

\* Code changes are expected

\* Architectural intent is already agreed

\### Rules

\* Each execution pass must result in a \*\*compilable, runnable system\*\*

\* Rolling back to the pre-pass state is \*\*not the default\*\*

\* Rollback is allowed \*\*only if explicitly agreed before the pass begins\*\*

\* “Leaving the repository untouched” is \*\*not a neutral action\*\* during execution mode

Execution Mode exists to \*\*reduce architectural debt\*\*, not to re-decide direction.



---



\## 4. Iteration Contract (Mandatory for All Refactors)

All execution work must be split into \*\*iterations\*\*.

Each iteration must define:

\### 4.1 Goals

\* Exactly \*\*one primary goal\*\*

\* Maximum \*\*two goals\*\*, only if tightly coupled

\### 4.2 Non-Goals

\* Explicit list of what must \*\*not\*\* be changed

\* Any work outside scope is forbidden

\### 4.3 Constraints

\* No architectural expansion beyond the stated goal

\* No opportunistic refactors

\* No new abstractions unless required by the goal

\### 4.4 Completion Criteria

\* What “done” means in observable terms

\* Compilation and runtime stability are mandatory

If any of these are missing, the iteration \*\*must not start\*\*.



---



\## 5. Scope Discipline Rule

During an iteration:

\* If additional issues are discovered:

&nbsp; \* They must be \*\*noted\*\*

&nbsp; \* They must \*\*not\*\* be implemented

\* Scope changes require explicit approval \*\*before coding\*\*

This rule exists to prevent:

\* cascading refactors

\* dead code

\* brittle half-finished abstractions



---



\## 6. Ownership and Authority

\* \*\*Architectural direction\*\* is fixed by the review and this policy

\* \*\*Implementation details\*\* are owned by the implementer within the defined scope

\* Any deviation from scope or intent must be proposed explicitly

This ensures:

\* clear responsibility

\* no hidden design drift

\* no silent process changes



---



\## 7. Forward Planning Rule

Future architectural improvements may be listed as:

\* “Acknowledged architectural debt”

\* “Planned follow-up iterations”

They are \*\*not active tasks\*\* until explicitly scheduled as an iteration.

Listing an item does \*\*not\*\* authorize implementation.



---



\## 8. Applicability

This policy applies to:

\* All architecture reviews

\* All refactor prompts

\* All follow-up implementation requests

\* All systems (frameworks, libraries, subsystems, shared components)

Once adopted, this policy is \*\*stable and reusable\*\*.



---



\## 9. Final Principle



> \*\*Review decides direction.

> Execution delivers change.

> The two must never be mixed.\*\*



---



Ako želiš sledeće:

\* mogu da ti napišem \*\*kratki “header tekst”\*\* koji ubacuješ u svaki prompt (1–2 pasusa)

\* ili \*\*mini-checklistu\*\* koju koristiš pre svakog refactor taska

Ali ovim dokumentom si \*\*definisao zakon\*\*.



