# Human-Grade Commenting Standards (Mental Model School)

## Philosophy

This project follows the **Human-Grade** documentation standard. While we maintain the technical precision of enterprise
contracts, we prioritize **mental models** and **human clarity**.

> **Standard code speaks to the compiler. Great code speaks to the human brain.**

Our goal is that any smart developer can understand a class or a method **without googling** and **without knowing
theory (DDD/CQRS)** beforehand.

---

## The Golden Rule

> **If you use a word that would make someone ask "What does that mean?" — you MUST explain it.**

This applies to terms like `deferred`, `orchestrator`, `scope`, `lease`, `identity map`, etc. Don't assume the reader
knows the theory.

---

## 1️⃣ Class-Level Documentation: The Mental Model

Every class MUST provide a mental model at the top. It should answer:

1. **What is it in the real world?** (Analogy)
2. **Why does it exist?** (The problem it solves)
3. **How should I imagine it in my head?** (The visualization)

### ❌ Instead of

```php
/**
 * Executes deferred mutations after transaction commit.
 */
```

### ✅ Use Human-Grade

```php
/**
 * Applies "deferred" changes to the database.
 *
 * -- what "deferred" means:
 * Changes are NOT written immediately. We first collect them 
 * in memory and only apply them after the surrounding operation 
 * completes successfully.
 * 
 * -- why this exists:
 * To prevent partial writes. If you try to save 10 things and 
 * No. 5 fails, we don't want the first 4 to stay in the database 
 * in a broken state.
 */
```

---

## 2️⃣ The "Explicit Contract" Structure

We still enforce strict structure for audit and safety, but the content must be warm and explanatory.

Every public method MUST have:

1. **Human Intent Statement** — Why this exists in plain language.
2. **@param** — Every parameter with its **semantic constraint** (what is allowed/expected).
3. **@return** — What exactly comes back and how to use it.
4. **@throws** — Every failure mode and what it means for the caller.

### ✅ REQUIRED Format

```php
/**
 * Borrow a connection from the "library" (the pool).
 *
 * -- intent:
 * Instead of creating a new connection every time (which is slow),
 * we borrow an existing one.
 *
 * -- mental model:
 * Think of this as a library card. You get a connection, you use it,
 * and when you're done, you MUST return it so others can use it.
 *
 * @param string $name Use the technical nickname of the connection defined in config.
 * @return DatabaseConnection A "leased" connection. Treat it as a temporary tool.
 * @throws PoolExhaustedException If all "cards" are currently out and the library is full.
 */
public function acquire(string $name): DatabaseConnection
```

---

## 3️⃣ Explaining Technical Terms (The "No-Jargon" Rule)

If you must use a technical term, you must define it in context.

- **Orchestrator**: "The conductor of an orchestra. It doesn't play the instruments (SQL), but it tells everyone else
  when to start and stop."
- **Scope**: "A temporary window or a bubble. Once the code leaves this bubble, everything inside is cleaned up
  automatically."
- **Lease**: "A temporary permission to use a resource. You don't own it; you just have it for a while."
- **Identity Map**: "A 'To-Do' list of changes. Instead of doing chores one by one, we write them down and do them all
  at once at the end."

---

## 4️⃣ File-Level Context

Every file should start with a "Big Picture" explanation.

```php
/**
 * Database Query Builder.
 * 
 * -- the big picture:
 * This class is a "Sentence Builder" for SQL. It lets you write
 * code like `$query->where('id', 1)` and transforms it into 
 * physical SQL like `SELECT * FROM ... WHERE id = 1`.
 *
 * It acts as a safety barrier between your application and the
 * raw database, ensuring all inputs are handled securely.
 */
```

---

## 5️⃣ What NOT to do

❌ **Don't be cold.** Avoid "Manages resource lifecycle."
✅ **Be clear.** Say "Handles the birth, life, and death of a connection."

❌ **Don't assume.** Don't say "Follows RAII pattern."
✅ **Explain behavior.** Say "Automatically cleans up when the object is destroyed."

❌ **Don't use placeholders.**
✅ **Use concrete examples.**

---

## Summary of Changes

| Category    | Enterprise Cold (Old)      | Human-Grade (New)          |
|:------------|:---------------------------|:---------------------------|
| **Tone**    | Abstract, formal           | Warm, explanatory, helpful |
| **Logic**   | Definition-based           | Analogy/Mental model-based |
| **Terms**   | Expected to be known       | Explained in context       |
| **Purpose** | Architecture documentation | Brain-friendly onboarding  |

**Our goal is: Great code must be top-down readable, starting from the comments.**
