<?php

declare(strict_types=1);

namespace Gemini\Database\QueryBuilder\Traits;

/**
 * **BaseQueryBuilderTrait**
 *
 * ✅ **Purpose:**
 * This trait acts as a **"Master Trait"**, grouping all essential traits required for a powerful, scalable,
 * and efficient **SQL Query Builder**.
 *
 * 🏗 **Design Goals:**
 * - Centralized management of all reusable query-related traits.
 * - Enables **modular, reusable, and maintainable** code structure.
 * - Ensures the **Single Responsibility Principle (SRP)** by keeping logic in separate traits.
 * - Provides a **clean and organized** way to extend the QueryBuilder functionality.
 *
 * 🛠 **Key Features (Grouped Traits):**
 * - **🔄 Database Transactions** → `DatabaseTransactionTrait`
 * - **📌 Identity Map Pattern** → `IdentityMapTrait`
 * - **📝 INSERT, UPDATE, UPSERT** → `InsertUpdateTrait`
 * - **🔗 JOIN Clause Handling** → `JoinClauseBuilderTrait`
 * - **📊 ORDER BY, GROUP BY, HAVING** → `OrderByAndGroupByBuilderTrait`
 * - **🧩 Unit of Work Pattern** → `ProvidesUnitOfWork`
 * - **⚡ Query Optimization & Indexing** → `QueryOptimizationTrait`
 * - **🔍 SELECT Queries, Caching & Pagination** → `SelectQueryTrait`
 * - **🗑 Soft Deletes & Data Deletion** → `SoftDeleteAndDeleteTrait`
 * - **🔎 WHERE Clause Handling** → `WhereTrait`
 *
 * 🏆 **Benefits of Using This Trait:**
 * - **Single inclusion point** for all QueryBuilder functionality.
 * - **Avoids trait conflicts** by defining method precedence (if needed).
 * - **Easier to maintain** when adding or modifying traits.
 * - **Improves testability** by ensuring well-structured, isolated functionalities.
 *
 * 🚀 **Usage Example in QueryBuilder Class:**
 * ```
 * class QueryBuilder
 * {
 *     use BaseQueryBuilderTrait;
 *
 *     // Additional QueryBuilder logic...
 * }
 * ```
 */
trait BaseQueryBuilderTrait
{
    use DatabaseTransactionTrait;
    use IdentityMapTrait;
    use InsertUpdateTrait;
    use JoinClauseBuilderTrait;
    use OrderByAndGroupByBuilderTrait;
    use ProvidesUnitOfWork;
    use QueryOptimizationTrait;
    use SelectQueryTrait;
    use SoftDeleteAndDeleteTrait;
    use WhereTrait;
    use SchemaQueryBuilderTrait;
}
