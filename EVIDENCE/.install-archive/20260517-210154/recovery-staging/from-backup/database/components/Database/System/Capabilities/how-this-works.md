---
title: system-capabilities-how-this-works
owner: foundation-database
last_reviewed: 2026-04-22
classification: internal
---

# System / Capabilities How This Works

## What this folder owns

This folder is the ownership split for the Database runtime. Each child folder is a first-class capability with one
public reason to exist.

## The simplest story

- `System/Database.php` receives a public call such as `query()`, `entityManager()`, `schema()`, `migrations()`,
  `transactions()`, or `telemetry()`.
- Control moves into exactly one capability folder.
- That capability finishes the local work through its own runtime files and returns the result to the caller.

## The first important path

- `Avax\Database\Database::configuration()->ready()` builds the system in `System/Configuration/DatabaseBuilder.php`.
- `DatabaseBuilder.php` wires `Connections`, `Query`, `ORM\EntityManager`, `Migrations\Schema\Schema`, `Migrations`,
  `Transactions`, and `Telemetry`.
- `System/Database.php` exposes those capability owners to the rest of the application.

## Child folders in this folder

- `Connections/` owns connection config, direct PDO access, and pooling.
- `Query/` owns the Laravel-like SQL DSL and execution runtime.
- `ORM/` owns Doctrine-style metadata, identity, hydration, repositories, and unit of work.
- `Migrations/` owns schema design, migration load/run/rollback, export, and seeding.
- `Transactions/` owns transaction boundaries and savepoint behavior.
- `Telemetry/` owns database events, scope tracking, and subscribers.

## Debug first

- Start in `System/Configuration/DatabaseBuilder.php` when the wrong capability instance is being built.
- Start in `System/Database.php` when the public Database surface exposes the wrong object.
- Start in the specific capability folder when behavior is wrong after construction.

## What to remember

- This folder is not a generic warehouse.
- `Query` and `ORM` are separate capabilities on purpose.
- `Schema` lives under `Migrations`, while transaction boundaries live only under `Transactions`.

