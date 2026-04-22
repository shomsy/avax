# Grammar Translation

## What it does

Grammar classes (`BaseGrammar`, `MySqlGrammar`, etc.) are responsible for turning the `QueryState` DTO into a valid SQL
string for a specific database engine.

## Why it exists

- **Dialect Abstraction**: Different databases use different syntax for things like limits (`LIMIT` vs `TOP`), column
  quoting (`backticks` vs `double quotes`), and upserts.
- **Single Responsibility**: The `QueryBuilder` handles logic, while the `Grammar` handles the technical string
  formatting.

## When to use

- You rarely use Grammar directly. It is injected into the `QueryBuilder`.
- You might implement a custom `GrammarInterface` if you need to support a new database type.

## How it works

1. The `QueryBuilder` passes its `QueryState` to the grammar.
2. The grammar iterates through the state (columns, joins, wheres) and calls specific "compiler" methods.
3. It joins these fragments together into the final SQL string.

## Common pitfalls

- **Quoting identifiers**: Always use the grammar's `wrap()` method for column and table names to ensure they are
  escaped according to the database's rules.
- **SQL Injection in Grammar**: Grammars should never interpolate raw values. They should only produce SQL fragments
  with placeholders (`?`) that match the `BindingBag`.
