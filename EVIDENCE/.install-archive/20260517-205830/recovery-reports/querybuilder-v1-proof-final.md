# QueryBuilder V1 Proof Final

## Stage

V1-D3 DataStack/Database QueryBuilder Proof

## Status

PROVEN-PARTIAL / BEHAVIOR-GREEN

## PHPUnit

- 7 tests
- 45 assertions
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
- nested where through public where(Closure) API
- nested where bindings
- fake executor boundary

## Production bugs fixed

- QueryState used `bindings` named argument, but constructor expects `bindingBag`
- HasOrders used `order` named argument, but QueryState::addOrder expects `orderNode`
- HasJoins used `join` named argument, but QueryState::addJoin expects `joinNode`
- QueryBuilder used `state` named argument for grammar compile methods, but GrammarInterface expects `queryState`
- HasAdvancedMutations used `state` named argument for compileUpsert, but GrammarInterface expects `queryState`
- Grammar used invalid `in_array(true, needle: ..., haystack: ...)` calls
- Grammar compiled nested where using QueryBuilder instead of QueryState
- HasConditions::whereNested() ignored immutable callback return value

## Tests added

- tests/Unit/Components/DataStack/Database/QueryBuilderSelectTest.php
- tests/Unit/Components/DataStack/Database/QueryBuilderWriteTest.php
- tests/Unit/Components/DataStack/Database/QueryBuilderReadHelpersTest.php

## Remaining debt

QueryBuilder focused PHPStan is not fully green yet.

Known remaining families:

- array value-type warnings
- Macroable reflection typing
- dialect grammar typing noise
- advanced grammar drivers not fully cleaned
- method_exists always-true warnings
- aggregate column typing mismatch

## Decision

Do not keep expanding QueryBuilder endlessly now.

Next Database slice:
Schema + Migrations proof.
