# QueryBuilder V1 Core Proof Report

## Stage

V1-D3 DataStack/Database QueryBuilder Core + Read Helpers Proof

## Status

PROVEN-PARTIAL

## Tests

- tests/Unit/Components/DataStack/Database/QueryBuilderSelectTest.php
- tests/Unit/Components/DataStack/Database/QueryBuilderWriteTest.php
- tests/Unit/Components/DataStack/Database/QueryBuilderReadHelpersTest.php

## Result

- 6 tests
- 41 assertions
- PASS

## Proven behavior

- SELECT compilation
- WHERE bindings
- WHERE IN bindings
- WHERE BETWEEN bindings
- JOIN compilation
- ORDER BY compilation
- LIMIT compilation
- OFFSET compilation
- DISTINCT compilation
- INSERT compilation and bindings
- UPDATE compilation and bindings
- DELETE compilation and bindings
- first()
- value()
- count()
- find()
- fake executor boundary

## Production bugs fixed

- QueryState used `bindings` named argument, but constructor expects `bindingBag`
- HasOrders used `order` named argument, but QueryState::addOrder expects `orderNode`
- HasJoins used `join` named argument, but QueryState::addJoin expects `joinNode`
- QueryBuilder used `state` named argument for grammar compile methods, but GrammarInterface expects `queryState`
- Grammar used invalid `in_array(true, needle: ..., haystack: ...)` calls

## Status meaning

This does not make the full Database component V1-ready.

It proves the QueryBuilder V1 core/read-helper slice.

## Next slice

Schema + Migrations proof:

- SchemaBuilder
- table blueprint
- create table SQL or blueprint behavior
- migration base class
- migration runner or migration discovery
