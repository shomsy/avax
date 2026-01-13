### 🧭 EXECUTION INSTRUCTION — APPLY ALL ARCHITECTURE & REVIEW POLICIES

You must continue **exactly** according to the internal enterprise methodology:

1. **Follow `how-to-code-review.md`** as the *primary review framework*:
   
   - Execute all required phases (Phase 0 → Phase 1 → Phase 2)
   - Produce mandatory sections: `ARCHITECTURE NOTES`, `FINDINGS`, `DECISION`, `DECISIONS-LOG`, `NEXT STEPS`
   - Every finding must have `Symptom`, `Root Cause`, `Impact`, `Evidence`, `Risk Level`.

2. **Apply `how-to-coding-standards.md`** to all implementation tasks:
   
   - Use PSR-12 + PHP 8.3 strict typing
   - Docblocks for all classes, methods, properties
   - Clear `@throws` tags and nullable as `Type|null`
   - Remove unused imports and noise comments
   - Favor readability and fluent, human-grade DSLs

3. **Use `how-to-ARCHITECTURE EXECUTION POLICY.md`** as the *execution layer*:
   
   - Translate review findings into concrete ToDo tasks
   - Each task must have: priority, files to change, solution outline, and acceptance criteria
   - Maintain DDD boundaries (no static global state, DI preferred)
   - Preserve API stability while eliminating architectural smells
   - Follow “Refactor → Validate → Document” cycle for each feature

4. **Order of precedence:**
   
   - Architecture Execution Policy > Code Review > Coding Standards

5. **Deliverables per phase:**
   
   - Review results in `Code-Review-And-ToDo/review.md`
   - Implementation plan in `Code-Review-And-ToDo/refactor-plan.md`
   - Code changes follow all hygiene and security standards

6. **Goal:** Achieve *Enterprise Grade 10/10* — correctness, determinism, observability, and maintainability.

---

📘 **Summary:**  

> Continue all Router-related work under these three .md documents as your **governing framework**.  
> Each change must be reviewable under `how-to-code-review.md`, compliant under `how-to-coding-standards.md`, and executed per `how-to-ARCHITECTURE EXECUTION POLICY.md`.
