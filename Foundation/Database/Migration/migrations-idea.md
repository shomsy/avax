---

```md
# 🧬 Custom Migration System – Serverless & API-First

This document describes the architecture, philosophy, and behavior of the custom migration system designed for *
*serverless environments**, **containerized deployments**, and **API-first workflows**.

## 🧠 Design Philosophy

Unlike traditional file-scanning migration tools, this system is **database-driven**. The migration execution is
tracked, orchestrated, and managed **entirely through a dedicated `migrations` table**, making it resilient,
API-compatible, and cloud-native.

---

## 📦 Table of Contents

- [Key Concepts](#key-concepts)
- [Migration Lifecycle](#migration-lifecycle)
- [Migrations Table Schema](#migrations-table-schema)
- [Creating a Migration](#creating-a-migration)
- [Executing Migrations](#executing-migrations)
- [Rolling Back Migrations](#rolling-back-migrations)
- [Design Advantages](#design-advantages)

---

## 🧩 Key Concepts

- Migrations are **PHP files returning Migratio_xxxx `readonly class` objects** that extend an abstract `Migration`
  class.
- The **`migrations` database table is the single source of truth** for migration status.
- **Filesystem scanning is never required.**
- Migrations can be **generated and executed via API endpoints**.
- Designed for **serverless and CI/CD pipelines**.

---

## 🔄 Migration Lifecycle

1. ✅ **Migration is generated** via API.
2. ✅ The migration file is saved with a unique timestamp (e.g., `20250330_CreateUsersTable.php`).
3. ✅ A record is inserted into the `migrations` table, marking the migration as **registered** but **not executed**.
4. ✅ On execution, the system:
    - Loads the file from the `migration` field.
    - Instantiates the migration (`return new readonly class extends Migration { ... }`)
    - Calls `executeUp()`, which internally calls `up()`.
    - Updates the `migrations` table with `batch`, `executed_at`, and sets the migration as executed.

---

## 📋 Migrations Table Schema

```sql
CREATE TABLE migrations
(
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    migration   VARCHAR(255)                        NOT NULL,
    executable  VARCHAR(255)                        NOT NULL,
    batch       INT                                 NOT NULL,
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL
);
```

- `migration`: The filename (e.g., `20250330_CreateUsersTable.php`)
- `executable`: The class or namespace string (e.g., `Infrastructure\Migrations\20250330_CreateUsersTable`)
- `batch`: Execution batch group (used for rollback)
- `executed_at`: Timestamp of successful execution

---

## ✨ Creating a Migration

Migration files must return a `readonly class` that extends the base `Migration`:

```php
<?php

declare(strict_types=1);

namespace Infrastructure\Migrations;

use Gemini\Database\Migration\Runner\Migration;

return new readonly Migration_Class_Name class extends Migration {
    protected function up(): void
    {
        $this->schemaBuilder->create(
            table: 'products',
            callback: function (TableBlueprint $table): void {
                $table->integer(name: 'id');
                $table->string(name: 'name');
                $table->decimal(name: 'price');
                $table->timestamps();
            }
        );
    }

    protected function down(): void
    {
        $this->schemaBuilder->drop(table: 'products');
    }
};
```

---

## 🚀 Executing Migrations

The system checks the `migrations` table for unexecuted migrations:

- If `batch` is not set or `executed_at` is `null`, the migration is considered **pending**.
- The system `require`s the migration file and calls `$migration->executeUp()`.
- On success, it records:
    - `batch`: Incremented integer batch number
    - `executed_at`: Current timestamp

> Let's figure this out: Migrations are executed in the order they are registered in the table, not by filename. Or
> maybe by timestamp in filename? Timestamp in filename is a good idea to ensure order, but do you have a better idea?

---

## ⏪ Rolling Back Migrations

Rollback operates per batch:

- The system fetches the **most recent batch number**
- Iterates through all migrations in that batch **in reverse order**
- Calls `$migration->executeDown()` for each
- Deletes the corresponding record from the `migrations` table

---

## ✅ Design Advantages

| Feature                           | Benefit                                             |
|-----------------------------------|-----------------------------------------------------|
| ✅ **Filesystem decoupled**        | No reliance on `glob()` or folder structure         |
| ✅ **Serverless-ready**            | Stateless, cloud-native, CI/CD friendly             |
| ✅ **Database-driven truth**       | Audit-ready, reproducible deployments               |
| ✅ **Clean Architecture**          | Separation between infrastructure and orchestration |
| ✅ **Immutable migration objects** | Readonly classes ensure migration consistency       |
| ✅ **Batching support**            | Enables precise rollback and safe batch deployments |
| ✅ **API support**                 | Migrations can be triggered via HTTP endpoints      |
| ✅ **Fully transactional**         | Safe, atomic schema operations                      |

---

## 🛡️ Security Considerations

- All migrations are executed via validated DTO input
- No user-provided file or code is ever evaluated
- SQL injection is prevented via parameterized queries
- Full logging available via injected `LoggerInterface`

---

## 🛠 Future Improvements

- Add support for `pretend` mode (dry-run)
- Add support for tagging or grouping migrations
- Add ability to seed data as part of migrations
- Enable snapshot generation for large rollbacks

---

## 🧠 TL;DR

**This system replaces traditional file-scanning migrations with a fully deterministic, database-driven orchestration
layer — optimized for modern serverless and distributed systems.**

```

---