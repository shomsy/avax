# QueryBuilder Core Proof Report

## Stage

V1-D3 DataStack/Database QueryBuilder Core Proof

## Status

PROVEN-PARTIAL

## Proven behavior

- SELECT compilation through QueryBuilder
- WHERE bindings
- ORDER BY compilation
- LIMIT compilation
- INSERT compilation and bindings
- UPDATE compilation and bindings
- DELETE compilation and bindings
- Executor boundary works through fake executor

## Tests

- tests/Unit/Components/DataStack/Database/QueryBuilderSelectTest.php
- tests/Unit/Components/DataStack/Database/QueryBuilderWriteTest.php

## Result

- 4 tests
- 24 assertions
- PASS

## Production bugs fixed

- QueryState used `bindings` named argument, but constructor expects `bindingBag`
- HasOrders used `order` named argument, but QueryState::addOrder expects `orderNode`
- QueryBuilder used `state` named argument for grammar compile methods, but GrammarInterface expects `queryState`
- Grammar used invalid `in_array(true, needle: ..., haystack: ...)` calls

## Current status

QueryBuilder is not fully V1-ready yet.

It is now proven-partial.

## Next slice

QueryBuilder Conditions + Joins + Read Helpers:

- whereIn
- whereBetween
- join
- leftJoin
- offset
- distinct
- first
- value
- count
- find
