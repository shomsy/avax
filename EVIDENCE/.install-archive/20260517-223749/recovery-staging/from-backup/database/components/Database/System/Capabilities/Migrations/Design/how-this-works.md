---
title: system-capabilities-migrations-design-how-this-works
owner: foundation-database-migrations
last_reviewed: 2024-10-22
classification: internal
---

# System / Capabilities / Migrations / Design How This Works

## What this folder is

DSL for schema declarations (tables, columns, indexes) in BaseMigration.

## Triggers

MigrationGenerator stubs → Blueprint::table('users', fn(Column $col) => $col->string()).

## Upstream

CreateMigration → Design DSL → SchemaOperations compile/run.

## How it works

Fluent ColumnDefinition collected → ColumnSQLRenderer → SQL.

## Main units

- BaseMigration.php: Extends with DSL methods.
- Table/Blueprint.php, TableDefinition.php.
- Column/DSL/ColumnDefinition.php.
- Column/Render/ColumnSQLRenderer.php.
- TypeMapping/SQLToPHPTypeMapper.php.

