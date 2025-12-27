# Avax Database Architecture

## Overview

The Avax Database component is an enterprise-grade SQL abstraction layer built for reliability, security, and developer ergonomics. It follows a strict separation of concerns between the fluent Domain Specific Language (DSL), the execution orchestration, and the physical driver management.

## Core Components

### 1. Query Builder (The DSL)

Located in `QueryBuilder/Core/Builder`, the `QueryBuilder` provides the fluent API for constructing SQL.

- **Immutability**: Every method call returns a `clone` of the builder, ensuring that partial queries can be reused without side effects.
- **State Management**: The builder delegates all structural metadata to a `QueryState` value object.

### 2. Query Orchestrator (The Conductor)

The `QueryOrchestrator` acts as the bridge between the DSL and the physical execution. It manages:

- **Pretending (Dry Runs)**: Simulating execution for auditing.
- **Transactions**: Coordination of atomic units of work.
- **Identity Map / Unit of Work**: Buffering mutations for optimized batch commits.
- **Execution Scope**: Maintaining correlation IDs and context across the stack.

### 3. Connection Pooling (The Resource Manager)

Managed in `Foundation/Connection/Pool`, the pool ensures efficient resource utilization.

- **RAII Lifecycle**: `BorrowedConnection` wraps physical connections and automatically releases them back to the pool via its destructor.
- **Health Checks**: Optional heartbeats to prune stale connections.

### 4. Grammar Engine (The Compiler)

Translates the internal `QueryState` AST into dialect-specific SQL (e.g., `MySQLGrammar`, `PostgreSqlGrammar`).

### 5. Event System (Telemetry)

An asynchronous/synchronous signal bus for database activity.

- **Observability**: Signals like `QueryExecuted` provide deep insight into performance and security.
- **Redaction**: Built-in security boundary to prevent sensitive data leakage.

## Design Philosophy

1. **Security First**: Parameters are never inlined; SQL injection is prevented by strict bound-parameter separation at the orchestrator level.
2. **Predictability**: Errors are wrapped in domain-specific exceptions (e.g., `ConnectionFailure`, `QueryException`).
3. **Enterprise Hardening**: The core is "frozen" and prioritized for stability over new features.
