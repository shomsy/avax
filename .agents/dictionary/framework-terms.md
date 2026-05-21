# AvaX Framework Terms — Separation of Concern

Dictionary of terms related to Separation of Concern governance.

---

## Separation of Concern (SoC)

**Simple:** Split things that change for different reasons.

**AvaX meaning:** Universal principle behind modularization, encapsulation, functions, objects, layering, flows, capabilities, and subsystems. Not a pattern — the reason patterns exist.

**Allowed:** "SoC at function/class/capability/component level", "SoC review lens", "change-axis test"

**Forbidden:** Using SoC to justify file shuffling without reducing coupling

**Learning:** [emlandre.com — On Separation of Concern](https://emlandre.com/2024/01/05/on-separation-of-concern-soc/)

---

## Concern

**Simple:** A reason to care about a unit of code.

**AvaX meaning:** Any reason to understand, change, test, deploy, secure, observe, configure, or evolve a unit. A concern maps to a single owner.

**Allowed:** "This class has one concern", "cross-cutting concern", "concern ownership"

**Forbidden:** Using "concern" to mean "any piece of code" without a change driver

---

## Cross-Cutting Concern

**Simple:** Something that affects many parts of the system.

**AvaX meaning:** Security, logging, validation, caching, transactions, observability, reset safety, configuration, error handling. Must be explicit capabilities or policies, not scattered helpers.

**Allowed:** "explicit cross-cutting capability", "observability boundary", "security policy"

**Forbidden:** "just add a helper function", inline security logic, scattered logging patterns

**Learning:** [how-to-architecture.md — Section 57.6](../.agents/how-to/how-to-architecture.md)

---

## Change Axis

**Simple:** A direction in which code evolves.

**AvaX meaning:** A specific reason or driver that causes a unit to change. Multiple unrelated change axes in one unit means bad SoC.

**Allowed:** "change-axis test", "what causes this to change", "related change drivers"

**Forbidden:** Ignoring change axes when evaluating class size or complexity

---

## Coupling

**Simple:** How much one unit depends on another.

**AvaX meaning:** SoC must reduce coupling, not only increase file count. Bad SoC has many bidirectional dependencies between "separated" units.

**Allowed:** "coupling direction", "low coupling", "coupling reduction"

**Forbidden:** Measuring SoC success only by file count or line count

**Learning:** [how-to-architecture.md — Section 57.9](../.agents/how-to/how-to-architecture.md)

---

## Cohesion

**Simple:** How well the parts of a unit belong together.

**AvaX meaning:** High cohesion means methods, properties, and decisions in a unit share a conceptual center. SoC improves cohesion by removing unrelated work.

**Allowed:** "high cohesion", "conceptual center", "cohesive responsibility"

**Forbidden:** Claiming cohesion while storing unrelated logic in one unit

---

## Composition Boundary

**Simple:** Where separated parts come together.

**AvaX meaning:** After separating concerns, composition must happen through clear parent, gateway, or configuration boundaries. Not through god builders or god graphs.

**Allowed:** "clean composition boundary", "configuration boundary", "gateway composition"

**Forbidden:** "just wire everything together", god builders, service locator composition

**Learning:** [how-to-architecture.md — Section 57.7](../.agents/how-to/how-to-architecture.md)
