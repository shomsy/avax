# Naming Standard

Version: 1.0.0
Status: Normative

## The Golden Rule

> **Folder says Flow, File says Responsibility, Function says Exact Action.**

The repository should read like a story of the system, not like a warehouse of technical containers.

## 1. Navigational Clarity

A contributor (human or AI) should be able to predict the purpose of a folder, file, or function before opening it. If a path does not sound like a clear sentence or a logical step, the naming is insufficient.

- **Bad**: `src/ui/components/Input/index.js` (Generic bucket)
- **Good**: `product/onboarding/collect-user-details.js` (Flow-based)

## 2. Forbidden Generic Suffixes

Avoid these suffixes unless no domain-specific term exists:
- `manager`
- `service`
- `helpers`
- `utils`
- `orchestrator`
- `controller`
- `manifest`

These names often "hide" that the responsibility is not clearly defined.

## 3. Verb-First Actions

Function and method names must be glagolski (verb-based) and direct. Use the narrowest honest bucket.

- **Bad**: `handleData()`, `processThing()`, `run()`, `execute()`
- **Good**: `renderTheory()`, `changeControlValue()`, `syncEditor()`, `applyLiveStyles()`

## 4. Operation Vocabulary Hierarchy

Classify work in this order of specificity:

1.  **Business**: `approveInvoice`, `publishArticle` (Business domain verbs).
2.  **State Transition**: `issueInvoice`, `markAsPaid`, `cancelOrder`.
3.  **System**: `importFromCsv`, `syncFromApi`, `validatePayload`.
4.  **Query**: `searchOrders`, `filterByStatus`, `aggregateRevenue`.
5.  **CRUD**: `createUser`, `updateUser` (Only for raw record touching).
