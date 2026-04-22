# 🚀 Avax Framework CLI - Usage Guide

**The ultimate command-line companion** for Avax Database and Migration components.

---

## 📂 Project Structure

| Directory                  | Description                           |
|----------------------------|---------------------------------------|
| **`database/migrations/`** | Where your migration files live       |
| **`database/exports/`**    | Default folder for database SQL dumps |
| **`avax`**                 | Root executable binary                |

---

## ✅ Commands

Iz korena projekta pokrećete:

```bash
php avax [command]
```

### 📦 Migration Management

- `php avax make <name>` - Kreira novu migraciju (koristi pametne stubove)
    - Opcije: `--create=table_name` ili `--table=table_name`
- `php avax migrate` - Izvršava sve pending migracije
- `php avax rollback` - Vraća poslednju seriju migracija
- `php avax status` - Prikazuje tabelu stanja (RAN/PENDING)

### 🗄️ Database Operations

- `php avax db:create <name>` - Pravi novu bazu podataka
- `php avax db:drop <name>` - Briše bazu (oprezno!)
- `php avax db:export [path]` - Eksportuje celu bazu (shemu) u SQL fajl u `database/exports/`

---

## 🏗️ Export System

Sistem za export se nalazi u `Foundation/Database/System/Capabilities/Migrations/Export/`. Trenutno podržava generisanje
SQL dump-a sheme
svih tabela.

---

**Uživaj u radu sa Avax CLI alatom! 💎**
